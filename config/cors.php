<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    // El origen exacto de tu frontend
   'allowed_origins' => ['http://127.0.0.1:8000'], // Tu origen exacto

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // CRUCIAL para que funcionen las cookies de sesión de Sanctum
  'supports_credentials' => true,

];