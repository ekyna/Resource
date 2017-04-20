<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Resolver;

use Ekyna\Component\Resource\Exception\ConfigurationException;

use function array_replace_recursive;
use function is_null;

/**
 * Class DefaultsResolver
 * @package Ekyna\Component\Resource\Config\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultsResolver
{
    private array $data = [];


    /**
     * Adds the default data.
     *
     * @throws ConfigurationException
     */
    public function add(array $default): void
    {
        $data = array_replace_recursive($this->data, $default);

        if (is_null($data)) {
            throw new ConfigurationException('Failed to merge default options.');
        }

        $this->data = $data;
    }

    /**
     * Returns the default data.
     */
    public function get(): array
    {
        return $this->data;
    }
}
