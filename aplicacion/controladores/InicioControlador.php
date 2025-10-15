<?php

namespace App\controladores;

use App\nucleo\Controlador;

class InicioControlador extends Controlador
{
    public function index()
    {
        $this->vista('inicio/index');
    }
}
