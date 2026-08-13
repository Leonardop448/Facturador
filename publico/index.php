<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar directorio e iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    if (!is_dir('/tmp/sessions')) {
        mkdir('/tmp/sessions', 0777, true);
    }
    session_save_path('/tmp/sessions');
    session_start();
}

// ✅ Primero definimos BASE_PATH
define('BASE_PATH', realpath(__DIR__ . '/..'));

// ✅ Ahora sí podemos usarla
require_once BASE_PATH . '/configuracion/config.php';
require BASE_PATH . '/vendor/autoload.php';

use App\nucleo\Aplicacion;

$aplicacion = new Aplicacion();
