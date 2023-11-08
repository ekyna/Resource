<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Serializer;

use Ekyna\Component\Resource\Model;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

use function class_exists;
use function in_array;
use function is_null;
use function is_string;
use function is_subclass_of;

/**
 * Class AbstractResourceNormalizer
 * @package Ekyna\Component\Resource\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    protected string                 $class;
    protected NameConverterInterface $nameConverter;
    protected PropertyAccessor       $propertyAccessor;


    /**
     * Sets the class.
     *
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * Sets the name converter.
     *
     * @param NameConverterInterface $converter
     */
    public function setNameConverter(NameConverterInterface $converter): void
    {
        $this->nameConverter = $converter;
    }

    /**
     * Sets the property accessor.
     *
     * @param PropertyAccessor $accessor
     */
    public function setPropertyAccessor(PropertyAccessor $accessor): void
    {
        $this->propertyAccessor = $accessor;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var Model\ResourceInterface $object */
        return [
            'id'   => $object->getId(),
            // TODO 'text' entry is only used by choice form types when creating choices from a XHR response.
            'text' => (string)$object, // 'text' Required for Select2
        ];
    }

    /**
     * Normalizes the object.
     */
    protected function normalizeObject(?object $object, string $format, array $context): array|string|null
    {
        if (is_null($object)) {
            return null;
        }

        if (!$this->serializer instanceof NormalizerInterface) {
            throw new LogicException(
                'Cannot normalize object because the injected serializer is not a normalizer'
            );
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->serializer->normalize($object, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        throw new Exception('Not yet implemented');
    }

    /**
     * Denormalizes the object.
     *
     * @param array  $data
     * @param string $class
     * @param string $format
     * @param array  $context
     *
     * @return object
     */
    protected function denormalizeObject(array $data, string $class, string $format, array $context): object
    {
        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException(
                'Cannot denormalize object because the injected serializer is not a denormalizer'
            );
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->serializer->denormalize($data, $class, $format, $context);
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof $this->class;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return class_exists($type) && is_subclass_of($type, $this->class, true);
    }

    /**
     * Returns whether the given groups are configured in the serialization context.
     *
     * @param string|array<string> $search  The group(s) to test
     * @param array                $context The serialization context
     *
     * @return bool
     */
    public static function contextHasGroup(string|array $search, array $context): bool
    {
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (empty($groups)) {
            return false;
        }

        if (is_string($search)) {
            return in_array($search, $groups, true);
        }

        foreach ($search as $group) {
            if (in_array($group, $groups, true)) {
                return true;
            }
        }

        return false;
    }
}
