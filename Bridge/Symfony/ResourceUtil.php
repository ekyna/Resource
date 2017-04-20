<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony;

use Ekyna\Component\Resource\Config\ResourceConfig;

/**
 * Class ResourceUtil
 * @package Ekyna\Component\Resource\Bridge\Symfony
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class ResourceUtil
{
    /**
     * Returns the resource class parameter.
     *
     * @param ResourceConfig $resource
     *
     * @return string
     */
    public static function getResourceClassParameter(ResourceConfig $resource): string
    {
        return self::getClassParameter($resource->getNamespace(), $resource->getName());
    }

    /**
     * Returns the resource translation class parameter.
     *
     * @param ResourceConfig $resource
     *
     * @return string
     */
    public static function getResourceTranslationClassParameter(ResourceConfig $resource): string
    {
        return self::getTranslationClassParameter($resource->getNamespace(), $resource->getName());
    }

    /**
     * Returns the resource class parameter.
     *
     * @param string $namespace
     * @param string $name
     *
     * @return string
     */
    public static function getClassParameter(string $namespace, string $name): string
    {
        return sprintf('%s.class.%s', $namespace, $name);
    }

    /**
     * Returns the resource translation class parameter.
     *
     * @param string $namespace
     * @param string $name
     *
     * @return string
     */
    public static function getTranslationClassParameter(string $namespace, string $name): string
    {
        return sprintf('%s.class.%s_translation', $namespace, $name);
    }

    /**
     * Returns the service id.
     *
     * @param string         $service
     * @param ResourceConfig $resource
     * @param bool           $translation
     *
     * @return string
     */
    public static function getServiceId(string $service, ResourceConfig $resource, bool $translation = false): string
    {
        return sprintf(
            '%s.%s.%s',
            $resource->getNamespace(),
            $service,
            $resource->getName() . ($translation ? '_translation' : '')
        );
    }
}
