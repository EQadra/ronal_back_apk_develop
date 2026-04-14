<?php
return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie', // 👈 FALTA ESTO (CRÍTICO)
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5173'], // 👈 ESPECÍFICO

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true, // 👈 NECESARIO PARA COOKIES

];