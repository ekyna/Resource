<?php

declare(strict_types=1);

namespace Acme\Resource\Action;

use Ekyna\Component\Resource\Action\AbstractAction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BarAction
 * @package Acme\Resource\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BarAction extends AbstractAction
{
    public function __invoke(): Response
    {
        return new Response('Bar.');
    }

    public static function configureAction(): array
    {
        return [];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('bar', 'foo');
    }
}
