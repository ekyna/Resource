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
    protected readonly PersistenceHelperInterface $persistenceHelper;

    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper): void
    {
        $this->persistenceHelper = $persistenceHelper;
    }
}
