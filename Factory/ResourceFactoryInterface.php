<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Factory;

use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ResourceFactoryInterface
 * @package  Ekyna\Component\Resource\Factory
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template T
 */
interface ResourceFactoryInterface
{
    public const DI_TAG = 'ekyna_resource.factory';

    /**
     * Sets the resource class.
     */
    public function setClass(string $class): void;

    /**
     * Returns a new resource instance.
     *
     * @return ResourceInterface
     * @psalm-return T
     */
    public function create(): ResourceInterface;

    /**
     * Returns a new resource instance initialized from the action context.
     *
     * @param Context $context
     *
     * @return ResourceInterface
     * @psalm-return T
     */
    public function createFromContext(Context $context): ResourceInterface;
}
