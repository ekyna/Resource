<?php

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
     *
     * @var ClassMetadata
     */
    private $embeddableMetadata;

    /**
     * Stores the processed classes names.
     *
     * @var array
     */
    private $processedClasses;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     * @param string                 $embeddableClass
     */
    public function __construct(EntityManagerInterface $em, $embeddableClass)
    {
        $this->embeddableMetadata = $em->getClassMetadata($embeddableClass);
        $this->processedClasses = [];
    }

    /**
     * Processes the class metadata.
     *
     * @param ClassMetadata $metadata
     * @param string        $property
     * @param string        $prefix
     */
    public function processClassMetadata(ClassMetadata $metadata, $property, $prefix)
    {
        if (in_array($metadata->getName(), $this->processedClasses)) {
            return;
        }

        $metadata->mapEmbedded([
            'fieldName'    => $property,
            'class'        => $this->embeddableMetadata->getName(),
            'columnPrefix' => empty($prefix) ? false : $prefix,
        ]);

        $metadata->inlineEmbeddable($property, $this->embeddableMetadata);

        $this->processedClasses[] = $metadata->getName();
    }
}
