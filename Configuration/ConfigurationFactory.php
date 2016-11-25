<?php

namespace Ekyna\Component\Resource\Configuration;

use Doctrine\Common\Inflector\Inflector;
use Ekyna\Component\Resource\Event\ResourceEvent;
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
        // TODO use a common option resolver with ConfigurationBuilder
        if (!$this->optionsResolver) {
            $resolver = new OptionsResolver();

            $resolver->setRequired(['namespace', 'id', 'classes']);

            $resolver->setDefault('name', function (Options $options) {
                return Inflector::camelize($options['id']);
            });
            $resolver->setDefault('parent_id', null);
            $resolver->setDefault('templates', null);
            $resolver->setDefault('translation', null);

            $resolver->setAllowedTypes('namespace', 'string');
            $resolver->setAllowedTypes('id', 'string');
            $resolver->setAllowedTypes('name', 'string');
            $resolver->setAllowedTypes('parent_id', ['null', 'string']);
            $resolver->setAllowedTypes('classes', 'array');
            $resolver->setAllowedTypes('templates', ['null', 'string', 'array']);
            $resolver->setAllowedTypes('translation', ['null', 'array']);

            $resolver->setAllowedValues('classes', function ($value) {
                if (!array_key_exists('resource', $value)) {
                    throw new InvalidOptionsException("Key 'resource' is missing in resource configuration classes.");
                }
                /*if (!empty(array_diff(array_keys($value), ['resource', 'form_type', 'event']))) {
                    return false;
                }*/
                foreach ($value as $class) {
                    if ($class && !class_exists($class)) {
                        throw new InvalidOptionsException(sprintf("Class '%s' does not exists.", $class));
                    }
                }

                return true;
            });

            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('templates', function (Options $options, $value) {
                return $this->buildTemplateList($value);
            });

            $classesResolver = new OptionsResolver();

            $classesResolver->setRequired(['resource']);

            $classesResolver->setDefault('form_type', null); // @TODO/WARNING no longer required, prior to resource behavior refactoring
            $classesResolver->setDefault('event', null);

            $classesResolver->setAllowedTypes('resource', 'string');
            $classesResolver->setAllowedTypes('form_type', ['null', 'string']); // @TODO/WARNING no longer required, prior to resource behavior refactoring
            $classesResolver->setAllowedTypes('event', ['null', 'string']);

            /** @noinspection PhpUnusedParameterInspection */
            $classesResolver->setNormalizer('event', function (Options $options, $value) {
                if (null === $value) {
                    return $this->defaultEventClass;
                }

                return $value;
            });

            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('classes', function (Options $options, $value) use ($classesResolver) {
                return $classesResolver->resolve($value);
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
