<?php

declare(strict_types=1);

namespace Acme\Resource\Action;

use Ekyna\Component\Resource\Action\AbstractAction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FooAction
 * @package Acme\Resource\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FooAction extends AbstractAction
{
    public function __invoke(): Response
    {
        return new Response('Foo !');
    }

    public static function configureAction(): array
    {
        return [];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('foo', 'bar');
    }
}
