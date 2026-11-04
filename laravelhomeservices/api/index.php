<?php

/*
 * Entrada de Vercel: solo ejecuta funciones dentro de api/. El disco es de solo lectura salvo /tmp,
 * así que aquí se llevan a /tmp los archivos que Laravel genera al arrancar.
 * Secretos y servicios van en las variables del proyecto (ver .env.vercel.example).
 */
foreach ([
    'VIEW_COMPILED_PATH' => '/tmp/views',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_CONFIG_CACHE' => '/tmp/config.php',
    'APP_ROUTES_CACHE' => '/tmp/routes.php',
    'APP_EVENTS_CACHE' => '/tmp/events.php',
] as $name => $path) {
    $_ENV[$name] = $_SERVER[$name] = $path;
    putenv($name . '=' . $path);
}

require __DIR__ . '/../public/index.php';
