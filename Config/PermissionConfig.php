<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config;

/**
 * Class PermissionConfig
 * @package Ekyna\Component\Resource\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PermissionConfig extends AbstractConfig
{
    /**
     * Returns the namespace label.
     */
    public function getLabel(): string
    {
        return $this->getData('label');
    }

    /**
     * Returns the translation domain.
     *
     * @return string|null
     */
    public function getTransDomain(): ?string
    {
        return $this->getData('trans_domain');
    }
}
