<?php

namespace App\controladores;

use App\nucleo\Sesion;
use App\modelos\Clientes;
use App\configuracion\BaseDatos;

require_once BASE_PATH . '/configuracion/BaseDatos.php';
require_once BASE_PATH . '/aplicacion/modelos/Clientes.php';
require_once BASE_PATH . '/configuracion/config.php';

if (session_status() === PHP_SESSION_NONE) {
    Sesion::iniciarSesionLarga();
}

class ClientesControlador
{
    private $clientesModelo;

    public function __construct()
    {
        $conexion = BaseDatos::conectar();
        $this->clientesModelo = new Clientes($conexion);
    }

    private function obtenerClienteIdSesion(): ?int
    {
        if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
            echo "<script>window.location.href = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $usuario = $_SESSION['usuario'];

        if ($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'SuperU') {
            return null; // SuperU no está atado a ningún cliente
        }

        if ($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'cliente') {
            return (int) $usuario['id'];
        }

        if ($usuario['tipo_usuario'] === 'usuarioscliente' && isset($usuario['cliente_id'])) {
            return (int) $usuario['cliente_id'];
        }

        echo "<script>alert('No se pudo obtener el cliente asignado');</script>";
        exit;
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteIdSesion();
        $clientes = $this->clientesModelo->obtenerTodosPorCliente($clienteId);
        require_once BASE_PATH . '/aplicacion/vistas/clientes/index.php';
    }

    public function crear()
    {
        $clientesDisponibles = [];

        $usuario = $_SESSION['usuario'];
        if ($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'SuperU') {
            require_once BASE_PATH . '/aplicacion/modelos/Usuario.php';
            $usuariosModelo = new \App\modelos\Usuario(BaseDatos::conectar());
            $clientesDisponibles = $usuariosModelo->obtenerTodosLosClientes();
        }

        require BASE_PATH . '/aplicacion/vistas/clientes/formulario.php';
    }


    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_SESSION['usuario'];
            $clienteId = null;

            if ($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'SuperU') {
                $clienteId = (int) $_POST['cliente_id'];
            } else {
                $clienteId = $this->obtenerClienteIdSesion();
            }

            $datos = [
                'cliente_id' => $clienteId,
                'nombre' => trim($_POST['nombre']),
                'documento' => trim($_POST['documento']),
                'correo' => trim($_POST['correo']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion']),
            ];

            // Validar que no exista ya ese documento para ese cliente_id
            if ($this->clientesModelo->existeDocumentoParaCliente($datos['documento'], $clienteId)) {
                echo "<script>alert('Ya existe un cliente con ese documento registrado para esta empresa.'); window.history.back();</script>";
                exit;
            }

            $this->clientesModelo->crear($datos);

            echo "<script>window.location.href = '" . RUTA_URL . "/clientes/index';</script>";
        }
    }



    public function editar($id)
    {
        $usuario = $_SESSION['usuario'];
        $clienteId = null;

        if (!($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'SuperU')) {
            $clienteId = $this->obtenerClienteIdSesion();
        }

        $cliente = $this->clientesModelo->obtenerPorId($id, $clienteId);
        require_once BASE_PATH . '/aplicacion/vistas/clientes/formulario.php';
    }


    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_SESSION['usuario'];
            $id = (int) $_POST['id'];

            if ($usuario['rol'] === 'SuperU') {
                $clienteId = isset($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : null;
            } else {
                $clienteId = $this->obtenerClienteIdSesion();
            }

            if ($clienteId === null) {
                echo "<script>alert('Cliente no válido.'); window.location.href = '" . RUTA_URL . "/clientes/index';</script>";
                exit;
            }

            $datos = [
                'nombre' => trim($_POST['nombre']),
                'documento' => trim($_POST['documento']),
                'correo' => trim($_POST['correo']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion']),
            ];

            // Validar documento duplicado excluyendo al cliente actual
            if ($this->clientesModelo->existeDocumentoParaCliente($datos['documento'], $clienteId, $id)) {
                echo "<script>alert('Ya existe otro cliente con ese documento registrado para esta empresa.'); window.history.back();</script>";
                exit;
            }

            $this->clientesModelo->actualizar($id, $datos, $clienteId);

            echo "<script>window.location.href = '" . RUTA_URL . "/clientes/index';</script>";
        }
    }




    public function eliminar($id)
    {
        // Bloquear acceso a vendedores
        if ($_SESSION['usuario']['rol'] === 'vendedor') {
            echo "<script>alert('Acceso denegado: no tienes permiso para eliminar clientes.'); window.location.href = '" . RUTA_URL . "/clientes/index';</script>";
            exit;
        }

        $clienteId = $this->obtenerClienteIdSesion();
        $this->clientesModelo->eliminar($id, $clienteId);

        echo "<script>window.location.href = '" . RUTA_URL . "/clientes/index';</script>";
    }


    public function buscar()
    {
        $clienteId = $this->obtenerClienteIdSesion();
        $clientes = [];

        if (!empty($_GET['q'])) {
            $clientes = $this->clientesModelo->buscarPorNombreODocumento(trim($_GET['q']), $clienteId);
        }

        require_once BASE_PATH . '/aplicacion/vistas/clientes/index.php';
    }
}
