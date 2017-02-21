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
        // TODO configurable class '%ekyna_resource.configuration.class%'
        return new Configuration($this->getOptionsResolver()->resolve($config));
    }

    /**
     * Returns the config options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        // TODO use the ConfigurationBuilder option resolver
        if (!$this->optionsResolver) {
            $resolver = new OptionsResolver();

            $resolver
                ->setRequired(['namespace', 'id', 'classes'])
                ->setDefaults([
                    'name' => function (Options $options) {
                        return Inflector::camelize($options['id']);
                    },
                    'parent_id'   => null,
                    'templates'   => null,
                    'translation' => null,
                ])
                ->setAllowedTypes('namespace', 'string')
                ->setAllowedTypes('id', 'string')
                ->setAllowedTypes('name', 'string')
                ->setAllowedTypes('parent_id', ['null', 'string'])
                ->setAllowedTypes('classes', 'array')
                ->setAllowedTypes('templates', ['null', 'string', 'array'])
                ->setAllowedTypes('translation', ['null', 'array']);


            // Classes option
            $classesResolver = new OptionsResolver();
            $classesResolver
                ->setRequired(['entity'])
                ->setDefaults([
                    'form_type'  => null, // @TODO/WARNING no longer required, prior to resource behavior refactoring
                    'repository' => null,
                    'event'      => null,
                ])
                ->setAllowedTypes('entity', 'string')
                ->setAllowedTypes('repository', ['null', 'string'])
                ->setAllowedTypes('event', ['null', 'string'])
                ->setAllowedTypes('form_type', ['null', 'string']); // @TODO/WARNING no longer required, prior to resource behavior refactoring;
                /*->setNormalizer('event', function (Options $options, $value) {
                    if (null === $value) {
                        return $this->defaultEventClass;
                    }

                    return $value;
                })*/
                /*->setAllowedValues('classes', function ($value) {
                    foreach ($value as $class) {
                        if ($class && !class_exists($class)) {
                            throw new InvalidOptionsException(sprintf("Class '%s' does not exists.", $class));
                        }
                    }

                    return true;
                })*/

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

                    $event = array_key_exists('event', $value) ? $value['event'] : null;
                    if (null === $event) {
                        $value['event'] = $this->defaultEventClass;
                    } elseif (!is_subclass_of($event, ResourceEventInterface::class)) {
                        throw new InvalidOptionsException(sprintf(
                            "Class '%s' must implements '%s'.",
                            $event,
                            ResourceEventInterface::class
                        ));
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
        // TODO add resources controller traits templates ? (like new_child.html)
        if (is_array($templatesConfig)) {
            $templatesList = array_merge($templatesList, $templatesConfig);
        }

        return $templatesList;
    }
}
