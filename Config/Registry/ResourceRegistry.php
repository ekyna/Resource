<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Registry;

use Closure;
use Doctrine\Common\Util\ClassUtils;
use Ekyna\Component\Resource\Config\AbstractConfig;
use Ekyna\Component\Resource\Config\Loader\ChildrenLoader;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\NotFoundConfigurationException;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;
use Generator;

use function class_exists;
use function get_class;
use function is_object;
use function is_string;
use function is_subclass_of;
use function ksort;
use function sprintf;

/**
 * Class ResourceRegistry
 * @package      Ekyna\Component\Resource\Config\Registry
 * @author       Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Generator|ResourceConfig[] all()
 * @noinspection PhpSuperClassIncompatibleWithInterfaceInspection
 */
class ResourceRegistry extends AbstractRegistry implements ResourceRegistryInterface
{
    protected ?array $parentMap        = null;
    protected ?array $depthMap        = null;
    protected ?array $eventPriorityMap = null;
    protected ?Closure $childrenLoader = null;


    /**
     * @inheritDoc
     */
    public function find($resource, bool $throwException = true): ?ResourceConfig
    {
        if ($resource instanceof ResourceInterface) {
            $resource = get_class($resource);
        }

        if (!is_string($resource)) {
            throw new UnexpectedTypeException($resource, ['string', ResourceInterface::class]);
        }

        if (class_exists($resource)) {
            if (is_subclass_of($resource, TranslationInterface::class)) {
                throw new LogicException('You must use the findByTranslation() method.');
            }

            $resource = ClassUtils::getRealClass($resource);
        }

        if ($this->has($resource)) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->get($resource);
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($resource);
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * @TODO Rework / Remove (allow translation with find) ?
     */
    public function findByTranslation($translation, bool $throwException = true): ?ResourceConfig
    {
        if (is_object($translation)) {
            if (!$translation instanceof TranslationInterface) {
                throw new LogicException($translation, TranslationInterface::class);
            }

            if (null !== $translatable = $translation->getTranslatable()) {
                return $this->find($translatable, $throwException);
            }

            $translation = get_class($translation);
        }

        if (is_string($translation)) {
            if (class_exists($translation)) {
                if (!is_subclass_of($translation, TranslationInterface::class)) {
                    throw new LogicException(sprintf(
                        'Expected subclass of %s, got %s',
                        TranslationInterface::class,
                        $translation
                    ));
                }

                $translation = ClassUtils::getRealClass($translation);
            }

            if ($this->has($translation)) {
                /** @noinspection PhpIncompatibleReturnTypeInspection */
                return $this->get($translation);
            }
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($translation);
        }

        return null;
    }

    public function getParentMap(): array
    {
        if (null !== $this->parentMap) {
            return $this->parentMap;
        }

        return $this->parentMap = $this->buildParentMap();
    }

    public function getDepthMap(): array
    {
        if (null !== $this->depthMap) {
            return $this->depthMap;
        }

        return $this->depthMap = $this->buildDepthMap();
    }

    public function getEventPriorityMap(): array
    {
        if (null !== $this->eventPriorityMap) {
            return $this->eventPriorityMap;
        }

        return $this->eventPriorityMap = $this->buildEventPriorityMap();
    }

    /**
     * @param ResourceConfig $config
     */
    protected function create(AbstractConfig $config): void
    {
        $config->setChildrenLoader(ChildrenLoader::create($this));
    }

    /**
     * Builds the parent map.
     */
    private function buildParentMap(): array
    {
        $map = [];

        foreach ($this->all() as $config) {
            if (null === $parentId = $config->getParentId()) {
                continue;
            }

            if ($parentId === $id = $config->getId()) {
                throw new LogicException("Circular reference $parentId <=> $id");
            }

            if (isset($map[$parentId]) && ($map[$parentId] === $id)) {
                throw new LogicException("Circular reference $parentId <=> $id");
            }

            $map[$id] = $parentId;
        }

        ksort($map);

        return $map;
    }

    /**
     * Builds the depth map.
     */
    private function buildDepthMap(): array
    {
        $map = [];

        foreach ($this->all() as $config) {
            $depth = 0;

            $parent = $config;
            while (null !== $parentId = $parent->getParentId()) {
                $depth++;
                $parent = $this->find($parentId);
            }

            $map[$config->getId()] = $depth;
        }

        ksort($map);

        return $map;
    }

    /**
     * Builds the event priority map.
     */
    private function buildEventPriorityMap(): array
    {
        $map = [];

        foreach ($this->all() as $config) {
            if (0 != $priority = $config->getEventPriority()) {
                $map[$config->getId()] = $priority;
            }
        }

        ksort($map);

        return $map;
    }
}
