<?php

namespace Ekyna\Component\Resource\Configuration;

/**
 * Interface ConfigurationInterface
 * @package Ekyna\Bundle\AdminBundle\Pool
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ConfigurationInterface
{
    /**
     * Returns the resource identifier (without namespace).
     */
    public function getId();

    /**
     * Returns the namespace.
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Returns the parent resource identifier.
     *
     * @return string
     */
    public function getParentId();

    /**
     * Returns the resource alias (<namespace>_<id>).
     *
     * @return string
     */
    public function getAlias();

    /**
     * Returns the resource identifier (<namespace>.<id>).
     *
     * @return string
     */
    public function getResourceId();

    /**
     * Returns the parent controller identifier.
     *
     * @return string
     */
    public function getParentControllerId();

    /**
     * Returns the parent configuration identifier.
     *
     * @return string
     */
    public function getParentConfigurationId();

    /**
     * Returns the resource FQCN.
     *
     * @return string
     */
    public function getResourceClass();

    /**
     * Returns the translation class.
     *
     * @return string|null
     */
    public function getTranslationClass();

    /**
     * Returns the translation fields.
     *
     * @return array|null
     */
    public function getTranslationFields();

    /**
     * Returns the eventClass.
     *
     * @return string
     */
    public function getEventClass();

    /**
     * Returns the resource name.
     *
     * @param boolean $plural
     *
     * @return string
     */
    public function getResourceName($plural = false);

    /**
     * Returns the resource label.
     *
     * @param boolean $plural
     *
     * @return string
     */
    public function getResourceLabel($plural = false);

    /**
     * Returns a full qualified template name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getTemplate($name);

    /**
     * Returns the route prefix.
     *
     * @return string
     */
    public function getRoutePrefix();

    /**
     * Returns a full qualified route name for the given action.
     *
     * @param string $action
     *
     * @return string
     */
    public function getRoute($action);

    /**
     * Returns the resource event name for the given action.
     *
     * @param $action
     *
     * @return string
     */
    public function getEventName($action);

    /**
     * Returns the form type service identifier.
     *
     * @return string
     */
    public function getFormType();

    /**
     * Returns the table type service identifier.
     *
     * @return string
     */
    public function getTableType();

    /**
     * Returns a service identifier.
     *
     * @param string $service
     *
     * @return string
     */
    public function getServiceKey($service);

    /**
     * Returns the object (resource) identify.
     *
     * @return \Symfony\Component\Security\Acl\Domain\ObjectIdentity
     */
    public function getObjectIdentity();

    /**
     * Returns whether this configuration is relevant for the given object.
     *
     * @param object $object
     *
     * @return boolean
     */
    public function isRelevant($object);
}
