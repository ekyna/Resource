<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Persistence;

/**
 * Trait PersistenceAwareTrait
 * @package Ekyna\Component\Resource\Persistence
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait PersistenceAwareTrait
{
    private PersistenceHelperInterface $persistenceHelper;

    public function getPersistenceHelper(): PersistenceHelperInterface
    {
        return $this->persistenceHelper;
    }

    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper): void
    {
        $this->persistenceHelper = $persistenceHelper;
    }
}
