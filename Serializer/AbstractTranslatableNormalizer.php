<?php

namespace Ekyna\Component\Resource\Serializer;

use Ekyna\Component\Resource\Model;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Class AbstractTranslatableNormalizer
 * @package Ekyna\Component\Resource\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatableNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     */
    public function normalize($resource, $format = null, array $context = [])
    {
        return array_replace(
            parent::normalize($resource, $format, $context),
            $this->normalizeTranslations($resource, $format, $context)
        );
    }

    /**
     * Normalizes the translatable's translations.
     *
     * @param Model\TranslatableInterface $translatable
     * @param string                      $format
     * @param array                       $context
     *
     * @return array
     */
    protected function normalizeTranslations(Model\TranslatableInterface $translatable, $format, array $context = [])
    {
        $data = [];

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        /*if (in_array('Default', $groups)) {
            if ($translatable instanceof Model\TranslatableInterface) {
                $data['translations'] = array_map(function (Model\TranslationInterface $t) use ($format, $context) {
                    return $t->getId();
                }, $translatable->getTranslations()->toArray());
            }
        }*/
        if (in_array('Search', $groups)) {
            if ($translatable instanceof Model\TranslatableInterface) {
                $data['translations'] = array_map(function (Model\TranslationInterface $t) use ($format, $context) {
                    return $this->normalizeObject($t, $format, $context);
                }, $translatable->getTranslations()->toArray());
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $resource = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }
}
