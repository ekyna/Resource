<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Factory;

use Acme\Resource\Entity\Foo;
use Ekyna\Component\Resource\Config\Cache\Config;
use Ekyna\Component\Resource\Config\PermissionConfig;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Config\Registry\PermissionRegistryInterface;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigTest
 * @package Ekyna\Component\Resource\Tests\Config\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigTest extends TestCase
{
    private function create(array $data = null): Config
    {
        return Config::create(__DIR__ . '/../../app/cache', $data, false);
    }

    public function test_with_invalidCacheDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Config::create(__DIR__ . '/invalid', null, false);
    }

    public function test_validate_withDefaults(): void
    {
        $config = $this->create();
        self::assertTrue($config->validate());
    }

    public function test_validate_withUnexpectedRegistryName(): void
    {
        $config = $this->create([
            'foo' => null,
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Unexpected registry name 'foo'.");
        $config->validate();
    }

    public function test_validate_withUnexpectedConfigType(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => null,
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Expected array, got NULL.');
        $config->validate();
    }

    public function test_validate_withUnexpectedConfigKey(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => [
                'foo' => null,
                'bar' => null,
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Unexpected key(s): foo, bar');
        $config->validate();
    }

    public function test_validate_withUnknownRegistryClass(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => [
                Config::REGISTRY => 'UnknownFoo',
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Class 'UnknownFoo' does not exist");
        $config->validate();
    }

    public function test_validate_withInvalidRegistryClass(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => [
                Config::REGISTRY => Foo::class,
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            "Class '%s' must implements '%s'.",
            Foo::class,
            PermissionRegistryInterface::class
        ));
        $config->validate();
    }

    public function test_validate_withUnknownConfigClass(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => [
                Config::CONFIG => 'UnknownFoo',
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Class 'UnknownFoo' does not exist");
        $config->validate();
    }

    public function test_validate_withInvalidConfigClass(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => [
                Config::CONFIG => Foo::class,
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            "Class '%s' must inherit from '%s'.",
            Foo::class,
            PermissionConfig::class
        ));
        $config->validate();
    }

    public function test_validate_withInvalidDataFilename(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => [
                Config::DATA => 'foo',
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Invalid file name 'foo'.");
        $config->validate();
    }

    public function test_validate_withInvalidClassesFilename(): void
    {
        $config = $this->create([
            ActionRegistryInterface::NAME => [
                Config::ALIASES => 'foo',
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Invalid file name 'foo'.");
        $config->validate();
    }

    public function test_validate_notNullClassesFilename(): void
    {
        $config = $this->create([
            PermissionRegistryInterface::NAME => [
                Config::ALIASES => 'foo',
            ],
        ]);

        self::assertFalse($config->validate(false));

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('File name should be NULL.');
        $config->validate();
    }

    public function test_get_withValidNameAndKey(): void
    {
        $config = $this->create();

        self::assertNotEmpty($config->getData(PermissionRegistryInterface::NAME, Config::REGISTRY));
    }

    public function test_get_withInvalidName(): void
    {
        $config = $this->create();

        $this->expectException(UnexpectedValueException::class);

        $config->getData('foo', Config::REGISTRY);
    }

    public function test_get_withInvalidKey(): void
    {
        $config = $this->create();

        $this->expectException(UnexpectedValueException::class);

        $config->getData(PermissionRegistryInterface::NAME, 'foo');
    }
}
