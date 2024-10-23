<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Extension;

use Ekyna\Component\Resource\Behavior\BehaviorBuilderInterface;
use Ekyna\Component\Resource\Behavior\BehaviorInterface;
use Ekyna\Component\Resource\Behavior\Behaviors;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\ConfigurationException;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Factory\TranslatableFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_diff;
use function array_map;
use function array_push;
use function array_replace;
use function class_exists;
use function constant;
use function defined;
use function gettype;
use function interface_exists;
use function is_array;
use function is_string;
use function is_subclass_of;
use function preg_match;
use function sprintf;

/**
 * Class CoreExtension
 * @package Ekyna\Component\Resource\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CoreExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function extendPermissionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'trans_domain' => null,
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $defaults->add($options);

        $resolver
            ->setRequired([
                'name',
                'label',
            ])
            ->setDefaults($options)
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('label', 'string')
            ->setAllowedTypes('trans_domain', ['string', 'null'])
            ->setAllowedValues('name', function ($value) {
                return preg_match(self::NAME_REGEX, $value);
            });
    }

    /**
     * @inheritDoc
     */
    public function extendNamespaceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'label'        => null,
            'trans_domain' => null,
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $defaults->add($options);

        $resolver
            ->setRequired([
                'name',
                'prefix',
            ])
            ->setDefaults($options)
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('prefix', 'string')
            ->setAllowedTypes('label', ['string', 'null'])
            ->setAllowedTypes('trans_domain', ['string', 'null'])
            ->setAllowedValues('name', function ($value) {
                return preg_match(self::NAME_REGEX, $value);
            })
            ->setAllowedValues('prefix', function ($value) {
                return preg_match(self::PREFIX_REGEX, $value);
            });
    }

    /**
     * @inheritDoc
     */
    public function extendActionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'permissions' => [],
            'options'     => [
                'expose' => false,
            ],
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $defaults->add($options);

        $resolver
            ->setDefined('name')
            ->setRequired(['class'])
            ->setDefaults($options)
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('options', 'array')
            ->setAllowedValues('name', function ($value) {
                if (!(class_exists($value) || preg_match(self::NAME_REGEX, $value))) {
                    throw new InvalidOptionsException("Invalid action name '$value'.");
                }

                return true;
            });

        $this->addPermissionsOptions($resolver);
    }

    public function extendActionOptions(OptionsResolver $resolver): void
    {
        $this->addPermissionsOptions($resolver);
    }

    /**
     * @inheritDoc
     */
    public function extendBehaviorConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'options' => [],
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $defaults->add($options);

        $resolver
            ->setDefined('name')
            ->setRequired(['interface', 'operations', 'class'])
            ->setDefaults($options)
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('class', 'string')
            ->setAllowedTypes('interface', 'string')
            ->setAllowedTypes('operations', 'array')
            ->setAllowedTypes('options', 'array')
            ->setAllowedValues('name', function ($value) {
                if (!preg_match(self::NAME_REGEX, $value)) {
                    throw new InvalidOptionsException("Invalid behavior name '$value'.");
                }

                return true;
            })
            ->setAllowedValues('class', function ($value) {
                if (!class_exists($value)) {
                    throw new InvalidOptionsException("Class $value does not exist.");
                }

                if (
                    is_subclass_of($value, BehaviorInterface::class)
                    || is_subclass_of($value, BehaviorBuilderInterface::class)
                ) {
                    return true;
                }

                throw new InvalidOptionsException(sprintf(
                    'Class %s must implements %s or %s',
                    $value,
                    BehaviorInterface::class,
                    BehaviorBuilderInterface::class
                ));
            })
            ->setAllowedValues('interface', function ($value) {
                if (!interface_exists($value)) {
                    throw new InvalidOptionsException("Interface $value does not exist.");
                }

                return true;
            })
            ->setAllowedValues('operations', function ($value) {
                if (empty($value)) {
                    throw new InvalidOptionsException("Behavior 'operations' option is empty.");
                }

                foreach ($value as $name) {
                    if (!Behaviors::isValid($name, false)) {
                        throw new ConfigurationException("Invalid behavior operation '$name'.");
                    }
                }

                return true;
            });
    }

    /**
     * @inheritDoc
     */
    public function extendResourceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'factory'      => null,
            'repository'   => null,
            'manager'      => null,
            'search'       => null,
            'translation'  => null,
            'parent'       => null,
            'event'        => null,
            'actions'      => [],
            'behaviors'    => [],
            'permissions'  => [],
            'trans_prefix' => null,
            'trans_domain' => null,
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $defaults->add($options);

        $resolver
            ->setRequired([
                'driver',
                'namespace',
                'name',
                'entity',
            ])
            ->setDefaults($options);

        $resolver
            ->setAllowedTypes('driver', 'string')
            ->setAllowedTypes('namespace', 'string')
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('entity', ['string', 'array'])
            ->setAllowedTypes('factory', ['null', 'string', 'array'])
            ->setAllowedTypes('repository', ['null', 'string', 'array'])
            ->setAllowedTypes('manager', ['null', 'string', 'array'])
            ->setAllowedTypes('translation', ['null', 'array'])
            ->setAllowedTypes('parent', ['null', 'string'])
            ->setAllowedTypes('event', ['null', 'string', 'array'])
            ->setAllowedTypes('actions', 'array')
            ->setAllowedTypes('behaviors', 'array')
            ->setAllowedTypes('permissions', 'array')
            ->setAllowedTypes('trans_prefix', ['null', 'string'])
            ->setAllowedTypes('trans_domain', ['null', 'string'])
            ->setAllowedValues('namespace', function ($value) {
                if (!preg_match(self::NAME_REGEX, $value)) {
                    throw new InvalidOptionsException("Invalid resource namespace '$value'.");
                }

                return true;
            })
            ->setAllowedValues('name', function ($value) {
                if (!preg_match(self::NAME_REGEX, $value)) {
                    throw new InvalidOptionsException("Invalid resource name '$value'.");
                }

                return true;
            })
            ->setAllowedValues('entity', function ($value) {
                return $this->validateClassInterface($value, ResourceInterface::class);
            })
            ->setAllowedValues('factory', function ($value) {
                return $this->validateClassInterface($value, ResourceFactoryInterface::class, false);
            })
            ->setAllowedValues('repository', function ($value) {
                return $this->validateClassInterface($value, ResourceRepositoryInterface::class, false);
            })
            ->setAllowedValues('manager', function ($value) {
                return $this->validateClassInterface($value, ResourceManagerInterface::class, false);
            })
            ->setAllowedValues('translation', function ($value) {
                if (empty($value)) {
                    return true;
                }

                if (!isset($value['class'])) {
                    throw new InvalidOptionsException('Resource translation class must be configured.');
                }
                if (!class_exists($value['class'])) {
                    throw new InvalidOptionsException(sprintf(
                        'Class %s does not exists',
                        $value['class']
                    ));
                }

                if (empty($value['fields'])) {
                    throw new InvalidOptionsException('Resource translation fields must be configured.');
                }

                foreach ($value['fields'] as $field) {
                    if (empty($field) || !is_string($field)) {
                        throw new InvalidOptionsException('Resource translation fields must be configured.');
                    }
                }

                return true;
            })
            ->setAllowedValues('event', function ($value) {
                if (empty($value)) {
                    return true;
                }

                if (is_string($value)) {
                    $value = ['class' => $value];
                }

                if (!isset($value['class'])) {
                    return true;
                }

                return $this->validateClass($value['class'], ResourceEventInterface::class, false);
            })
            // TODO validate actions
            // TODO validate behaviors
            ->setAllowedValues('permissions', function ($value) {
                foreach ($value as $name) {
                    if (!is_string($name)) {
                        throw new InvalidOptionsException(sprintf(
                            "Resource's permissions must be an array of string, '%s' given.",
                            gettype($name)
                        ));
                    }
                }

                return true;
            });

        $resolver
            ->setNormalizer(
                'entity',
                function (Options $options, $value) {
                    return $this->normalizeClassInterface($value, ResourceInterface::class);
                }
            )
            ->setNormalizer('translation', function (Options $options, $value) {
                // Warning: Other extension may add keys to the translation array.

                $entity = is_array($options['entity']) ? $options['entity']['class'] : $options['entity'];

                if (is_subclass_of($entity, TranslatableInterface::class)) {
                    if (empty($value)) {
                        throw new InvalidOptionsException(sprintf(
                            'Resource translation must be configured for entity %s.',
                            $entity
                        ));
                    }
                    if (!isset($value['class'])) {
                        throw new InvalidOptionsException(sprintf(
                            'Resource translation class must be configured for entity %s.',
                            $entity
                        ));
                    }
                    if (!isset($value['fields'])) {
                        throw new InvalidOptionsException(sprintf(
                            'Resource translation fields must be configured for entity %s.',
                            $entity
                        ));
                    }

                    return array_replace([
                        'class'  => null,
                        'fields' => null,
                    ], $value);
                }

                if (!empty($value)) {
                    throw new InvalidOptionsException(sprintf(
                        'Translation configuration for entity %s should be null as this entity does not implements %s.',
                        $entity,
                        TranslatableInterface::class
                    ));
                }

                return null;
            })
            ->setNormalizer('factory', function (Options $options, $value) {
                $entity = is_array($options['entity']) ? $options['entity']['class'] : $options['entity'];

                if (is_subclass_of($entity, TranslatableInterface::class)) {
                    $interface = TranslatableFactoryInterface::class;
                } else {
                    $interface = ResourceFactoryInterface::class;
                }

                return $this->normalizeInterface($value, $interface);
            })
            ->setNormalizer('repository', function (Options $options, $value) {
                $entity = is_array($options['entity']) ? $options['entity']['class'] : $options['entity'];

                if (is_subclass_of($entity, TranslatableInterface::class)) {
                    $interface = TranslatableRepositoryInterface::class;
                } else {
                    $interface = ResourceRepositoryInterface::class;
                }

                return $this->normalizeInterface($value, $interface);
            })
            ->setNormalizer('manager', function (Options $options, $value) {
                return $this->normalizeInterface($value, ResourceManagerInterface::class);
            })
            ->setNormalizer('event', function (Options $options, $value) {
                if (empty($value)) {
                    $value = [];
                } elseif (is_string($value)) {
                    $value = ['class' => $value,];
                }

                $value = array_replace([
                    'class'    => ResourceEvent::class,
                    'priority' => 0,
                ], $value);

                if (
                    $value['class'] !== ResourceEvent::class
                    && !is_subclass_of($value['class'], ResourceEventInterface::class)
                ) {
                    throw new InvalidOptionsException(sprintf(
                        'Class %s must implements %s.',
                        $value['class'],
                        ResourceEventInterface::class
                    ));
                }

                return $value;
            })
            ->setNormalizer('permissions', function (Options $options, $value) {
                if (is_string($value)) {
                    $value = [$value];
                }

                return array_map(
                    fn(string $val): string => defined($val) ? constant($val) : $val,
                    $value
                );
            });
    }

    private function addPermissionsOptions(OptionsResolver $resolver): void
    {
        $normalizer = function (Options $options, $value) {
            if (is_string($value)) {
                $value = [$value];
            }

            return array_map(
                fn(string $val): string => defined($val) ? constant($val) : $val,
                $value
            );
        };

        $resolver
            ->setDefined(['permission', 'permissions'])
            ->setAllowedTypes('permission', ['array', 'string'])
            ->setAllowedTypes('permissions', ['array', 'string'])
            ->setNormalizer('permission', $normalizer)
            ->setNormalizer('permissions', $normalizer)
            ->setDeprecated('permission', 'ekyna/resource', '0.9')
            ->addNormalizer('permissions', function (Options $options, $value) {
                if (!isset($options['permission'])) {
                    return $value;
                }

                if (!empty($diff = array_diff($options['permission'], $value))) {
                    array_push($value, ...$diff);
                }

                return $value;
            });
    }
}
