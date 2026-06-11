<?php

declare(strict_types=1);

return [
    'app_namespace' => 'App',

    'paths' => [
        'enums' => 'app/Enums/Permissions',
        'policies' => 'app/Policies',
        'tests' => [
            'unit_enums' => 'tests/Unit/Enums/Permissions',
            'feature_policies' => 'tests/Feature/Policies',
        ],
    ],

    'models' => [
        'user' => 'App\\Models\\User',
        'permission' => null,
    ],

    'filament' => [
        'panel' => 'admin',
        'record_title_attribute' => 'id',
    ],

    'stubs' => [
        'path' => __DIR__ . '/../stubs',
    ],
];
