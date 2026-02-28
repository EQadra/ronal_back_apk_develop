<?php

return [

    'paths' => [
        'api/*',
        'login',
        'logout',
        'me',
        'profile',
        'profile/*',
        'usuarios',
        'usuarios/*',
        'productos',
        'productos/*',
        'transactions',
        'transactions/*',
        'transaccion',
        'transacciones/dia',
        'caja/abrir',
        'caja/cerrar',
        'caja/actual',
        'roles',
        'roles/*',
        'permisos',
        'permisos/*',
        'reportes',
        'admin/dashboard',
    ],

    'allowed_methods' => ['*'],

    // Para APK nativa es seguro usar '*'
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    // JWT NO usa cookies → debe ser false
    'supports_credentials' => false,

];
