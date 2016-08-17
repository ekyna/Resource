<?php

namespace Ekyna\Component\Resource\Exception;

/**
 * Class NotFoundConfigurationException
 * @package Ekyna\Component\Resource\Exception
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotFoundConfigurationException extends \InvalidArgumentException implements ResourceExceptionInterface
{
    /**
     * @param string $resource
     */
    public function __construct($resource)
    {
        parent::__construct(sprintf(
            'Unable to find configuration for resource "%s".',
            is_object($resource) ? get_class($resource) : $resource
        ));
    }
}
