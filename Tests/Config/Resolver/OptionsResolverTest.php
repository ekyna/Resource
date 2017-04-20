<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Resolver;

use Acme\Resource\Action\FooAction;
use Ekyna\Component\Resource\Config\Resolver\OptionsResolver;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Extension\AbstractExtension;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class OptionsResolverTest
 * @package Ekyna\Component\Resource\Tests\Config\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OptionsResolverTest extends TestCase
{
    private ?OptionsResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new OptionsResolver([
            new class extends AbstractExtension {
                public function extendActionOptions(SymfonyResolver $resolver): void
                {
                    $resolver->setDefaults(['john' => 'doe']);
                }
            }
        ], [
            'test' => [
                'class' => FooAction::class,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->resolver = null;
    }

    public function testUndefinedConfig()
    {
        $this->expectException(ConfigurationException::class);

        $this->resolver->resolve('foo', []);
    }

    public function testResolve()
    {
        $options = $this->resolver->resolve('test', []);

        self::assertEquals(['foo' => 'bar', 'john' => 'doe'], $options);
    }
}
