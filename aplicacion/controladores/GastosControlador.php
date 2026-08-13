<?php

namespace App\controladores;

use App\nucleo\Controlador;

class GastosControlador extends Controlador
{
    public function index()
    {
        // Esta vista utiliza tu archivo en: aplicacion/vistas/en_construccion.php
        $this->vista('en_construccion', [
            'modulo' => 'Gastos'
        ]);
    }
}
