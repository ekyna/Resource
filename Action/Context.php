<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Action;

use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Context
 * @package Ekyna\Component\Resource\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Context
{
    protected ResourceConfig $config;
    protected ?ResourceInterface $resource = null;
    protected ?Context $parent = null;


    /**
     * Constructor.
     *
     * @param ResourceConfig $config The resource configuration.
     */
    public function __construct(ResourceConfig $config)
    {
        $this->config  = $config;
    }

    /**
     * Returns the config.
     *
     * @return ResourceConfig
     */
    public function getConfig(): ResourceConfig
    {
        return $this->config;
    }

    /**
     * Sets the resource.
     *
     * @param ResourceInterface $resource
     */
    public function setResource(ResourceInterface $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource.
     *
     * @return ResourceInterface|null
     */
    public function getResource(): ?ResourceInterface
    {
        return $this->resource;
    }

    /**
     * Sets the parent context.
     *
     * @param Context $parent
     */
    public function setParent(Context $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Returns the parent context.
     *
     * @return Context|null
     */
    public function getParent(): ?Context
    {
        return $this->parent;
    }

    /**
     * Returns the parent resource.
     *
     * @return ResourceInterface|null
     */
    public function getParentResource(): ?ResourceInterface
    {
        if ($this->parent) {
            return $this->parent->getResource();
        }

        return null;
    }
}
