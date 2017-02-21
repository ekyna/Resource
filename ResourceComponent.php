<?php

namespace Ekyna\Component\Resource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\EntityListener;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\LoadMetadataListener;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\TranslatableListener;

/**
 * Class ResourceComponent
 * @package Ekyna\Component\Resource
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceComponent
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Configuration\ConfigurationFactory
     */
    private $configurationFactory;

    /**
     * @var Configuration\ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var Dispatcher\ResourceEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Locale\LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var Event\EventQueue;
     */
    private $eventQueue;

    /**
     * @var Persistence\PersistenceEventQueue
     */
    private $persistenceEventQueue;

    /**
     * @var Persistence\PersistenceHelperInterface
     */
    private $persistenceHelper;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Configures the resources.
     *
     * @param array $definitions
     * @param array $interfaceMap
     *
     * @return array The inheritance map and the translation map.
     */
    public function configureResources(array $definitions, array $interfaceMap)
    {
        $inheritanceMap = $translationMap = [];

        $registry = $this->getConfigurationRegistry();
        $factory = $this->getConfigurationFactory();

        foreach ($definitions as $namespace => $resources) {
            foreach ($resources as $id => $config) {
                $translation = null;
                if (isset($config['translation'])) {
                    $translation = $config['translation'];
                    unset($config['translation']);

                    $translationMap[$translation['entity']] = $config['entity'];
                    $translationMap[$config['entity']] = $translation['entity'];
                }

                $inheritance = [
                    'class'      => $config['entity'],
                ];
                if (array_key_exists('repository', $config)) {
                    $inheritance['repository'] = $config['repository'];
                }
                $inheritanceMap = [
                    $namespace.'.'.$id => $inheritance,
                ];

                $registry->addConfiguration($factory->createConfiguration([
                    'namespace'   => $namespace,
                    'id'          => $id,
                    'classes'     => $config,
                    'translation' => $translation,
                ]));
            }
        }

        $evm = $this->em->getEventManager();

        // Resolve entity target subscriber
        if (!empty($interfaceMap)) {
            $rtel = new ResolveTargetEntityListener();
            foreach ($interfaceMap as $model => $implementation) {
                $rtel->addResolveTargetEntity($model, $implementation, []);
            }
            $evm->addEventSubscriber($rtel);
        }

        // Load metadata listener
        $lms = new LoadMetadataListener($inheritanceMap, $interfaceMap);
        $evm->addEventSubscriber($lms);

        // Translatable listener
        $tl = new TranslatableListener(
            $this->getConfigurationRegistry(),
            $this->getLocaleProvider(),
            $translationMap
        );
        $evm->addEventSubscriber($tl);

        // Load Entity listener
        $el = new EntityListener($this->getPersistenceEventQueue());
        $evm->addEventSubscriber($el);


        return [$inheritanceMap, $translationMap];
    }


    /**
     * Returns the configuration factory.
     *
     * @return Configuration\ConfigurationFactory
     */
    public function getConfigurationFactory()
    {
        if (null === $this->configurationFactory) {
            $this->configurationFactory = new Configuration\ConfigurationFactory();
        }

        return $this->configurationFactory;
    }

    /**
     * Returns the configuration registry.
     *
     * @return Configuration\ConfigurationRegistry
     */
    public function getConfigurationRegistry()
    {
        if (null === $this->configurationRegistry) {
            $this->configurationRegistry = new Configuration\ConfigurationRegistry();
        }

        return $this->configurationRegistry;
    }

    /**
     * Returns the event dispatcher.
     *
     * @return Dispatcher\ResourceEventDispatcher
     */
    public function getEventDispatcher()
    {
        if (null === $this->eventDispatcher) {
            $this->eventDispatcher = new Dispatcher\ResourceEventDispatcher();
            $this->eventDispatcher->setConfigurationRegistry($this->getConfigurationRegistry());
            $this->eventDispatcher->setEventQueue($this->getEventQueue());
        }

        return $this->eventDispatcher;
    }

    /**
     * Returns the locale provider.
     *
     * @return Locale\LocaleProviderInterface
     */
    public function getLocaleProvider()
    {
        if (null === $this->localeProvider) {
            $this->localeProvider = new Locale\LocaleProvider('fr', 'en', ['fr', 'en']);
        }

        return $this->localeProvider;
    }

    /**
     * Returns the event queue.
     *
     * @return Event\EventQueue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = new Event\EventQueue(
                $this->getConfigurationRegistry(),
                $this->getEventDispatcher()
            );
        }

        return $this->eventQueue;
    }

    /**
     * Returns the persistence event queue.
     *
     * @return Persistence\PersistenceEventQueue
     */
    public function getPersistenceEventQueue()
    {
        if (null === $this->persistenceEventQueue) {
            $this->persistenceEventQueue = new Persistence\PersistenceEventQueue(
                $this->getConfigurationRegistry(),
                $this->getEventDispatcher()
            );
        }

        return $this->persistenceEventQueue;
    }

    /**
     * Returns the persistence helper.
     *
     * @return Persistence\PersistenceHelperInterface
     */
    public function getPersistenceHelper()
    {
        if (null === $this->persistenceHelper) {
            $this->persistenceHelper = new Doctrine\ORM\PersistenceHelper(
                $this->em,
                $this->getPersistenceEventQueue()
            );
        }

        return $this->persistenceHelper;
    }
}
