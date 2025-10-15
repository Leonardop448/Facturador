<?php

namespace App\controladores;

use App\nucleo\Controlador;
use App\nucleo\Sesion;

class ModulosControlador extends Controlador
{
    public function inicio()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario'])) {
            echo "<script>window.location='" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $this->vista('modulos/inicio');
    }
}
