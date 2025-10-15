<?php

namespace App\nucleo;

class Controlador
{
    /**
     * Cargar modelo desde App\modelos
     */
    public function modelo(string $modelo)
    {
        $nombreClase = "App\\modelos\\$modelo";

        if (class_exists($nombreClase)) {
            return new $nombreClase();
        }

        throw new \Exception("El modelo $modelo no fue encontrado.");
    }

    /**
     * Cargar vista desde BASE_PATH/aplicacion/vistas
     */
    public function vista(string $vista, array $datos = []): void
    {
        $vistaPath = BASE_PATH . "/aplicacion/vistas/$vista.php";

        if (file_exists($vistaPath)) {
            extract($datos);
            require $vistaPath;
        } else {
            throw new \Exception("La vista $vista no fue encontrada.");
        }
    }
}
