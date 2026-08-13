<?php

namespace App\controladores;

use App\nucleo\Controlador;

class PedidosControlador extends Controlador
{
    public function index()
    {
        // Esta vista utiliza tu archivo en: aplicacion/vistas/en_construccion.php
        $this->vista('en_construccion', [
            'modulo' => 'Pedidos'
        ]);
    }
}
