<?php

namespace Ekyna\Component\Resource\Serializer;

use Ekyna\Component\Resource\Model;

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
        /** @var Model\TranslatableInterface $resource */
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

        if (in_array('Search', $groups)) {
            if ($translatable instanceof Model\TranslatableInterface) {
                $translations = [];

                /** @var Model\TranslationInterface $translation */
                foreach ($translatable->getTranslations()->toArray() as $translation) {
                    if ($this->filterTranslation($translation)) {
                        $translations[$translation->getLocale()] =
                            $this->normalizeObject($translation, $format, $context);
                    }
                }

                if (!empty($translations)) {
                    $data['translations'] = $translations;
                }
            }
        }

        return $data;
    }

    /**
     * Returns whether to index the translation.
     *
     * @param Model\TranslationInterface $translation
     *
     * @return bool
     */
    protected function filterTranslation(Model\TranslationInterface $translation)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$resource = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        if ($data instanceof Model\TranslatableInterface) {
            return null !== $this->configurationRegistry->findConfiguration($data);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if (class_exists($type) && is_subclass_of($type, Model\TranslatableInterface::class)) {
            return null !== $this->configurationRegistry->findConfiguration($type);
        }

        return false;
    }
}
