<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Persistence;

/**
 * Interface PersistenceAwareInterface
 * @package Ekyna\Component\Resource\Persistence
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface PersistenceAwareInterface
{
    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper): void;
}
