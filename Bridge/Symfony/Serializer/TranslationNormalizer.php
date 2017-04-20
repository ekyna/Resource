<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Serializer;

use Ekyna\Component\Resource\Model;
use Exception;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Class TranslationNormalizer
 * @package Ekyna\Component\Resource\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TranslationNormalizer extends ResourceNormalizer
{
    use SerializerAwareTrait;

    /**
     * @var string[]
     */
    private array $translationFields;


    /**
     * Sets the translation fields.
     *
     * @param array $fields
     */
    public function setTranslationFields(array $fields): void
    {
        $this->translationFields = $fields;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Model\TranslationInterface $object $translation */

        $data = [
            'id' => $object->getId(),
        ];

        foreach ($this->translationFields as $attribute) {
            $name        = $this->nameConverter->normalize($attribute);
            $data[$name] = $this->propertyAccessor->getValue($object, $attribute);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        throw new Exception('Not yet implemented');
    }
}
