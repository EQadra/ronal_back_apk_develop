<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AUTENTICACIÓN POR DEFECTO
    |--------------------------------------------------------------------------
    |
    | El guard "web" se usa para las rutas web (como la página de inicio).
    | Tus endpoints API seguirán usando el guard "api" que funciona con JWT.
    |
    */
    'defaults' => [
        'guard' => 'web',          
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | GUARDS (MÉTODOS DE AUTENTICACIÓN)
    |--------------------------------------------------------------------------
    |
    | Aquí defines cómo se autentican los usuarios:
    | - "web": por sesión (para vistas)
    | - "api": por token (JWT)
    |
    */
    'guards' => [

        // Guard para la parte web (incluye la ruta '/')
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard que usan tus endpoints API con JWT
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PROVEEDORES DE USUARIOS
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN DE REINICIO DE CONTRASEÑAS
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TIEMPO DE EXPIRACIÓN DE CONFIRMACIÓN DE CONTRASEÑA
    |--------------------------------------------------------------------------
    */
    'password_timeout' => 10800,

];
