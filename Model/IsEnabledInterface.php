<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Interface IsEnabledInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface IsEnabledInterface
{
    /**
     * Sets whether the resource is enabled or not.
     *
     * @param bool $enabled
     *
     * @return $this|IsEnabledInterface
     */
    public function setEnabled(bool $enabled): IsEnabledInterface;

    /**
     * Returns whether the resource is enabled or not.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
