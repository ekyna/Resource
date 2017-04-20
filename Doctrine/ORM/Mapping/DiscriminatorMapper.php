<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Ekyna\Component\Resource\Exception\RuntimeException;
use ReflectionClass;

use function array_flip;
use function array_key_exists;
use function end;
use function in_array;
use function is_subclass_of;
use function preg_match;
use function preg_match_all;
use function strtolower;

/**
 * Class DiscriminatorMapper
 * @package Ekyna\Component\Resource\Doctrine\ORM\Mapping
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @see     https://medium.com/@jasperkuperus/defining-discriminator-maps-at-child-level-in-doctrine-2-1cd2ded95ffb#.zgi5ccx32
 */
class DiscriminatorMapper
{
    /**
     * The driver of Doctrine, can be used to find all loaded classes.
     */
    private MappingDriver $driver;

    /**
     * The top-level class of the inheritance tree.
     */
    private string $baseClass;

    /**
     * The cached map, this holds the results after a computation, also for other classes.
     */
    private array $cachedMap;

    /**
     * The *temporary* map used for one run, when computing everything.
     *
     * @var array
     */
    private array $map;


    /**
     * Constructor.
     *
     * @param MappingDriver $driver
     * @param string        $baseClass
     */
    public function __construct(MappingDriver $driver, string $baseClass)
    {
        // TODO option to exclude top level class from maps.

        $this->driver = $driver;
        $this->baseClass = $baseClass;
        $this->cachedMap = [];
    }

    /**
     * @param ClassMetadata $metadata
     */
    public function processClassMetadata(ClassMetadata $metadata): void
    {
        // Reset the temporary calculation map and get the classname
        $this->map = [];

        $class = $metadata->name;

        // Did we already calculate the map for this element?
        if (array_key_exists($class, $this->cachedMap)) {
            $this->overrideMetadata($metadata);

            return;
        }

        // Do we have to process this class?
        if (($class === $this->baseClass || empty($metadata->discriminatorMap)) && $this->extractEntry($class)) {
            // Now build the whole map
            $this->checkFamily($class);
        } else {
            // Nothing to do…
            return;
        }

        // Create the lookup entries
        $dMap = array_flip($this->map);
        foreach ($this->map as $cName => $discr) {
            $this->cachedMap[$cName]['map'] = $dMap;
            $this->cachedMap[$cName]['discr'] = $this->map[$cName];
        }

        // Override the data for this class
        $this->overrideMetadata($metadata);
    }

    /**
     * @param ClassMetadata $metadata
     */
    private function overrideMetadata(ClassMetadata $metadata): void
    {
        $class = $metadata->name;

        // Set the discriminator map and value
        $metadata->discriminatorMap = $this->cachedMap[$class]['map'];
        $metadata->discriminatorValue = $this->cachedMap[$class]['discr'];

        // If we are the top-most parent, set subclasses!
        if (isset($this->cachedMap[$class]['isParent']) && $this->cachedMap[$class]['isParent'] === true) {
            $subclasses = $this->cachedMap[$class]['map'];
            unset($subclasses[$this->cachedMap[$class]['discr']]);

            $metadata->subClasses = array_values($subclasses);
        }
    }

    /**
     * Checks the class's children recursively.
     *
     * @param string $class
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function checkFamily(string $class): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $rc = new ReflectionClass($class);

        if ($parent = $rc->getParentClass()) {
            // Also check all the children of our parent
            $this->checkFamily($parent->name);
        } else {
            // This is the top-most parent, used in overrideMetadata
            $this->cachedMap[$class]['isParent'] = true;
            // Find all the children of this class
            $this->checkChildren($class);
        }
    }

    /**
     * Checks the class's children recursively.
     *
     * @param string $class
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function checkChildren(string $class): void
    {
        foreach ($this->driver->getAllClassNames() as $name) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $rc = new ReflectionClass($name);
            if (!$parentRc = $rc->getParentClass()) {
                continue;
            }

            if (array_key_exists($name, $this->map)) {
                continue;
            }

            if ($parentRc->name !== $class) {
                continue;
            }

            if ($this->extractEntry($name)) {
                $this->checkChildren($name);
            }
        }
    }

    /**
     * Extracts the discriminator name from the given class.
     *
     * @param string $class
     *
     * @return bool
     */
    private function extractEntry(string $class): bool
    {
        if ($class != $this->baseClass && !is_subclass_of($class, $this->baseClass)) {
            return false;
        }

        if (!preg_match_all('~/?([a-zA-Z0-9]+)~', $class, $namespaces)) {
            throw new RuntimeException("Unexpected class {$class}.");
        }

        $prefix = $suffix = null;
        $parts = $namespaces[0];

        foreach ($parts as $index => $namespace) {
            if ($namespace === 'Component') {
                $prefix = strtolower($parts[$index + 1]);
                break;
            } elseif (preg_match('~([a-zA-Z0-9]+)Bundle~', $namespace, $matches)) {
                $prefix = strtolower($matches[1]);
                break;
            }
        }

        $suffix = strtolower(end($parts));

        if (empty($prefix) || empty($suffix)) {
            throw new RuntimeException("Failed to extract discriminator value from class '{$class}'.");
        }

        $value = $prefix . '_' . $suffix;

        if (in_array($value, $this->map)) {
            throw new RuntimeException("Found duplicate discriminator map entry '{$value}' in {$class}");
        }

        $this->map[$class] = $value;

        return true;
    }
}
