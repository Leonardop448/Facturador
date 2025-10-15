<?php

namespace App\nucleo;

use App\controladores\InicioControlador;

class Aplicacion
{
    protected $controlador = 'App\\controladores\\InicioControlador';
    protected $metodo = 'index';

    protected $parametros = [];

    public function __construct()
    {
        $url = $this->obtenerUrl();

        if (isset($url[0])) {
            $nombreControlador = 'App\\controladores\\' . ucfirst($url[0]) . 'Controlador';

            if (class_exists($nombreControlador)) {
                $this->controlador = new $nombreControlador();
                unset($url[0]);
            } else {
                throw new \Exception("Controlador no encontrado: {$nombreControlador}");
            }
        } else {
            $this->controlador = new InicioControlador();
        }

        if (isset($url[1]) && method_exists($this->controlador, $url[1])) {
            $this->metodo = $url[1];
            unset($url[1]);
        }

        $this->parametros = $url ? array_values($url) : [];

        call_user_func_array([$this->controlador, $this->metodo], $this->parametros);
    }

    private function obtenerUrl(): array
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
