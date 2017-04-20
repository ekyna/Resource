<?php

return [
    'acme.category' => [
        'entity'      => [
            'interface' => null,
            'class'     => 'Acme\\Resource\\Entity\\Category',
        ],
        'repository'  => [
            'interface' => null,
            'class'     => 'Ekyna\\Component\\Resource\\Doctrine\\ORM\\Repository\\ResourceRepository',
        ],
        'manager'     => [
            'interface' => null,
            'class'     => 'Ekyna\\Component\\Resource\\Doctrine\\ORM\\Manager\\ResourceManager',
        ],
        'factory'     => [
            'interface' => null,
            'class'     => 'Ekyna\\Component\\Resource\\Doctrine\\ORM\\Factory\\ResourceFactory',
        ],
        'translation' => null,
        'parent'      => null,
        'event'       => 'Ekyna\\Component\\Resource\\Event\\ResourceEvent',
        'actions'     => [],
        'behaviors'   => [],
        'permissions' => [],
        'namespace'   => 'acme',
        'name'        => 'category',
    ],
];
