<?php

namespace Ekyna\Component\Resource\Serializer;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Model;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Class AbstractResourceNormalizer
 * @package Ekyna\Component\Resource\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractResourceNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * @var ConfigurationRegistry
     */
    protected $configurationRegistry;

    /**
     * @var NameConverterInterface
     */
    protected $nameConverter;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;


    /**
     * Sets the configuration registry.
     *
     * @param ConfigurationRegistry $configurationRegistry
     */
    public function setConfigurationRegistry(ConfigurationRegistry $configurationRegistry)
    {
        $this->configurationRegistry = $configurationRegistry;
    }

    /**
     * Sets the name converter.
     *
     * @param NameConverterInterface $nameConverter
     */
    public function setNameConverter(NameConverterInterface $nameConverter)
    {
        $this->nameConverter = $nameConverter;
    }

    /**
     * Sets the property accessor.
     *
     * @param PropertyAccessor $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @inheritdoc
     */
    public function normalize($resource, $format = null, array $context = [])
    {
        /** @var Model\ResourceInterface $resource */
        $data = [
            'id'           => $resource->getId(),
            'choice_label' => (string)$resource,
        ];

        return $data;
    }

    /**
     * Normalizes the object.
     *
     * @param object $object
     * @param string $format
     * @param array  $context
     *
     * @return array|string
     */
    protected function normalizeObject($object, $format, array $context)
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException(
                'Cannot normalize object because the injected serializer is not a normalizer'
            );
        }

        return $this->serializer->normalize($object, $format, $context);
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Denormalizes the object.
     *
     * @param array  $data
     * @param string $class
     * @param string $format
     * @param array  $context
     *
     * @return object|void
     */
    protected function denormalizeObject($data, $class, $format, array $context)
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException(
                'Cannot denormalize object because the injected serializer is not a denormalizer'
            );
        }

        return $this->serializer->denormalize($data, $class, $format, $context);
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        if ($data instanceof Model\ResourceInterface) {
            return null !== $this->configurationRegistry->findConfiguration($data);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if (class_exists($type) && is_subclass_of($type, Model\ResourceInterface::class)) {
            return null !== $this->configurationRegistry->findConfiguration($type);
        }

        return false;
    }
}
