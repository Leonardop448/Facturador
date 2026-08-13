<?php

namespace App\controladores;

use App\nucleo\Controlador;

class InicioControlador extends Controlador
{
    public function index()
    {
        // Capturamos el mensaje de la URL si existe
        $mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : null;

        // Pasamos el mensaje a la vista en el array de datos
        $this->vista('inicio/index', ['mensaje' => $mensaje]);
    }
}
