<?php

return [
    'permission' => ['trans_domain' => null,],
    'namespace'  => ['label' => null, 'trans_domain' => null,],
    'action'     => [
        'permissions' => null,
        'options'     => ['expose' => false,],
    ],
    'behavior'   => ['options' => [],],
    'resource'   => [
        'repository'   => null,
        'manager'      => null,
        'factory'      => null,
        'translation'  => null,
        'parent'       => null,
        'event'        => null,
        'actions'      => [],
        'behaviors'    => [],
        'permissions'  => [],
        'trans_prefix' => null,
        'trans_domain' => null,
    ],
];
