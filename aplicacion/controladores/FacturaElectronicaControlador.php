<?php

namespace App\controladores;

use App\nucleo\Controlador;

class FacturaElectronicaControlador extends Controlador
{
    public function index()
    {
        // Pasamos un título para que la vista sea dinámica
        $this->vista('/en_construccion', [
            'modulo' => 'Facturación Electrónica'
        ]);
    }
}
