<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Mapping;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class EmbeddableMapper
 * @package Ekyna\Component\Resource\Doctrine\ORM\Mapping
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EmbeddableMapper
{
    /**
     * The embeddable metadata.
     */
    private ClassMetadata $embeddableMetadata;

    /**
     * Stores the processed classes names.
     */
    private array $processedClasses;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     * @param string                 $embeddableClass
     */
    public function __construct(EntityManagerInterface $em, string $embeddableClass)
    {
        $this->embeddableMetadata = $em->getClassMetadata($embeddableClass);
        $this->processedClasses = [];
    }

    /**
     * Processes the class metadata.
     *
     * @param ClassMetadata $metadata
     * @param string        $property
     * @param string|null   $prefix
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function processClassMetadata(ClassMetadata $metadata, string $property, string $prefix = null): void
    {
        if (in_array($metadata->getName(), $this->processedClasses)) {
            return;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $metadata->mapEmbedded([
            'fieldName'    => $property,
            'class'        => $this->embeddableMetadata->getName(),
            'columnPrefix' => is_null($prefix) ? false : $prefix,
        ]);

        $metadata->inlineEmbeddable($property, $this->embeddableMetadata);

        $this->processedClasses[] = $metadata->getName();
    }
}
