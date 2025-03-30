<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */


    'paths' => ['api/', 'v1/'],  // Permite CORS en rutas específicas
    'allowed_methods' => [''],  // Permite todos los métodos HTTP (GET, POST, PUT, DELETE, etc.)
    // TODO: just allow the frontend origin
    'allowed_origins' => [''],  // Permite solicitudes desde cualquier origen
    'allowed_origins_patterns' => [],  // Puedes agregar patrones específicos si es necesario
    'allowed_headers' => [''],  // Permite todos los encabezados en las solicitudes
    'exposed_headers' => [],  // Lista de encabezados que serán visibles en el cliente
    'max_age' => 0,  // Tiempo en segundos para almacenar en caché las respuestas de preflight (0 = desactivado)
    'supports_credentials' => false,  // Cambia a true si necesitas enviar cookies o autenticación con la solicitud

];
