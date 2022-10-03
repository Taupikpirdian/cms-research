<?php

use Cactuar\Admin\Helpers\admin;

return [
    'user' => [
        'label' => 'User',
        'fa' => 'user',
        'controller' => 'Cactuar\Admin\Http\Controllers\UserController',
        'routes' => admin::moduleRoutes(['index','create','edit','delete','email-template']),
    ],
    'privilege' => [
        'label' => 'Privilege',
        'fa' => 'key',
        'controller' => 'Cactuar\Admin\Http\Controllers\PrivilegeController',
        'routes' => admin::moduleRoutes(['index','create','edit','delete']),
    ]
];