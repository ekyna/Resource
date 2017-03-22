<?php

namespace Ekyna\Component\Resource\Exception;

/**
 * Interface ResourceExceptionInterface
 * @package Ekyna\Component\Resource\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ResourceExceptionInterface
{
    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage();
}
