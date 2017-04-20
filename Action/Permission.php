<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Action;

/**
 * Class Actions
 * @package Ekyna\Component\Resource\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Permission
{
    public const LIST   = 'list';
    public const READ   = 'read';
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const SEARCH = 'search';

    public const CREATE_CHILD = 'create_child';
    public const CONFIRM      = 'confirm';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
