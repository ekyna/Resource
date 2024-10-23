<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Resolver;

use Acme\Resource\Action\FooAction;
use Acme\Resource\Behavior\FooBehavior;
use Acme\Resource\Behavior\FooInterface;
use Acme\Resource\Entity;
use Acme\Resource\Repository;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Config\Resolver\ConfigResolver;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Extension\CoreExtension;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigResolverTest
 * @package Ekyna\Component\Resource\Tests\Config\Resolver
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigResolverTest extends TestCase
{
    private const RESOURCE_DEFAULTS = [
        'driver'       => 'doctrine/orm',
        'namespace'    => null,
        'name'         => null,
        'entity'       => null,
        'repository'   => null,
        'manager'      => null,
        'factory'      => null,
        'translation'  => null,
        'parent'       => null,
        'event'        => [
            'class'    => ResourceEvent::class,
            'priority' => 0,
        ],
        'actions'      => [],
        'behaviors'    => [],
        'permissions'  => [],
        'trans_prefix' => null,
        'trans_domain' => null,
        'search'       => null,
    ];

    private const PERMISSION_DEFAULTS = [
        'name'         => null,
        'label'        => null,
        'trans_domain' => null,
    ];

    private const NAMESPACE_DEFAULTS = [
        'name'         => null,
        'prefix'       => null,
        'label'        => null,
        'trans_domain' => null,
    ];

    private const ACTION_DEFAULTS = [
        'name'        => null,
        'class'       => null,
        'permissions' => null,
        'options'     => [
            'expose' => false,
        ],
    ];

    private const BEHAVIOR_DEFAULTS = [
        'name'       => null,
        'class'      => null,
        'interface'  => null,
        'operations' => null,
        'options'    => [],
    ];

    private ?ConfigResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ConfigResolver([new CoreExtension()]);
    }

    protected function tearDown(): void
    {
        $this->resolver = null;
    }

    /**
     * @param array $input
     * @param array $output
     *
     * @dataProvider validPermissionConfigProvider
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testValidPermissionConfig(array $input, array $output): void
    {
        $result = $this->resolver->resolvePermissionConfig($input);

        self::assertEquals($output, $result);
    }

    /**
     * @param array $input
     *
     * @dataProvider invalidPermissionConfigProvider
     */
    public function testInvalidPermissionConfig(array $input): void
    {
        $this->expectException(ConfigurationException::class);

        $this->resolver->resolvePermissionConfig($input);
    }

    /**
     * @param array $input
     * @param array $output
     *
     * @dataProvider validNamespaceConfigProvider
     */
    public function testValidNamespaceConfig(array $input, array $output): void
    {
        $result = $this->resolver->resolveNamespaceConfig($input);

        self::assertEquals($output, $result);
    }

    /**
     * @param array $input
     *
     * @dataProvider invalidNamespaceConfigProvider
     */
    public function testInvalidNamespaceConfig(array $input): void
    {
        $this->expectException(ConfigurationException::class);

        $this->resolver->resolveNamespaceConfig($input);
    }

    /**
     * @param array $input
     * @param array $output
     *
     * @dataProvider validActionConfigProvider
     */
    public function testValidActionConfig(array $input, array $output): void
    {
        $result = $this->resolver->resolveActionConfig($input, ['valid_permission' => []]);

        self::assertEquals($output, $result);
    }

    /**
     * @param array $input
     *
     * @dataProvider invalidActionConfigProvider
     */
    public function testInvalidActionConfig(array $input): void
    {
        $this->expectException(ConfigurationException::class);

        $this->resolver->resolveActionConfig($input, ['valid_permission' => []]);
    }

    /**
     * @param array $input
     * @param array $output
     *
     * @dataProvider validBehaviorConfigProvider
     */
    public function testValidBehaviorConfig(array $input, array $output): void
    {
        $result = $this->resolver->resolveBehaviorConfig($input);

        self::assertEquals($output, $result);
    }

    /**
     * @param array $input
     *
     * @dataProvider invalidBehaviorConfigProvider
     */
    public function testInvalidBehaviorConfig(array $input): void
    {
        $this->expectException(ConfigurationException::class);

        $this->resolver->resolveBehaviorConfig($input);
    }

    /**
     * @param array $input
     * @param array $output
     *
     * @dataProvider validResourceConfigProvider
     */
    public function testValidResourceConfig(array $input, array $output): void
    {
        $result = $this->resolver->resolveResourceConfig($input, [], ['acme' => []]);

        self::assertEquals($output, $result);
    }

    /**
     * @param array $input
     *
     * @dataProvider invalidResourceConfigProvider
     */
    public function testInvalidResourceConfig(array $input): void
    {
        $this->expectException(ConfigurationException::class);

        $this->resolver->resolveResourceConfig($input, [], ['acme' => []]);
    }

    public function invalidPermissionConfigProvider(): array
    {
        return [
            [
                [
                    'name'  => '0_0', // Invalid name (must start and end with letters)
                    'label' => 'foo',
                ],
            ],
            [
                [
                    'name'  => 'foo_bar',
                    'label' => null, // Invalid label (must be set)
                ],
            ],
            [
                [
                    'name'  => 'foo_bar',
                    'label' => false, // Invalid label (must be string)
                ],
            ],
            [
                [
                    'name'         => 'foo_bar',
                    'label'        => 'foo.bar',
                    'trans_domain' => false, // Invalid (must be null or string)
                ],
            ],
        ];
    }

    public function validPermissionConfigProvider(): array
    {
        return [
            [
                [
                    'name'  => 'read',
                    'label' => 'acme.read',
                ],
                array_replace(self::PERMISSION_DEFAULTS, [
                    'name'  => 'read',
                    'label' => 'acme.read',
                ]),
            ],
            [
                [
                    'name'         => 'acme_update',
                    'label'        => 'acme.update',
                    'trans_domain' => 'Acme',
                ],
                array_replace(self::PERMISSION_DEFAULTS, [
                    'name'         => 'acme_update',
                    'label'        => 'acme.update',
                    'trans_domain' => 'Acme',
                ]),
            ],
        ];
    }

    public function invalidNamespaceConfigProvider(): array
    {
        return [
            [
                [
                    'name'   => '0_0', // Invalid name (regex)
                    'prefix' => '/acme',
                ],
            ],
            [
                [
                    'name'   => 'acme',
                    'prefix' => 'acme', // Invalid prefix (must start with '/')
                ],
            ],
        ];
    }

    public function validNamespaceConfigProvider(): array
    {
        return [
            [
                [
                    'name'   => 'acme',
                    'prefix' => '/acme',
                ],
                array_replace(self::NAMESPACE_DEFAULTS, [
                    'name'   => 'acme',
                    'prefix' => '/acme',
                ]),
            ],
            [
                [
                    'name'         => 'acme',
                    'prefix'       => '/acme',
                    'label'        => 'acme.label',
                    'trans_domain' => 'Acme',
                ],
                array_replace(self::NAMESPACE_DEFAULTS, [
                    'name'         => 'acme',
                    'prefix'       => '/acme',
                    'label'        => 'acme.label',
                    'trans_domain' => 'Acme',
                ]),
            ],
        ];
    }

    public function invalidActionConfigProvider(): array
    {
        return [
            [
                [
                    'name'        => '0_0', // Invalid name (regex)
                    'class'       => FooAction::class,
                    'permissions' => 'valid_permission',
                ],
            ],
            [
                [
                    'name'        => 'foo',
                    'class'       => 'UnknownClass', // Invalid class (does not exist)
                    'permissions' => 'valid_permission',
                ],
            ],
            [
                [
                    'name'        => 'foo',
                    'class'       => Entity\Foo::class, // Invalid class (does not implements ActionInterface)
                    'permissions' => 'valid_permission',
                ],
            ],
            [
                [
                    'name'        => 'foo',
                    'class'       => FooAction::class,
                    'permissions' => 'invalid_permission', // Invalid permission (unknown)
                ],
            ],
            [
                [
                    'name'        => 'foo',
                    'class'       => FooAction::class,
                    'permissions' => 'valid_permission',
                    'button'      => ['label' => null], // Invalid button (undefined label)
                ],
            ],
        ];
    }

    public function validActionConfigProvider(): array
    {
        return [
            [
                [
                    'name'        => 'foo',
                    'class'       => FooAction::class,
                    'permissions' => 'valid_permission',
                ],
                array_replace(self::ACTION_DEFAULTS, [
                    'name'        => 'foo',
                    'class'       => FooAction::class,
                    'permissions' => 'valid_permission',
                ]),
            ],
            [
                [
                    'name'        => 'foo',
                    'class'       => FooAction::class,
                    'permissions' => 'valid_permission',
                ],
                array_replace(self::ACTION_DEFAULTS, [
                    'name'        => 'foo',
                    'class'       => FooAction::class,
                    'permissions' => 'valid_permission',
                ]),
            ],
        ];
    }

    public function invalidBehaviorConfigProvider(): array
    {
        return [
            [
                [
                    'name'       => '0_0', // Invalid name (regex)
                    'class'      => FooBehavior::class,
                    'interface'  => FooInterface::class,
                    'operations' => [Behaviors::INSERT],
                ],
            ],
            [
                [
                    'name'       => 'foo',
                    'class'      => 'UnknownClass', // Invalid class (does not exists)
                    'interface'  => FooInterface::class,
                    'operations' => [Behaviors::INSERT],
                ],
            ],
            [
                [
                    'name'       => 'foo',
                    'class'      => Entity\Foo::class, // Invalid class (does not implements BehaviorInterface)
                    'interface'  => FooInterface::class,
                    'operations' => [Behaviors::INSERT],
                ],
            ],
            [
                [
                    'name'       => 'foo',
                    'class'      => FooBehavior::class,
                    'interface'  => 'UnknownInterface', // Invalid interface (does not exists)
                    'operations' => [Behaviors::INSERT],
                ],
            ],
            [
                [
                    'name'       => 'foo',
                    'class'      => FooBehavior::class,
                    'interface'  => FooInterface::class,
                    'operations' => [], // Invalid operations (empty)
                ],
            ],
            [
                [
                    'name'       => 'foo',
                    'class'      => FooBehavior::class,
                    'interface'  => FooInterface::class,
                    'operations' => ['unknown'], // Invalid operations (unknown)
                ],
            ],
        ];
    }

    public function validBehaviorConfigProvider(): array
    {
        return [
            [
                [
                    'name'       => 'foo',
                    'class'      => FooBehavior::class,
                    'interface'  => FooInterface::class,
                    'operations' => [Behaviors::INSERT],
                ],
                array_replace(self::BEHAVIOR_DEFAULTS, [
                    'name'       => 'foo',
                    'class'      => FooBehavior::class,
                    'interface'  => FooInterface::class,
                    'operations' => [Behaviors::INSERT],
                ]),
            ],
            [
                [
                    'name'       => 'bar',
                    'class'      => FooBehavior::class,
                    'interface'  => FooInterface::class,
                    'operations' => [Behaviors::INSERT],
                    'options'    => ['foo' => 'bar'],
                ],
                array_replace(self::BEHAVIOR_DEFAULTS, [
                    'name'       => 'bar',
                    'class'      => FooBehavior::class,
                    'interface'  => FooInterface::class,
                    'operations' => [Behaviors::INSERT],
                    'options'    => ['foo' => 'bar'],
                ]),
            ],
        ];
    }

    public function invalidResourceConfigProvider(): array
    {
        return [
            [
                [
                    // ERROR: Missing driver
                    'namespace' => 'foo',
                    'name'      => 'post',
                    'entity'    => [
                        'class' => Entity\Post::class,
                    ],
                ],
            ],
            [
                [
                    'driver'    => 'doctrine/orm',
                    // ERROR: Unknown namespace
                    'namespace' => 'foo',
                    'name'      => 'post',
                    'entity'    => [
                        'class' => Entity\Post::class,
                    ],
                ],
            ],
            [
                [
                    'driver'      => 'doctrine/orm',
                    'namespace'   => 'acme',
                    'name'        => 'post',
                    'entity'      => [
                        // ERROR: Post does not implements CommentInterface
                        'interface' => Entity\CommentInterface::class,
                        'class'     => Entity\Post::class,
                    ],
                    'repository'  => [
                        'interface' => Repository\PostRepositoryInterface::class,
                        'class'     => Repository\PostRepository::class,
                    ],
                    'translation' => [
                        'class'  => Entity\PostTranslation::class,
                        'fields' => ['content'],
                    ],
                ],
            ],
            [
                [
                    'driver'      => 'doctrine/orm',
                    'namespace'   => 'acme',
                    'name'        => 'post',
                    'entity'      => [
                        'interface' => Entity\PostInterface::class,
                        'class'     => Entity\Post::class,
                    ],
                    'repository'  => [
                        'interface' => Repository\PostRepositoryInterface::class,
                        // ERROR: Missing class
                    ],
                    'translation' => [
                        'class'  => Entity\PostTranslation::class,
                        'fields' => ['content'],
                    ],
                ],
            ],
            [
                [
                    'driver'      => 'doctrine/orm',
                    'namespace'   => 'acme',
                    'name'        => 'post',
                    'entity'      => [
                        'interface' => Entity\PostInterface::class,
                        'class'     => Entity\Post::class,
                    ],
                    'repository'  => [
                        'interface' => Repository\PostRepositoryInterface::class,
                        'class'     => Repository\PostRepository::class,
                    ],
                    'translation' => [
                        'class' => Entity\PostTranslation::class,
                        // ERROR: Missing translation fields
                    ],
                ],
            ],
            [
                [
                    'driver'    => 'doctrine/orm',
                    'namespace' => 'acme',
                    'name'      => 'bar',
                    'entity'    => [
                        'class' => Entity\Bar::class,
                    ],
                    // ERROR: Missing translation config (Bar implements TranslatableInterface)
                ],
            ],
            [
                [
                    'driver'    => 'doctrine/orm',
                    'namespace' => 'acme',
                    'name'      => 'foo',
                    'entity'    => [
                        // ERROR: Foo does not implements ResourceInterface
                        'class' => Entity\Foo::class,
                    ],
                ],
            ],
        ];
    }

    public function validResourceConfigProvider(): array
    {
        return [
            [
                [
                    'driver'    => 'doctrine/orm',
                    'namespace' => 'acme',
                    'name'      => 'category',
                    'entity'    => [
                        'class' => Entity\Category::class,
                    ],
                ],
                array_replace_recursive(self::RESOURCE_DEFAULTS, [
                    'namespace' => 'acme',
                    'name'      => 'category',
                    'entity'    => [
                        'class'     => Entity\Category::class,
                        'interface' => null,
                    ],
                ]),
            ],
            [
                [
                    'driver'      => 'doctrine/orm',
                    'namespace'   => 'acme',
                    'name'        => 'post',
                    'entity'      => [
                        'interface' => Entity\PostInterface::class,
                        'class'     => Entity\Post::class,
                    ],
                    'repository'  => [
                        'interface' => Repository\PostRepositoryInterface::class,
                        'class'     => Repository\PostRepository::class,
                    ],
                    'translation' => [
                        'class'  => Entity\PostTranslation::class,
                        'fields' => ['content'],
                    ],
                ],
                array_replace_recursive(self::RESOURCE_DEFAULTS, [
                    'namespace'   => 'acme',
                    'name'        => 'post',
                    'entity'      => [
                        'interface' => Entity\PostInterface::class,
                        'class'     => Entity\Post::class,
                    ],
                    'repository'  => [
                        'interface' => Repository\PostRepositoryInterface::class,
                        'class'     => Repository\PostRepository::class,
                    ],
                    'translation' => [
                        'class'  => Entity\PostTranslation::class,
                        'fields' => ['content'],
                    ],
                ]),
            ],
            [
                [
                    'driver'     => 'doctrine/orm',
                    'namespace'  => 'acme',
                    'name'       => 'comment',
                    'entity'     => [
                        'interface' => Entity\CommentInterface::class,
                        'class'     => Entity\Comment::class,
                    ],
                    'repository' => [
                        'class' => Repository\CommentRepository::class,
                    ],
                ],
                array_replace_recursive(self::RESOURCE_DEFAULTS, [
                    'namespace'  => 'acme',
                    'name'       => 'comment',
                    'entity'     => [
                        'interface' => Entity\CommentInterface::class,
                        'class'     => Entity\Comment::class,
                    ],
                    'repository' => [
                        'interface' => null,
                        'class'     => Repository\CommentRepository::class,
                    ],
                ]),
            ],
        ];
    }
}
