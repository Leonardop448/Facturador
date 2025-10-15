<?php

namespace App\controladores;

use App\nucleo\Controlador;

class InformacionControlador extends Controlador
{
    public function contacto()
    {
        include BASE_PATH . '/aplicacion/vistas/informacion/contacto.php';
    }

    public function privacidad()
    {
        include BASE_PATH . '/aplicacion/vistas/informacion/privacidad.php';
    }

    public function terminos()
    {
        include BASE_PATH . '/aplicacion/vistas/informacion/terminos.php';
    }
}
