<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Serializer;

use Ekyna\Component\Resource\Model;
use Exception;

use function array_replace;
use function in_array;

/**
 * Class TranslatableNormalizer
 * @package Ekyna\Component\Resource\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TranslatableNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var Model\TranslatableInterface $object */
        return array_replace(
            parent::normalize($object, $format, $context),
            $this->normalizeTranslations($object, $format, $context)
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
    protected function normalizeTranslations(
        Model\TranslatableInterface $translatable,
        string $format,
        array $context = []
    ): array {
        $data = [];

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Search', $groups)) {
            $translations = [];

            /** @var Model\TranslationInterface $translation */
            foreach ($translatable->getTranslations()->toArray() as $translation) {
                if (!$this->filterTranslation($translation)) {
                    continue;
                }

                $translations[$translation->getLocale()] =
                    $this->normalizeObject($translation, $format, $context);
            }

            if (!empty($translations)) {
                $data['translations'] = $translations;
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
    protected function filterTranslation(Model\TranslationInterface $translation): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        //$resource = parent::denormalize($data, $class, $format, $context);

        throw new Exception('Not yet implemented');
    }
}
