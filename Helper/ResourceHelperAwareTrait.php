<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

/**
 * Trait ResourceHelperAwareTrait
 * @package Ekyna\Component\Resource\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ResourceHelperAwareTrait
{
    private ResourceHelperInterface $helper;

    public function setResourceHelper(ResourceHelperInterface $helper): void
    {
        $this->helper = $helper;
    }

    public function getResourceHelper(): ResourceHelperInterface
    {
        return $this->helper;
    }
}
