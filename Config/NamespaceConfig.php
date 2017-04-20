<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config;

/**
 * Class NamespaceConfig
 * @package Ekyna\Component\Resource\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NamespaceConfig extends AbstractConfig
{
    /**
     * Returns the routing prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->getData('prefix');
    }

    /**
     * Returns the namespace label.
     */
    public function getLabel(): string
    {
        if (!empty($label = $this->getData('label'))) {
            return $label;
        }

        return $this->getName() . '.label';
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
