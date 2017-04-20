<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Doctrine\ORM\Factory;

use Acme\Resource\Entity\Foo;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\FactoryFactoryAdapter;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Doctrine\ORM\OrmExtension;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FactoryFactoryAdapterTest
 * @package Ekyna\Component\Resource\Tests\Doctrine\ORM\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FactoryFactoryAdapterTest extends TestCase
{
    private ?MockObject            $managerRegistry;
    private ?FactoryFactoryAdapter $adapter;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->adapter = new FactoryFactoryAdapter($this->managerRegistry);
    }

    protected function tearDown(): void
    {
        $this->adapter = null;
        $this->managerRegistry = null;
    }

    public function testCreateFactory(): void
    {
        $config = $this->createMock(ResourceConfig::class);
        $config
            ->expects(self::once())
            ->method('getFactoryClass')
            ->willReturn(ResourceFactory::class);

        self::assertInstanceOf(ResourceFactoryInterface::class, $this->adapter->createFactory($config));

        $config = $this->createMock(ResourceConfig::class);
        $config
            ->expects(self::once())
            ->method('getFactoryClass')
            ->willReturn(Foo::class);

        self::expectException(UnexpectedTypeException::class);

        $this->adapter->createFactory($config);
    }

    public function testSupports(): void
    {
        $config = $this->createMock(ResourceConfig::class);
        $config
            ->expects(self::once())
            ->method('getDriver')
            ->willReturn(OrmExtension::DRIVER);

        self::assertTrue($this->adapter->supports($config));

        $config = $this->createMock(ResourceConfig::class);
        $config
            ->expects(self::once())
            ->method('getDriver')
            ->willReturn('unsupported');

        self::assertFalse($this->adapter->supports($config));
    }
}
