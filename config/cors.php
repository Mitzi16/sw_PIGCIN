<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

  'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

  'allowed_methods' => ['*'],

  'allowed_origins' => [
    'http://sw_pigcin.test:8080',
    'http://192.168.100.111:8080',
    'http://localhost:8080',
    'http://127.0.0.1:8000'
  ],
  'allowed_origins_patterns' => [],

  'allowed_headers' => ['*'],

  'exposed_headers' => [],

  'max_age' => 0,

  // CRUCIAL para que funcionen las cookies de sesión de Sanctum
  'supports_credentials' => true,

];
