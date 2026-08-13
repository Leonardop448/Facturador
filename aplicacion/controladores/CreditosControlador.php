<?php

namespace App\controladores;

use App\nucleo\Controlador;

class CreditosControlador extends Controlador
{
    public function index()
    {
        // Reutilizamos la misma vista, solo cambiamos el nombre del módulo
        $this->vista('/en_construccion', [
            'modulo' => 'Gestión de Créditos'
        ]);
    }
}
