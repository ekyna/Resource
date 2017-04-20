<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Ekyna\Component\Resource\Doctrine\AbstractTranslatableListener;
use Ekyna\Component\Resource\Model\TranslatableInterface;
use Ekyna\Component\Resource\Model\TranslationInterface;

use function array_diff;

/**
 * Class ORMTranslatableListener
 * @package Ekyna\Component\Resource\Doctrine\ORM\Listener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TranslatableListener extends AbstractTranslatableListener
{
    /**
     * Add mapping to translatable entities
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     *
     * @throws MappingException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $reflection = $classMetadata->reflClass;

        if (!$reflection || $reflection->isAbstract()) {
            return;
        }

        if ($reflection->implementsInterface(TranslatableInterface::class)) {
            $this->mapTranslatable($classMetadata);

            return;
        }

        if ($reflection->implementsInterface(TranslationInterface::class)) {
            $this->mapTranslation($classMetadata);
        }
    }

    /**
     * Translatable post load event handler.
     *
     * Initializes the translations and sets the current and fallback locales.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof TranslatableInterface) {
            return;
        }

        $entity->initializeTranslations();
        $entity->setCurrentLocale($this->localeProvider->getCurrentLocale());
        $entity->setFallbackLocale($this->localeProvider->getFallbackLocale());
    }

    /**
     * Translatable pre flush event handler.
     *
     * Removes translations when all 'content' fields are set to null.
     *
     * @param TranslatableInterface $translatable
     */
    public function onTranslatablePreFlush(TranslatableInterface $translatable): void
    {
        if (null === $config = $this->registry->find($translatable, false)) {
            return;
        }

        $accessor = $this->getPropertyAccessor();

        foreach ($translatable->getTranslations() as $translation) {
            foreach ($config->getTranslationFields() as $field) {
                if (null !== $accessor->getValue($translation, $field)) {
                    continue 2;
                }
            }

            $translatable->removeTranslation($translation);
        }
    }

    /**
     * Add mapping data to a translatable entity.
     *
     * @param ClassMetadata $metadata
     *
     * @throws MappingException
     */
    private function mapTranslatable(ClassMetadata $metadata): void
    {
        // In the case A -> B -> TranslatableInterface, B might not have mapping defined
        // as it is probably defined in A, so in that case, we just return.
        if (!isset($this->translations[$metadata->name])) {
            return;
        }

        $metadata->mapOneToMany([
            'fieldName'     => 'translations',
            'targetEntity'  => $this->translations[$metadata->name],
            'mappedBy'      => 'translatable',
            //'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
            'indexBy'       => 'locale',
            'cascade'       => ['persist', 'merge', 'refresh', 'remove', 'detach'],
            'orphanRemoval' => true,
        ]);

        /** @see TranslatableListener::onTranslatablePreFlush() */
        $metadata->addEntityListener('preFlush', self::class, 'onTranslatablePreFlush');
    }

    /**
     * Add mapping data to a translation entity.
     *
     * @param ClassMetadata $metadata
     *
     * @throws MappingException
     */
    private function mapTranslation(ClassMetadata $metadata): void
    {
        // In the case A -> B -> TranslationInterface, B might not have mapping defined as it
        // is probably defined in A, so in that case, we just return.
        if (!isset($this->translations[$metadata->name])) {
            return;
        }

        $metadata->mapManyToOne([
            'fieldName'    => 'translatable',
            'targetEntity' => $this->translations[$metadata->name],
            'inversedBy'   => 'translations',
            'joinColumns'  => [
                [
                    'name'                 => 'translatable_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'CASCADE',
                    'nullable'             => false,
                ],
            ],
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
            'locale',
        ];

        if (!$this->hasUniqueConstraint($metadata, $columns)) {
            $constraints = $metadata->table['uniqueConstraints'] ?? [];

            $constraints[$metadata->getTableName() . '_uniq_trans'] = [
                'columns' => $columns,
            ];

            $metadata->setPrimaryTable([
                'uniqueConstraints' => $constraints,
            ]);
        }

        // TODO Replace this : the goal is to trigger a cache invalidation on the translatable.
        /** @see TranslationListener::preFlush() */
        //$metadata->addEntityListener('preFlush', TranslationListener::class, 'preFlush');
    }

    /**
     * Check if an unique constraint has been defined.
     *
     * @param ClassMetadata $metadata
     * @param array         $columns
     *
     * @return bool
     */
    private function hasUniqueConstraint(ClassMetadata $metadata, array $columns): bool
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
}
