<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Primero definimos BASE_PATH
define('BASE_PATH', realpath(__DIR__ . '/..'));

// ✅ Ahora sí podemos usarla
require_once BASE_PATH . '/configuracion/config.php';
require BASE_PATH . '/vendor/autoload.php';

use App\nucleo\Aplicacion;

$aplicacion = new Aplicacion();
