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
        $data = parent::normalize($resource, $format, $context);

        /** @var Model\ResourceInterface $resource */
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Default', $groups)) {
            if ($resource instanceof Model\TranslatableInterface) {
                $data['translations'] = array_map(function (Model\TranslationInterface $t) use ($format, $context) {
                    return $t->getId();
                }, $resource->getTranslations()->toArray());
            }
        } elseif (in_array('Search', $groups)) {
            if ($resource instanceof Model\TranslatableInterface) {
                $data['translations'] = array_map(function (Model\TranslationInterface $t) use ($format, $context) {
                    return $this->normalizeObject($t, $format, $context);
                }, $resource->getTranslations()->toArray());
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
