<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

/**
 * Interface ResourceHelperAwareInterface
 * @package Ekyna\Component\Resource\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceHelperAwareInterface
{
    public function setResourceHelper(ResourceHelperInterface $helper): void;

    public function getResourceHelper(): ResourceHelperInterface;
}
