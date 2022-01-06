<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Copier\CopyInterface;

/**
 * Class AbstractTranslatable
 * @package Ekyna\Component\Resource\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatable extends AbstractResource implements TranslatableInterface, CopyInterface
{
    use TranslatableTrait;

    public function onCopy(CopierInterface $copier): void
    {
        $this->translations = $copier->copyCollection($this->translations, true);
    }
}
