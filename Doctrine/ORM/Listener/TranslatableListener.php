<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Model\TranslatableInterface;
use Ekyna\Component\Resource\Doctrine\AbstractTranslatableListener;
use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Class ORMTranslatableListener
 * @package Ekyna\Component\Resource\Doctrine\ORM\Listener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TranslatableListener extends AbstractTranslatableListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
        ];
    }

    /**
     * Add mapping to translatable entities
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();
        $reflection    = $classMetadata->reflClass;

        if (!$reflection || $reflection->isAbstract()) {
            return;
        }

        if ($reflection->implementsInterface(TranslatableInterface::class)) {
            $this->mapTranslatable($classMetadata);
        }

        if ($reflection->implementsInterface(TranslationInterface::class)) {
            $this->mapTranslation($classMetadata);
        }
    }

    /**
     * Add mapping data to a translatable entity.
     *
     * @param ClassMetadata $metadata
     */
    private function mapTranslatable(ClassMetadata $metadata)
    {
        // In the case A -> B -> TranslatableInterface, B might not have mapping defined as it
        // is probably defined in A, so in that case, we just return.
        if (!isset($this->configs[$metadata->name])) {
            return;
        }

        $metadata->mapOneToMany([
            'fieldName'     => 'translations',
            'targetEntity'  => $this->configs[$metadata->name],
            'mappedBy'      => 'translatable',
            'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            'indexBy'       => 'locale',
            'cascade'       => ['persist', 'merge', 'refresh', 'remove', 'detach'],
            'orphanRemoval' => true,
        ]);
    }

    /**
     * Add mapping data to a translation entity.
     *
     * @param ClassMetadata $metadata
     */
    private function mapTranslation(ClassMetadata $metadata)
    {
        // In the case A -> B -> TranslationInterface, B might not have mapping defined as it
        // is probably defined in A, so in that case, we just return.
        if (!isset($this->configs[$metadata->name])) {
            return;
        }

        $metadata->mapManyToOne([
            'fieldName'    => 'translatable' ,
            'targetEntity' => $this->configs[$metadata->name],
            'inversedBy'   => 'translations' ,
            'joinColumns'  => [[
                'name'                 => 'translatable_id',
                'referencedColumnName' => 'id',
                'onDelete'             => 'CASCADE',
                'nullable'             => false,
            ]],
        ]);

        if (!$metadata->hasField('locale')) {
            $metadata->mapField([
                'fieldName' => 'locale',
                'type'      => 'string',
                'nullable'  => false,
            ]);
        }

        // Map unique index.
        $columns = [
            $metadata->getSingleAssociationJoinColumnName('translatable'),
            'locale'
        ];

        if (!$this->hasUniqueConstraint($metadata, $columns)) {
            $constraints = isset($metadata->table['uniqueConstraints']) ? $metadata->table['uniqueConstraints'] : [];

            $constraints[$metadata->getTableName().'_uniq_trans'] = [
                'columns' => $columns,
            ];

            $metadata->setPrimaryTable([
                'uniqueConstraints' => $constraints,
            ]);
        }

        $metadata->addEntityListener('preFlush', TranslationListener::class, 'preFlush');
    }

    /**
     * Check if an unique constraint has been defined.
     *
     * @param ClassMetadata $metadata
     * @param array         $columns
     *
     * @return bool
     */
    private function hasUniqueConstraint(ClassMetadata $metadata, array $columns)
    {
        if (!isset($metadata->table['uniqueConstraints'])) {
            return false;
        }

        foreach ($metadata->table['uniqueConstraints'] as $constraint) {
            if (!array_diff($constraint['columns'], $columns)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load translations.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof TranslatableInterface) {
            return;
        }

        $entity->initializeTranslations();
        $entity->setCurrentLocale($this->localeProvider->getCurrentLocale());
        $entity->setFallbackLocale($this->localeProvider->getFallbackLocale());
    }
}
