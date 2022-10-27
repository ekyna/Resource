<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Anniversary
 * @package Ekyna\Component\Resource\Bridge\Symfony\Validator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Anniversary extends Constraint
{
    /**
     * @inheritDoc
     */
    public function getTargets(): string|array
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
