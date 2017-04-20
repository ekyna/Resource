<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Cache;

use Ekyna\Component\Resource\Config as Cnf;
use Ekyna\Component\Resource\Config\Registry as Reg;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Exception\UnexpectedValueException;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_replace_recursive;
use function class_exists;
use function is_array;
use function is_dir;
use function is_null;
use function is_subclass_of;
use function mkdir;
use function preg_match;
use function rtrim;
use function sprintf;

/**
 * Class Config
 * @package Ekyna\Component\Resource\Config\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Config
{
    public const DIRECTORY = '/ekyna/resource';

    public const REGISTRY = 'registry';
    public const CONFIG   = 'config';
    public const DATA     = 'data';
    public const ALIASES  = 'aliases';

    public const REGISTRIES = [
        Reg\PermissionRegistryInterface::NAME,
        Reg\ActionRegistryInterface::NAME,
        Reg\BehaviorRegistryInterface::NAME,
        Reg\NamespaceRegistryInterface::NAME,
        Reg\ResourceRegistryInterface::NAME,
    ];

    private const FILE_REGEX = '~^[a-z][a-z0-9_]+\.php$~';

    private string $cacheDir;
    private array  $data;


    /**
     * Creates the registry configurations.
     *
     * @param string     $cacheDir
     * @param array|null $data
     * @param bool       $validate
     *
     * @return Config
     * @throws ConfigurationException
     */
    public static function create(string $cacheDir, array $data = null, bool $validate = true): self
    {
        $data = array_replace_recursive([
            Reg\PermissionRegistryInterface::NAME => [
                self::REGISTRY => Reg\PermissionRegistry::class,
                self::CONFIG   => Cnf\PermissionConfig::class,
                self::DATA     => 'permissions.php',
                self::ALIASES  => null,
            ],
            Reg\ActionRegistryInterface::NAME     => [
                self::REGISTRY => Reg\ActionRegistry::class,
                self::CONFIG   => Cnf\ActionConfig::class,
                self::DATA     => 'actions.php',
                self::ALIASES  => 'actions_map.php',
            ],
            Reg\BehaviorRegistryInterface::NAME   => [
                self::REGISTRY => Reg\BehaviorRegistry::class,
                self::CONFIG   => Cnf\BehaviorConfig::class,
                self::DATA     => 'behaviors.php',
                self::ALIASES  => 'behaviors_map.php',
            ],
            Reg\NamespaceRegistryInterface::NAME  => [
                self::REGISTRY => Reg\NamespaceRegistry::class,
                self::CONFIG   => Cnf\NamespaceConfig::class,
                self::DATA     => 'namespaces.php',
                self::ALIASES  => null,
            ],
            Reg\ResourceRegistryInterface::NAME   => [
                self::REGISTRY => Reg\ResourceRegistry::class,
                self::CONFIG   => Cnf\ResourceConfig::class,
                self::DATA     => 'resources.php',
                self::ALIASES  => 'resources_map.php',
            ],
        ], (array)$data);

        if (is_null($data)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new ConfigurationException('Failed to merge registries configurations.');
        }

        // Create the cache directory
        $cacheDir = rtrim($cacheDir, '/');
        if (!is_dir($cacheDir)) {
            throw new InvalidArgumentException("Directory '$cacheDir' does not exist.");
        }
        foreach (['ekyna', 'resource'] as $directory) {
            $cacheDir .= '/' . $directory;
            if (!is_dir($cacheDir) && !mkdir($cacheDir)) {
                throw new RuntimeException("Failed to create '$cacheDir'.");
            }
        }

        $config = new self($cacheDir, $data);

        /** @noinspection PhpUnhandledExceptionInspection */
        $validate && $config->validate();

        return $config;
    }

    /**
     * Constructor.
     *
     * @param string $cacheDir
     * @param array  $data
     */
    private function __construct(string $cacheDir, array $data)
    {
        $this->cacheDir = $cacheDir;
        $this->data = $data;
    }

    /**
     * Validates the configuration.
     *
     * @param bool $throwException
     *
     * @return bool
     * @throws ConfigurationException
     */
    public function validate(bool $throwException = true): bool
    {
        $validation = [
            Reg\PermissionRegistryInterface::NAME => [
                self::REGISTRY => Reg\PermissionRegistryInterface::class,
                self::CONFIG   => Cnf\PermissionConfig::class,
                self::DATA     => self::FILE_REGEX,
                self::ALIASES  => null,
            ],
            Reg\ActionRegistryInterface::NAME     => [
                self::REGISTRY => Reg\ActionRegistryInterface::class,
                self::CONFIG   => Cnf\ActionConfig::class,
                self::DATA     => self::FILE_REGEX,
                self::ALIASES  => self::FILE_REGEX,
            ],
            Reg\BehaviorRegistryInterface::NAME   => [
                self::REGISTRY => Reg\BehaviorRegistryInterface::class,
                self::CONFIG   => Cnf\BehaviorConfig::class,
                self::DATA     => self::FILE_REGEX,
                self::ALIASES  => self::FILE_REGEX,
            ],
            Reg\NamespaceRegistryInterface::NAME  => [
                self::REGISTRY => Reg\NamespaceRegistryInterface::class,
                self::CONFIG   => Cnf\NamespaceConfig::class,
                self::DATA     => self::FILE_REGEX,
                self::ALIASES  => null,
            ],
            Reg\ResourceRegistryInterface::NAME   => [
                self::REGISTRY => Reg\ResourceRegistryInterface::class,
                self::CONFIG   => Cnf\ResourceConfig::class,
                self::DATA     => self::FILE_REGEX,
                self::ALIASES  => self::FILE_REGEX,
            ],
        ];

        foreach ($this->data as $name => $config) {
            if (!isset($validation[$name])) {
                if ($throwException) {
                    throw new ConfigurationException("Unexpected registry name '$name'.");
                }

                return false;
            }

            if (!is_array($config)) {
                if ($throwException) {
                    throw new ConfigurationException(sprintf('Expected array, got %s.', gettype($config)));
                }

                return false;
            }

            if (!$this->validateConfig($config, $validation[$name], $throwException)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates the given registry configuration.
     *
     * @param array $config
     * @param array $constraints
     * @param bool  $throwException
     *
     * @return bool
     * @throws ConfigurationException
     */
    private function validateConfig(array $config, array $constraints, bool $throwException = true): bool
    {
        $allowed = [
            self::REGISTRY,
            self::CONFIG,
            self::DATA,
            self::ALIASES,
        ];

        // Check defined keys
        if (!empty($keys = array_diff(array_keys($config), $allowed))) {
            if ($throwException) {
                throw new ConfigurationException('Unexpected key(s): ' . implode(', ', $keys));
            }

            return false;
        }

        // Validate registry class
        if (!class_exists($class = $config[self::REGISTRY])) {
            if ($throwException) {
                throw new ConfigurationException("Class '$class' does not exist");
            }

            return false;
        }
        if (!is_subclass_of($class, $interface = $constraints[self::REGISTRY])) {
            if ($throwException) {
                throw new ConfigurationException("Class '$class' must implements '$interface'.");
            }

            return false;
        }

        // Validate config class
        if (!class_exists($class = $config[self::CONFIG])) {
            if ($throwException) {
                throw new ConfigurationException("Class '$class' does not exist");
            }

            return false;
        }
        $parent = $constraints[self::CONFIG];
        if ($class !== $parent && !is_subclass_of($class, $parent)) {
            if ($throwException) {
                throw new ConfigurationException("Class '$class' must inherit from '$parent'.");
            }

            return false;
        }

        // Validate data file name
        if (!preg_match($constraints[self::DATA], $config[self::DATA])) {
            if ($throwException) {
                throw new ConfigurationException("Invalid file name '{$config[self::DATA]}'.");
            }

            return false;
        }

        // Validate classes file name
        if (null !== $regex = $constraints[self::ALIASES]) {
            if (!preg_match($regex, $config[self::ALIASES])) {
                if ($throwException) {
                    throw new ConfigurationException("Invalid file name '{$config[self::ALIASES]}'.");
                }

                return false;
            }
        } elseif (null !== $config[self::ALIASES]) {
            if ($throwException) {
                throw new ConfigurationException('File name should be NULL.');
            }

            return false;
        }

        return true;
    }

    /**
     * Returns the cache directory.
     *
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * Returns the data by name and key.
     *
     * @param string $name
     * @param string $key
     *
     * @return string|null
     */
    public function getData(string $name, string $key): ?string
    {
        if (!isset($this->data[$name]) || !array_key_exists($key, $this->data[$name])) {
            throw new UnexpectedValueException("Configuration '$name.$key' is not defined.");
        }

        return $this->data[$name][$key];
    }
}
