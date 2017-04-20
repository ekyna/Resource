<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config;

/**
 * Class ActionConfig
 * @package Ekyna\Component\Resource\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ActionConfig extends AbstractConfig
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
     * Returns the action route name.
     *
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->getData('route');
    }

    /**
     * Returns the action permission name.
     *
     * @return string|null
     */
    public function getPermission(): ?string
    {
        return $this->getData('permission');
    }

    /**
     * Returns the button.
     *
     * @return array|null
     */
    public function getButton(): ?array
    {
        return $this->getData('button');
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
