<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

/**
 * Class AbstractResource
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractResource implements ResourceInterface
{
    use ResourceTrait;

    public function __clone()
    {
        $this->id = null;
    }
}
