<?php

namespace Ekyna\Component\Resource\Doctrine\ORM\Listener;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Class TranslationListener
 * @package Ekyna\Component\Resource\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TranslationListener
{
    /**
     * Pre update event handler.
     *
     * @param TranslationInterface $translation
     * @param PreFlushEventArgs $event
     */
    public function preFlush(TranslationInterface $translation, PreFlushEventArgs $event)
    {
        if (null !== $translatable = $translation->getTranslatable()) {
            if (method_exists($translatable, 'setUpdatedAt')) {
                call_user_func([$translatable, 'setUpdatedAt'], new \DateTime());

                $em = $event->getEntityManager();
                $uow = $em->getUnitOfWork();
                $metadata = $em->getClassMetadata(get_class($translatable));
                if ($uow->getEntityChangeSet($translatable)) {
                    $uow->recomputeSingleEntityChangeSet($metadata, $translatable);
                } else {
                    $uow->computeChangeSet($metadata, $translatable);
                }
            }
        }
    }
}
