<?php

namespace Ekyna\Component\Resource\Configuration;

use Doctrine\Common\Inflector\Inflector;

/**
 * Class Configuration
 * @package Ekyna\Component\Resource\Configuration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->config['id'];
    }

    /**
     * @inheritdoc
     */
    public function getNamespace()
    {
        return $this->config['namespace'];
    }

    /**
     * @inheritdoc
     */
    public function getParentId()
    {
        return $this->config['parent_id'];
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return sprintf('%s_%s', $this->getNamespace(), $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function getResourceId()
    {
        return sprintf('%s.%s', $this->getNamespace(), $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function getParentControllerId()
    {
        if (!empty($this->getParentId())) {
            return sprintf('%s.controller', $this->getParentId());
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getParentConfigurationId()
    {
        if (!empty($this->getParentId())) {
            return sprintf('%s.configuration', $this->getParentId());
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getResourceClass()
    {
        return $this->getClass('entity');
    }

    /**
     * @inheritdoc
     */
    public function getTranslationPrefix()
    {
        return $this->config['trans_prefix'] ?: $this->getResourceId();
    }

    /**
     * @inheritdoc
     */
    public function getTranslationClass()
    {
        return $this->getTranslation('entity'); // TODO rename key to 'class' (and in builder / config tree)
    }

    /**
     * @inheritdoc
     */
    public function getTranslationFields()
    {
        return $this->getTranslation('fields');
    }

    /**
     * @inheritdoc
     */
    public function getEventClass()
    {
        return $this->config['event']['class'];
    }

    /**
     * @inheritdoc
     */
    public function getEventPriority()
    {
        return $this->config['event']['priority'];
    }

    /**
     * @inheritdoc
     */
    public function getResourceName($plural = false)
    {
        return $plural ? Inflector::pluralize($this->config['name']) : $this->config['name'];
    }

    /**
     * @inheritdoc
     */
    public function getResourceLabel($plural = false)
    {
        return sprintf('%s.label.%s', $this->getTranslationPrefix(), $plural ? 'plural' : 'singular');
    }

    /**
     * @inheritdoc
     */
    public function getTemplate($name)
    {
        if (!array_key_exists($name, $this->config['templates'])) {
            throw new \InvalidArgumentException(sprintf('Template "%s.twig" is not registered.', $name));
        }

        return sprintf('%s.twig', $this->config['templates'][$name]);
    }

    /**
     * @inheritdoc
     */
    public function getRoutePrefix()
    {
        return sprintf('%s_%s_admin', $this->getNamespace(), $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function getRoute($action)
    {
        return sprintf('%s_%s', $this->getRoutePrefix(), $action);
    }

    /**
     * @inheritdoc
     */
    public function getEventName($action)
    {
        return sprintf('%s.%s.%s', $this->getNamespace(), $this->getId(), $action);
    }

    /**
     * @inheritdoc
     */
    public function getFormType()
    {
        return $this->getClass('form_type');
    }

    /**
     * @inheritdoc
     */
    public function getTableType()
    {
        return $this->getClass('table_type');
    }

    /**
     * @inheritdoc
     */
    public function getServiceKey($service)
    {
        return sprintf('%s.%s.%s', $this->getNamespace(), $this->getId(), $service);
    }

    /**
     * @inheritdoc
     */
    public function isRelevant($object)
    {
        $class = $this->getResourceClass();

        return $object instanceOf $class;
    }

    /**
     * Returns the class for the given key.
     *
     * @param string $key
     *
     * @return string
     */
    private function getClass($key)
    {
        if (!array_key_exists($key, $this->config['classes'])) {
            throw new \InvalidArgumentException(sprintf('Undefined resource class "%s".', $key));
        }

        return $this->config['classes'][$key];
    }

    /**
     * Returns the translation $key config.
     *
     * @param string $key
     *
     * @return mixed
     */
    private function getTranslation($key)
    {
        if ($this->config['translation']) {
            return $this->config['translation'][$key];
        }

        return null;
    }
}
