<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config;

/**
 * Class BehaviorConfig
 * @package Ekyna\Component\Resource\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BehaviorConfig extends AbstractConfig
{
    /**
     * Returns the class.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->getData('class');
    }

    /**
     * Returns the interface.
     *
     * @return string|null
     */
    public function getInterface(): ?string
    {
        return $this->getData('interface');
    }

    /**
     * Returns the operations.
     *
     * @return array
     */
    public function getOperations(): array
    {
        return $this->getData('operations');
    }

    /**
     * Returns the default options.
     *
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return $this->getData('options') ?? [];
    }
}
