<?php

namespace App\controladores;

use App\nucleo\Sesion;
use App\modelos\Proveedores;
use App\configuracion\BaseDatos;

require_once BASE_PATH . '/configuracion/BaseDatos.php';
require_once BASE_PATH . '/aplicacion/modelos/Proveedores.php';
require_once BASE_PATH . '/configuracion/config.php';

if (session_status() === PHP_SESSION_NONE) {
    Sesion::iniciarSesionLarga();
}

class ProveedoresControlador
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new Proveedores(BaseDatos::conectar());
    }

    private function obtenerClienteIdSesion(): ?int
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if (!$usuario) {
            echo "<script>window.location.href = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        if ($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'SuperU') {
            return null;
        }

        if ($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'cliente') {
            return (int)$usuario['id'];
        }

        if ($usuario['tipo_usuario'] === 'usuarioscliente') {
            return (int)$usuario['cliente_id'];
        }

        echo "<script>alert('No se pudo identificar el cliente.');</script>";
        exit;
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteIdSesion();
        $proveedores = $this->modelo->obtenerTodosPorCliente($clienteId);
        require BASE_PATH . '/aplicacion/vistas/proveedores/index.php';
    }

    public function crear()
    {
        $usuario = $_SESSION['usuario'];
        $clientesDisponibles = [];

        if ($usuario['rol'] === 'SuperU') {
            require_once BASE_PATH . '/aplicacion/modelos/Usuario.php';
            $usuariosModelo = new \App\modelos\Usuario(BaseDatos::conectar());
            $clientesDisponibles = $usuariosModelo->obtenerTodosLosClientes();
        }

        require BASE_PATH . '/aplicacion/vistas/proveedores/formulario.php';
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_SESSION['usuario'];
            $clienteId = ($usuario['rol'] === 'SuperU') ? (int)$_POST['cliente_id'] : $this->obtenerClienteIdSesion();

            if ($this->modelo->existeDocumento($_POST['documento'], $clienteId)) {
                echo "<script>alert('Ya existe un proveedor con ese NIT para este cliente'); window.location.href = '" . RUTA_URL . "/proveedores/crear';</script>";
                return;
            }

            $datos = [
                'cliente_id' => $clienteId,
                'nombre' => trim($_POST['nombre']),
                'documento' => trim($_POST['documento']),
                'telefono' => trim($_POST['telefono']),
                'email' => trim($_POST['email']),
                'ciudad' => trim($_POST['ciudad']),
                'direccion' => trim($_POST['direccion'])
            ];

            $this->modelo->crear($datos);

            echo "<script>window.location.href = '" . RUTA_URL . "/proveedores/index';</script>";
        }
    }

    public function editar($id)
    {
        $usuario = $_SESSION['usuario'];
        $clienteId = ($usuario['rol'] === 'SuperU') ? null : $this->obtenerClienteIdSesion();
        $proveedor = $this->modelo->obtenerPorId($id, $clienteId);
        $clientesDisponibles = [];

        if ($usuario['rol'] === 'SuperU') {
            require_once BASE_PATH . '/aplicacion/modelos/Usuario.php';
            $usuariosModelo = new \App\modelos\Usuario(BaseDatos::conectar());
            $clientesDisponibles = $usuariosModelo->obtenerTodosLosClientes();
        }

        require BASE_PATH . '/aplicacion/vistas/proveedores/formulario.php';
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_SESSION['usuario'];
            $id = (int) $_POST['id'];
            $clienteId = ($usuario['rol'] === 'SuperU') ? (int) $_POST['cliente_id'] : $this->obtenerClienteIdSesion();

            if ($this->modelo->existeDocumento($_POST['documento'], $clienteId, $id)) {
                echo "<script>alert('Ya existe un proveedor con ese NIT para este cliente'); window.location.href = '" . RUTA_URL . "/proveedores/editar/$id';</script>";
                return;
            }

            $this->modelo->actualizar($id, [
                'cliente_id' => $clienteId,
                'nombre' => trim($_POST['nombre']),
                'documento' => trim($_POST['documento']),
                'telefono' => trim($_POST['telefono']),
                'email' => trim($_POST['email']),
                'ciudad' => trim($_POST['ciudad']),
                'direccion' => trim($_POST['direccion'])
            ], $clienteId);

            echo "<script>window.location.href = '" . RUTA_URL . "/proveedores/index';</script>";
        }
    }

    public function eliminar($id)
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if (!$usuario) {
            echo "<script>alert('Debe iniciar sesión'); window.location.href = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $clienteIdSesion = $this->obtenerClienteIdSesion();
        $proveedor = $this->modelo->obtenerPorId((int)$id, $clienteIdSesion);


        if (!$proveedor) {
            echo "<script>alert('Proveedor no encontrado'); window.location.href = '" . RUTA_URL . "/proveedores/index';</script>";
            return;
        }

        // Si no es SuperU, validar que el proveedor le pertenezca
        if ($usuario['rol'] !== 'SuperU' && $proveedor['cliente_id'] != $clienteIdSesion) {
            echo "<script>alert('No tiene permisos para eliminar este proveedor'); window.location.href = '" . RUTA_URL . "/proveedores/index';</script>";
            return;
        }

        $this->modelo->eliminar((int)$id, $clienteIdSesion);

        echo "<script>window.location.href = '" . RUTA_URL . "/proveedores/index';</script>";
    }

    public function buscar()
    {
        $clienteId = $this->obtenerClienteIdSesion();
        $proveedores = [];

        if (!empty($_GET['q'])) {
            $proveedores = $this->modelo->buscar(trim($_GET['q']), $clienteId);
        }

        require BASE_PATH . '/aplicacion/vistas/proveedores/index.php';
    }
}
