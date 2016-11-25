<?php

namespace Ekyna\Component\Resource\Serializer;

use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Model;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Class TranslationNormalizer
 * @package Ekyna\Component\Resource\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TranslationNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /**
     * @var ConfigurationRegistry
     */
    private $configurationRegistry;

    /**
     * @var NameConverterInterface
     */
    private $nameConverter;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * Constructor.
     *
     * @param ConfigurationRegistry  $configurationRegistry
     * @param NameConverterInterface $nameConverter
     * @param PropertyAccessor       $propertyAccessor
     */
    public function __construct(
        ConfigurationRegistry $configurationRegistry,
        NameConverterInterface $nameConverter,
        PropertyAccessor $propertyAccessor
    ) {
        $this->configurationRegistry = $configurationRegistry;
        $this->nameConverter = $nameConverter;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @inheritdoc
     */
    public function normalize($translation, $format = null, array $context = [])
    {
        /** @var Model\TranslationInterface $translation */

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        $data = [
            'id' => $translation->getId(),
        ];

        if (in_array('Search', $groups)) {
            $config = $this->findConfiguration($translation);
            foreach ($config->getTranslationFields() as $attribute) {
                $name = $this->nameConverter->normalize($attribute);
                $data[$name] = $this->propertyAccessor->getValue($translation, $attribute);
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Finds the translations's translatable configuration.
     *
     * @param Model\TranslationInterface $translation
     *
     * @return \Ekyna\Component\Resource\Configuration\ConfigurationInterface|NULL
     */
    protected function findConfiguration(Model\TranslationInterface $translation)
    {
        return $this->configurationRegistry->findConfigurationByTranslation($translation, false);
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        if ($data instanceof Model\TranslationInterface) {
            return null !== $this->findConfiguration($data);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, Model\TranslationInterface::class);
    }
}
