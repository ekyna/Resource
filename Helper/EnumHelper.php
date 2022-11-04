<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

use Ekyna\Component\Resource\Enum\ColorInterface;
use Ekyna\Component\Resource\Enum\LabelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class EnumHelper
 * @package Ekyna\Bundle\ResourceBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EnumHelper
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function label(?LabelInterface $value): string
    {
        if (null === $value) {
            return $this->translator->trans('enum.undefined', [], 'EkynaResource');
        }

        return $value->label()->trans($this->translator);
    }

    public function badge(?LabelInterface $value): string
    {
        $color = $value instanceof ColorInterface ? $value->color() : 'grey';

        return sprintf('<span class="label label-%s">%s</span>', $color, $this->label($value));
    }
}
