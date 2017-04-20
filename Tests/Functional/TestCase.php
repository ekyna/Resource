<?php

namespace Ekyna\Component\Resource\Tests\Functional;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\EventManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\ContainerBuilder;
use Ekyna\Component\Resource\Config\Loader\ConfigLoader;
use Ekyna\Component\Resource\Config\Loader\YamlFileLoader;
use Ekyna\Component\Resource\Doctrine\ORM;
use Ekyna\Component\Resource\Tests\Functional\Doctrine\DoctrineRegistry;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection as SDI;

/**
 * Class TestCase
 * @package Ekyna\Component\Resource\Tests\Functional
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var SDI\ContainerInterface
     */
    private $container;


    public static function setUpBeforeClass(): void
    {
        if (false == class_exists('Doctrine\ORM\Version', $autoload = true)) {
            self::markTestSkipped('Doctrine ORM lib not installed. Have you run composer with --dev option?');
        }
        if (false == extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('The pdo_sqlite extension is not loaded. It is required to run doctrine tests.');
        }
    }

    protected function setUp(): void
    {
        $this->boot();
    }

    protected function get($id)
    {
        return $this->container->get($id);
    }

    protected function clearDatabase(): void
    {
        /** @var EntityManager $manager */
        $manager = $this->get('doctrine.entity_manager');

        $purger = new ORMPurger($manager);
        $purger->purge();

        // Reset auto increment for all tables
        $connection = $manager->getConnection();
        $tables = $connection->getSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            /** @noinspection SqlResolve */
            $connection->query("DELETE FROM sqlite_sequence WHERE name = '$table';");
        }
    }

    protected function load($fixtures): void
    {
        if (!is_array($fixtures)) {
            $fixtures = [$fixtures];
        }

        if (empty($fixtures)) {
            return;
        }

        $loader = new Loader();

        foreach ($fixtures as $fixture) {
            if (is_string($fixture)) {
                if (!class_exists($fixture)) {
                    throw new \RuntimeException(sprintf("Class %s does not exist", $fixture));
                }

                $fixture = new $fixture;
            }

            if (!$fixture instanceof FixtureInterface) {
                throw new \RuntimeException("Expected instance of " . FixtureInterface::class);
            }

            if ($fixture instanceof SDI\ContainerAwareInterface) {
                $fixture->setContainer($this->container);
            }

            $loader->addFixture($fixture);
        }

        /** @var EntityManager $manager */
        $manager = $this->get('doctrine.entity_manager');

        $executor = new ORMExecutor($manager);
        $executor->execute($loader->getFixtures(), true);
    }

    private function boot(): void
    {
        $this->cacheDir = __DIR__ . '/../app/cache';
        $file = $this->cacheDir . '/container.php';
        $createDb = false;

        if (!file_exists($file)) {
            $createDb = true;
            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir);
            }

            $builder = new SDI\ContainerBuilder();

            $builder->setParameter('kernel.cache_dir', $this->cacheDir);
            $builder->setParameter('kernel.debug', true);
            $builder->setParameter('locales', ['en', 'fr']);
            $builder->setParameter('locale', 'en');

            $this->buildContainer($builder);

            $builder->compile();

            $dumper = new SDI\Dumper\PhpDumper($builder);
            file_put_contents($file, $dumper->dump([
                'class' => 'TestContainer',
            ]));
        }

        /** @noinspection PhpIncludeInspection */
        require_once $file;
        /** @noinspection PhpUndefinedClassInspection */
        $this->container = new \TestContainer();

        if ($createDb) {
            $path = $this->cacheDir . '/db.sqlite';
            if (is_file($path)) {
                unlink($path);
            }

            /** @var ManagerRegistry $registry */
            $registry = $this->get(ManagerRegistry::class);

            foreach ($registry->getManagers() as $em) {
                /** @noinspection PhpParamsInspection */
                $schemaTool = new SchemaTool($em);
                $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
            }
        }
    }

    private function buildContainer(SDI\ContainerBuilder $container)
    {
        $loader = new SDI\Loader\XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load(__DIR__ . '/../../Bridge/Symfony/Resources/config/services.xml');

        $configLoader = new ConfigLoader();
        $locator = new FileLocator([__DIR__ . '/../app']);

        $yamlLoader = new YamlFileLoader($configLoader, $locator);
        $yamlLoader->load('Acme/Resource/Resources/config/resources.yml');

        $builder = new ContainerBuilder($configLoader);
        $builder->build($container);

        $this->buildDoctrine($container);
    }

    private function buildDoctrine(SDI\ContainerBuilder $container): void
    {
        $container->register('doctrine.cache', ArrayCache::class);

        $container
            ->register('doctrine.xml_driver', SimplifiedXmlDriver::class)
            ->setArgument(0, [
                __DIR__ . '/../app/Acme/Resource/Resources/ORM' => 'Acme\Resource\Entity',
            ]);

        $container
            ->register('doctrine.orm.listeners.resolve_target_entity', ResolveTargetEntityListener::class);

        $container
            ->register('doctrine.event_manager', EventManager::class)
            ->addMethodCall('addEventSubscriber', [new SDI\Reference(ORM\Listener\EntityListener::class)])
            ->addMethodCall('addEventSubscriber', [new SDI\Reference(ORM\Listener\LoadMetadataListener::class)])
            ->addMethodCall('addEventSubscriber', [new SDI\Reference(ORM\Listener\TranslatableListener::class)])
            ->addMethodCall('addEventSubscriber', [new SDI\Reference('doctrine.orm.listeners.resolve_target_entity')]);

        $container
            ->register('doctrine.orm.entity_listener_resolver', DefaultEntityListenerResolver::class)
            ->addMethodCall('register', [new SDI\Reference(ORM\Listener\TranslatableListener::class)]);

        $container
            ->register('doctrine.configuration', Configuration::class)
            ->addMethodCall('setMetadataCacheImpl', [new SDI\Reference('doctrine.cache')])
            ->addMethodCall('setQueryCacheImpl', [new SDI\Reference('doctrine.cache')])
            ->addMethodCall('setResultCacheImpl', [new SDI\Reference('doctrine.cache')])
            ->addMethodCall('setMetadataDriverImpl', [new SDI\Reference('doctrine.xml_driver')])
            ->addMethodCall('setProxyDir', [$this->cacheDir . '/Proxies'])
            ->addMethodCall('setProxyNamespace', ['Proxies'])
            ->addMethodCall('setAutoGenerateProxyClasses', [true])
            ->addMethodCall('setEntityListenerResolver', [new SDI\Reference('doctrine.orm.entity_listener_resolver')]);

        $container
            ->register('doctrine.connection', Connection::class)
            ->setFactory([DriverManager::class, 'getConnection'])
            ->setArguments([
                [
                    'driver' => 'pdo_sqlite',
                    //'path'   => ':memory:',
                    'path'   => $this->cacheDir . '/db.sqlite',
                ],
                new SDI\Reference('doctrine.configuration'),
                new SDI\Reference('doctrine.event_manager'),
            ]);

        $container
            ->register('doctrine.entity_manager', EntityManager::class)
            ->setFactory([EntityManager::class, 'create'])
            ->setArguments([
                new SDI\Reference('doctrine.connection'),
                new SDI\Reference('doctrine.configuration'),
            ]);

        $container
            ->register(ManagerRegistry::class, DoctrineRegistry::class)
            ->setArguments([
                ['default' => new SDI\Reference('doctrine.connection')],
                ['default' => new SDI\Reference('doctrine.entity_manager')],
                'default',
                'default',
                'Doctrine\ORM\Proxy\Proxy',
            ]);
    }
}
