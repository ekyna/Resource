<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\PhpUnit;

use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;

/**
 * Class TestCase
 * @package Ekyna\Component\Resource\Tests\PhpUnit
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var array<string, MockObject>
     */
    private array $mocks;

    protected function tearDown(): void
    {
        $this->mocks = [];
    }

    /**
     * Returns the persistence helper mock.
     */
    protected function getPersistenceHelperMock(): PersistenceHelperInterface|MockObject
    {
        return $this->mockService(PersistenceHelperInterface::class);
    }

    /**
     * Returns the resource factory factory mock.
     */
    protected function getFactoryFactoryMock(): FactoryFactoryInterface|MockObject
    {
        return $this->mockService(FactoryFactoryInterface::class);
    }

    /**
     * Returns the resource repository factory mock.
     */
    protected function getRepositoryFactoryMock(): RepositoryFactoryInterface|MockObject
    {
        return $this->mockService(RepositoryFactoryInterface::class);
    }

    /**
     * Returns the resource manager factory mock.
     */
    protected function getManagerFactoryMock(): ManagerFactoryInterface|MockObject
    {
        return $this->mockService(ManagerFactoryInterface::class);
    }

    /**
     * @param string $interface
     *
     * @return bool
     */
    protected function hasMock(string $interface): bool
    {
        return isset($this->mocks[$interface]);
    }

    /**
     * @param string $interface
     *
     * @return MockObject
     */
    protected function getMock(string $interface): MockObject
    {
        if (!$this->hasMock($interface)) {
            throw new RuntimeException("$interface has not been mocked yet.");
        }

        return $this->mocks[$interface];
    }

    protected function mockService(string $interface): MockObject
    {
        if ($this->hasMock($interface)) {
            return $this->getMock($interface);
        }

        return $this->mocks[$interface] = parent::createMock($interface);
    }
}
