<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config;

/**
 * Class AbstractConfig
 * @package Ekyna\Component\Resource\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractConfig
{
    protected string $name;
    protected array  $data;

    /**
     * Constructor.
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct(string $name, array $options)
    {
        $this->name = $name;
        $this->data = $options;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the data for the given key.
     *
     * @param string $key
     *
     * @return array|string|int|bool|null
     */
    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }
}
