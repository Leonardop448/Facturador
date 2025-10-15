<?php

namespace App\nucleo;

class Sesion
{
    public static function iniciarSesionLarga()
    {
        if (session_status() === PHP_SESSION_NONE) {

            ini_set('session.gc_maxlifetime', 18000);

            session_set_cookie_params([
                'lifetime' => 18000, // 
                'path' => '/',
                'domain' => '.pulcast.com', // O 'pulcast.com' según tu dominio
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }
}
