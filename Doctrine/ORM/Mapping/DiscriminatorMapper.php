<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Mapping;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class DiscriminatorMapper
 * @package Ekyna\Component\Resource\Doctrine\ORM\Mapping
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @see https://medium.com/@jasperkuperus/defining-discriminator-maps-at-child-level-in-doctrine-2-1cd2ded95ffb#.zgi5ccx32
 */
class DiscriminatorMapper
{
    /**
     * The top-level class of the inheritance tree.
     *
     * @var string
     */
    private $baseClass;

    /**
     * The driver of Doctrine, can be used to find all loaded classes
     *
     * @var \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver|null
     */
    private $driver;

    /**
     * The cached map, this holds the results after a computation, also for other classes
     *
     * @var array
     */
    private $cachedMap;

    /**
     * The *temporary* map used for one run, when computing everything
     *
     * @var array
     */
    private $map;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     * @param string                 $baseClass
     */
    public function __construct(EntityManagerInterface $em, $baseClass)
    {
        // TODO option to exclude top level class from maps.

        $this->baseClass = $baseClass;
        $this->driver = $em->getConfiguration()->getMetadataDriverImpl();
        $this->cachedMap = [];
    }

    /**
     * @param ClassMetadata $metadata
     */
    public function processClassMetadata(ClassMetadata $metadata)
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
        if (($class === $this->baseClass || count($metadata->discriminatorMap) === 0)
            && $this->extractEntry($class)
        ) {
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
    private function overrideMetadata(ClassMetadata $metadata)
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
     * @param $class
     */
    private function checkFamily($class)
    {
        $rc = new \ReflectionClass($class);

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
     * @param $class
     */
    private function checkChildren($class)
    {
        foreach ($this->driver->getAllClassNames() as $name) {
            $cRc = new \ReflectionClass($name);
            if (!$cParent = $cRc->getParentClass()) {
                continue;
            }

            // Haven't done this class yet? Go for it.
            if (!array_key_exists($name, $this->map) && $cParent->name == $class && $this->extractEntry($name)) {
                $this->checkChildren($name);
            }
        }
    }

    /**
     * @param $class
     *
     * @return bool
     * @throws \Exception
     */
    /*private function extractEntry($class)
    {
        $annotations = \Namespace\To\Annotation::getAnnotationForClass($class);
        $success = false;

        if (array_key_exists(self::ENTRY_ANNOTATION, $annotations['class'])) {
            $value = $annotations['class'][self::ENTRY_ANNOTATION]->value;

            if (in_array($value, $this->map)) {
                throw new \Exception("Found duplicate discriminator map entry '" . $value . "' in " . $class);
            }

            $this->map[$class] = $value;
            $success = true;
        }

        return $success;
    }*/

    /**
     * Extracts the discriminator name from the given class.
     *
     * @param string $class
     *
     * @return string
     *
     * @return bool
     * @throws \Exception
     */
    private function extractEntry($class)
    {
        if ($class != $this->baseClass && !is_subclass_of($class, $this->baseClass)) {
            return false;
        }

        if (!preg_match_all('~/?([a-zA-Z0-9]+)~', $class, $namespaces)) {
            throw new \Exception("Unexpected class {$class}.");
        }

        $prefix = $suffix = null;
        $parts = $namespaces[0];

        foreach ($parts as $index => $namespace) {
            if ($namespace == 'Component') {
                $prefix = strtolower($parts[$index + 1]);
                break;
            } elseif (preg_match('~([a-zA-Z0-9]+)Bundle~', $namespace, $matches)) {
                $prefix = strtolower($matches[1]);
                break;
            }
        }

        $suffix = strtolower(end($parts));

        if (empty($prefix) || empty($suffix)) {
            throw new \Exception(
                "Failed to extract discriminator value from class '{$class}'."
            );
        }

        $value = $prefix . '_' . $suffix;

        if (in_array($value, $this->map)) {
            throw new \Exception("Found duplicate discriminator map entry '{$value}' in {$class}");
        }

        $this->map[$class] = $value;

        return true;
    }
}
