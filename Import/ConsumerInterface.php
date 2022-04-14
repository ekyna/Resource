<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Import;

use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Interface ConsumerInterface
 * @package Ekyna\Component\Resource\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ConsumerInterface
{
    public function getConfig(): ConfigInterface;

    public function initialize(
        FactoryFactoryInterface    $factoryFactory,
        RepositoryFactoryInterface $repositoryFactory,
        ValidatorInterface         $validator,
        PropertyAccessor           $accessor
    );

    public function consume(array $data): ?ResourceInterface;

    public function preFlush(): void;

    public function postFlush(): void;
}
