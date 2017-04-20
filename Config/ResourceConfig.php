<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config;

use Closure;

use function array_key_exists;
use function in_array;
use function lcfirst;
use function sprintf;
use function str_replace;
use function ucwords;

/**
 * Class ResourceMetadata
 * @package Ekyna\Component\Resource\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceConfig extends AbstractConfig
{
    private Closure $childrenLoader;
    /** @var ResourceConfig[] */
    private ?array $children = null;


    /**
     * Returns the name (eg "order_item").
     */
    public function getName(): string
    {
        return $this->getData('name');
    }

    /**
     * Returns the namespace (eg "acme_commerce").
     */
    public function getNamespace(): string
    {
        return $this->getData('namespace');
    }

    /**
     * Returns the name with dashed format (eg "order-item").
     */
    public function getDashedName(): string
    {
        return strtr($this->getName(), ['_' => '-']);
    }

    /**
     * Returns the name with camel case format (eg "orderItem").
     */
    public function getCamelCaseName(): string
    {
        return lcfirst(str_replace([' ', '_', '-'], '', ucwords($this->getName(), ' _-')));
    }

    /**
     * Returns the id (eg "acme_commerce.order_item").
     */
    public function getId(): string
    {
        return sprintf('%s.%s', $this->getNamespace(), $this->getName());
    }

    /**
     * Returns the underscore id (eg "acme_commerce_order_item").
     */
    public function getUnderscoreId(): string
    {
        return sprintf('%s_%s', $this->getNamespace(), $this->getName());
    }

    /**
     * Returns the parent resource id (eg "acme_commerce.order").
     */
    public function getParentId(): ?string
    {
        return $this->getData('parent');
    }

    public function getDriver(): string
    {
        return $this->getData('driver');
    }

    /** TODO Rename to getInterface() */
    public function getEntityInterface(): ?string
    {
        return $this->getData('entity')['interface'];
    }

    /** @TODO Rename to getClass() */
    public function getEntityClass(): string
    {
        return $this->getData('entity')['class'];
    }

    public function getRepositoryInterface(): ?string
    {
        return $this->getData('repository')['interface'];
    }

    public function getRepositoryClass(): string
    {
        return $this->getData('repository')['class'];
    }

    public function getManagerInterface(): ?string
    {
        return $this->getData('manager')['interface'];
    }

    public function getManagerClass(): string
    {
        return $this->getData('manager')['class'];
    }

    public function getFactoryInterface(): ?string
    {
        return $this->getData('factory')['interface'];
    }

    public function getFactoryClass(): string
    {
        return $this->getData('factory')['class'];
    }

    public function getTranslationInterface(): ?string
    {
        return $this->getTranslation('interface');
    }

    public function getTranslationClass(): ?string
    {
        return $this->getTranslation('class');
    }

    /**
     * @return array<string>
     */
    public function getTranslationFields(): ?array
    {
        return $this->getTranslation('fields');
    }

    public function getTransPrefix(): string
    {
        return $this->getData('trans_prefix')
            ?? ($this->getData('trans_domain') ? $this->getName() : $this->getId());
    }

    public function getTransDomain(): ?string
    {
        return $this->getData('trans_domain') ?? null;
    }

    public function getResourceLabel(bool $plural = false): string // TODO rename to label (check usage in templates)
    {
        return sprintf('%s.label.%s', $this->getTransPrefix(), $plural ? 'plural' : 'singular');
    }

    public function getEventClass(): string
    {
        return $this->getData('event')['class'];
    }

    public function getEventPriority(): int
    {
        return $this->getData('event')['priority'];
    }

    public function getEventName(string $action, bool $translation = false): string
    {
        $name = $this->getName();

        if ($translation) {
            $name .= '_translation';
        }

        return sprintf('%s.%s.%s', $this->getNamespace(), $name, $action);
    }

    public function getActions(): array
    {
        return $this->getData('actions');
    }

    public function getAction(string $name): ?array
    {
        return $this->getActions()[$name] ?? null;
    }

    public function hasAction(string $name): bool
    {
        return array_key_exists($name, $this->getActions());
    }

    public function getBehaviors(): array
    {
        return $this->getData('behaviors');
    }

    public function getBehavior(string $name): ?array
    {
        return $this->getBehaviors()[$name] ?? null;
    }

    public function hasBehavior(string $name): bool
    {
        return array_key_exists($name, $this->getBehaviors());
    }

    public function getPermissions(): array
    {
        return $this->getData('permissions');
    }

    public function hasPermission(string $name): bool
    {
        return in_array($name, $this->getPermissions(), true);
    }

    /**
     * @internal
     */
    public function setChildrenLoader(Closure $loader): void
    {
        $this->childrenLoader = Closure::fromCallable($loader);
    }

    /**
     * @return array<ResourceConfig>
     */
    public function getChildren(): array
    {
        if (null === $this->children) {
            $this->children = $this->childrenLoader->call($this);
        }

        return $this->children;
    }

    /**
     * @return array|string|null
     */
    private function getTranslation(string $key)
    {
        if ($config = $this->getData('translation')) {
            return $config[$key] ?? null;
        }

        return null;
    }
}
