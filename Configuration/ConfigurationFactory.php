<?php

namespace Ekyna\Component\Resource\Configuration;

use Doctrine\Common\Inflector\Inflector;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigurationFactory
 * @package Ekyna\Component\Resource\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationFactory
{
    /**
     * @var string
     */
    private $defaultEventClass;

    /**
     * @var string
     */
    private $defaultTemplates;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * The required templates (name => extensions[])[].
     * @var array
     */
    static private $templates = [
        '_form'  => ['html'],
        'list'   => ['html', 'xml'],
        'new'    => ['html', 'xml'],
        'show'   => ['html'],
        'edit'   => ['html'],
        'remove' => ['html'],
    ];


    /**
     * Constructor.
     *
     * @param string $defaultEventClass
     * @param string $defaultTemplates
     */
    public function __construct(
        $defaultEventClass = ResourceEvent::class,
        $defaultTemplates = 'EkynaAdminBundle:Entity/Default'
    ) {
        $this->defaultEventClass = $defaultEventClass;
        $this->defaultTemplates = $defaultTemplates;
    }

    /**
     * Creates and register a configuration
     *
     * @param array $config
     *
     * @return ConfigurationInterface
     */
    public function createConfiguration(array $config)
    {
        return new Configuration($this->getOptionsResolver()->resolve($config));
    }

    /**
     * Returns the config options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        // TODO Use/Merge ConfigurationBuilder options resolver.
        if (!$this->optionsResolver) {
            $resolver = new OptionsResolver();

            $resolver
                ->setRequired(['namespace', 'id', 'classes'])
                ->setDefaults([
                    'name'        => function (Options $options) {
                        return Inflector::camelize($options['id']);
                    },
                    'parent_id'   => null,
                    'event'       => null,
                    'templates'   => null,
                    'translation' => null,
                    'trans_prefix' => null,
                ])
                ->setAllowedTypes('namespace', 'string')
                ->setAllowedTypes('id', 'string')
                ->setAllowedTypes('name', 'string')
                ->setAllowedTypes('parent_id', ['null', 'string'])
                ->setAllowedTypes('classes', 'array')
                ->setAllowedTypes('event', ['null', 'string', 'array'])
                ->setAllowedTypes('templates', ['null', 'string', 'array'])
                ->setAllowedTypes('translation', ['null', 'array'])
                ->setAllowedTypes('trans_prefix', ['null', 'string']);


            // Classes option
            $classesResolver = new OptionsResolver();
            $classesResolver
                ->setRequired(['entity'])
                ->setDefaults([
                    'table_type'  => null, // @TODO/WARNING no longer required (behavior refactoring)
                    'form_type'  => null, // @TODO/WARNING no longer required (behavior refactoring)
                    'repository' => null,
                ])
                ->setAllowedTypes('entity', 'string')
                ->setAllowedTypes('repository', ['null', 'string'])
                ->setAllowedTypes('form_type', ['null', 'string'])
                ->setAllowedTypes('table_type', ['null', 'string']); // @TODO/WARNING no longer required (behavior refactoring)

            $resolver
                ->setNormalizer('classes', function (Options $options, $value) use ($classesResolver) {
                    foreach ($value as $class) {
                        if ($class && !class_exists($class)) {
                            throw new InvalidOptionsException(sprintf("Class '%s' does not exists.", $class));
                        }
                    }

                    $entity = $value['entity'];
                    if (null !== $options['translation'] && !is_subclass_of($entity, TranslatableInterface::class)) {
                        throw new InvalidOptionsException(sprintf(
                            "Class '%s' must implements '%s'.",
                            $entity,
                            TranslatableInterface::class));
                    }

                    $repository = array_key_exists('repository', $value) ? $value['repository'] : null;
                    if (null === $repository) {
                        $value['repository'] = null !== $options['translation']
                            ? TranslatableResourceRepository::class
                            : ResourceRepository::class;
                    } elseif (null !== $options['translation']) {
                        if (!is_subclass_of($repository, TranslatableResourceRepositoryInterface::class)) {
                            throw new InvalidOptionsException(sprintf(
                                "Class '%s' must implements '%s'.",
                                $repository,
                                TranslatableResourceRepositoryInterface::class
                            ));
                        }
                    } else {
                        if (!is_subclass_of($repository, ResourceRepositoryInterface::class)) {
                            throw new InvalidOptionsException(sprintf(
                                "Class '%s' must implements '%s'.",
                                $repository,
                                ResourceRepositoryInterface::class
                            ));
                        }
                    }

                    return $classesResolver->resolve($value);
                });


            // Translation option
            $translationResolver = new OptionsResolver();
            $translationResolver
                ->setRequired(['entity', 'fields'])
                ->setDefault('repository', null)
                ->setAllowedTypes('entity', 'string')
                ->setAllowedTypes('fields', 'array')
                ->setAllowedTypes('repository', ['null', 'string'])
                ->setAllowedValues('entity', function ($class) {
                    if (!class_exists($class)) {
                        throw new InvalidOptionsException(sprintf("Class '%s' does not exists.", $class));
                    }
                    if (!is_subclass_of($class, TranslationInterface::class)) {
                        throw new InvalidOptionsException(sprintf("Class '%s' must implements '%s'.", $class, TranslationInterface::class));
                    }

                    return true;
                })
                ->setAllowedValues('fields', function ($fields) {
                    if (empty($fields)) {
                        throw new InvalidOptionsException("Translatable fields cannot be empty.");
                    }

                    return true;
                })
                ->setAllowedValues('repository', function ($class) {
                    if (null !== $class && !class_exists($class)) {
                        throw new InvalidOptionsException(sprintf("Class '%s' does not exists.", $class));
                    }

                    return true;
                });

            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('translation', function (Options $options, $value) use ($translationResolver) {
                if (is_array($value)) {
                    return $translationResolver->resolve($value);
                }

                return $value;
            });

            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('trans_prefix', function (Options $options, $value) use ($translationResolver) {
                if (empty($value)) {
                    return sprintf('%s.%s', $options['namespace'], $options['id']);
                }

                return $value;
            });

            // Event option
            $eventResolver = new OptionsResolver();
            $eventResolver
                ->setRequired(['class'])
                ->setDefaults([
                    'class'    => $this->defaultEventClass,
                    'priority' => 0,
                ])
                ->setAllowedTypes('class', 'string')
                ->setAllowedTypes('priority', 'int');

            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('event', function (Options $options, $value) use ($eventResolver) {
                if (is_string($value)) {
                    $value = ['class' => $value];
                }

                $value = $eventResolver->resolve((array)$value);

                if (!is_subclass_of($value['class'], ResourceEventInterface::class)) {
                    throw new InvalidOptionsException(sprintf(
                        "Class '%s' must implements '%s'.",
                        $value['class'],
                        ResourceEventInterface::class
                    ));
                }

                return $value;
            });


            // Templates option
            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('templates', function (Options $options, $value) {
                return $this->buildTemplateList($value);
            });


            $this->optionsResolver = $resolver;
        }

        return $this->optionsResolver;
    }

    /**
     * Builds the templates list.
     *
     * @param mixed $templatesConfig
     *
     * @return array
     */
    private function buildTemplateList($templatesConfig)
    {
        $templateNamespace = $this->defaultTemplates;
        if (is_string($templatesConfig)) {
            $templateNamespace = $templatesConfig;
        }
        $templatesList = [];
        foreach (self::$templates as $name => $extensions) {
            foreach ($extensions as $extension) {
                $file = $name . '.' . $extension;
                $templatesList[$file] = $templateNamespace . ':' . $file;
            }
        }
        // TODO add resource actions templates ? (behavior refactoring)
        if (is_array($templatesConfig)) {
            $templatesList = array_merge($templatesList, $templatesConfig);
        }

        return $templatesList;
    }
}
