<?php

namespace App\controladores;

use App\nucleo\Sesion;
use App\modelos\UsuariosCliente;
use App\nucleo\Controlador;
use App\configuracion\BaseDatos;
use PDO;

class UsuariosclienteControlador extends Controlador
{
    private function validarAcceso()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['rol'], ['cliente', 'admin', 'SuperU'])) {
            echo "<script>alert('Acceso denegado'); window.location='" . RUTA_URL . "/modulos/inicio';</script>";
            exit;
        }
    }

    public function index()
    {
        $this->listar(); // o puedes redirigir a listar directamente
    }


    public function crear()
    {
        $this->validarAcceso();
        include BASE_PATH . '/aplicacion/vistas/usuarioscliente/crear.php';
    }

    public function guardar()
    {
        $this->validarAcceso();

        $conexion = BaseDatos::conectar();
        $modelo = new UsuariosCliente($conexion);

        $rol = $_SESSION['usuario']['rol'];

        // ✅ Determinar correctamente el cliente_id
        if ($rol === 'SuperU' && isset($_POST['cliente_id'])) {
            $cliente_id = $_POST['cliente_id'];
        } elseif ($rol === 'admin') {
            $cliente_id = $this->obtenerClienteIdDeAdmin($_SESSION['usuario']['id'], $conexion);
        } else {
            $cliente_id = $_SESSION['usuario']['id'];
        }

        $nombre = ucwords(strtolower(trim($_POST['nombre'])));
        $documento = trim($_POST['documento']);
        if (!ctype_digit($documento)) {
            echo "<script>alert('El documento debe contener solo números.'); window.history.back();</script>";
            exit;
        }

        $correo = strtolower(trim($_POST['correo']));
        $clave = password_hash($_POST['clave'], PASSWORD_BCRYPT);
        $telefono = trim($_POST['telefono']);
        $direccion = ucwords(strtolower(trim($_POST['direccion'])));
        $rolUsuario = in_array($_POST['rol'], ['admin', 'vendedor']) ? $_POST['rol'] : 'vendedor';

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || !ctype_digit($telefono)) {
            echo "<script>alert('Correo o teléfono inválido'); window.history.back();</script>";
            exit;
        }

        if ($modelo->correoExiste($correo, $cliente_id)) {
            echo "<script>alert('Este correo ya está registrado como usuario interno.'); window.history.back();</script>";
            exit;
        }

        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<script>alert('Este correo ya está registrado como usuario del sistema.'); window.history.back();</script>";
            exit;
        }

        if ($modelo->crear($cliente_id, $nombre, $documento, $correo, $clave, $telefono, $direccion, $rolUsuario)) {
            echo "<script>alert('Usuario creado correctamente'); window.location='" . RUTA_URL . "/usuarioscliente/listar';</script>";
        } else {
            echo "<script>alert('Error al guardar usuario'); window.history.back();</script>";
        }
    }


    public function listar()
    {
        $this->validarAcceso();

        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();
        $modelo = new UsuariosCliente($conexion);

        $rol = $_SESSION['usuario']['rol'];
        $usuarioId = $_SESSION['usuario']['id'];

        if ($rol === 'SuperU') {
            $usuarios = $modelo->obtenerTodosConNombreCliente();
        } elseif ($rol === 'cliente') {
            $usuarios = $modelo->obtenerUsuariosDeClienteYAdmins($usuarioId);
        } elseif ($rol === 'admin') {
            $clienteId = $this->obtenerClienteIdDeAdmin($usuarioId, $conexion);
            $usuarios = $modelo->obtenerUsuariosPorCliente($clienteId);
        } else {
            $usuarios = [];
        }

        include BASE_PATH . '/aplicacion/vistas/usuarioscliente/listar.php';
    }



    private function obtenerClienteIdDeAdmin($adminId, $conexion)
    {
        $stmt = $conexion->prepare("SELECT cliente_id FROM usuarioscliente WHERE id = :id");
        $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['cliente_id'] ?? 0;
    }

    public function actualizar($id)
    {
        $this->validarAcceso();

        $conexion = BaseDatos::conectar();
        $modelo = new UsuariosCliente($conexion);

        $rol = $_SESSION['usuario']['rol'];

        // Si es SuperU, buscamos el cliente_id del usuario a editar
        if ($rol === 'SuperU') {
            $usuarioInterno = $modelo->buscarPorId($id);
            if (!$usuarioInterno) {
                echo "<script>alert('Usuario no encontrado'); window.history.back();</script>";
                exit;
            }
            $cliente_id = $usuarioInterno['cliente_id'];
        } elseif ($rol === 'admin') {
            $cliente_id = $this->obtenerClienteIdDeAdmin($_SESSION['usuario']['id'], $conexion);
        } else {
            $cliente_id = $_SESSION['usuario']['id'];
        }

        $nombre = ucwords(strtolower(trim($_POST['nombre'])));
        $correo = strtolower(trim($_POST['correo']));
        $telefono = trim($_POST['telefono']);
        $direccion = ucwords(strtolower(trim($_POST['direccion'])));
        $rolNuevo = in_array($_POST['rol'], ['admin', 'vendedor']) ? $_POST['rol'] : 'vendedor';

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || !ctype_digit($telefono)) {
            echo "<script>alert('Correo o teléfono inválido'); window.history.back();</script>";
            exit;
        }

        if ($modelo->actualizar($id, $cliente_id, $nombre, $correo, $telefono, $direccion, $rolNuevo)) {
            echo "<script>alert('Usuario actualizado'); window.location='" . RUTA_URL . "/usuarioscliente/listar';</script>";
        } else {
            echo "<script>alert('Error al actualizar usuario'); window.history.back();</script>";
        }
    }


    public function cambiarClave($id)
    {
        $this->validarAcceso();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clave = trim($_POST['clave']);

            if (strlen($clave) < 6) {
                echo "<script>alert('La contraseña debe tener al menos 6 caracteres.'); window.history.back();</script>";
                exit;
            }

            $claveHash = password_hash($clave, PASSWORD_BCRYPT);
            $modelo = new UsuariosCliente(BaseDatos::conectar());

            $clienteId = $_SESSION['usuario']['id'];

            if ($modelo->actualizarClave($id, $clienteId, $claveHash)) {
                echo "<script>alert('Contraseña actualizada.'); window.location = '" . RUTA_URL . "/usuarioscliente/listar';</script>";
            } else {
                echo "<script>alert('No se pudo actualizar la contraseña.'); window.history.back();</script>";
            }
        }
    }

    public function eliminar($id)
    {
        $this->validarAcceso();

        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();
        $modelo = new UsuariosCliente($conexion);

        $rol = $_SESSION['usuario']['rol'];
        $miId = $_SESSION['usuario']['id'];

        $usuarioInterno = $modelo->buscarPorId($id);
        if (!$usuarioInterno) {
            echo "<script>alert('Usuario no encontrado.'); window.history.back();</script>";
            exit;
        }

        $clienteIdDelUsuario = $usuarioInterno['cliente_id'];

        if (
            $rol === 'SuperU' ||
            ($rol === 'admin' && $this->obtenerClienteIdDeAdmin($miId, $conexion) == $clienteIdDelUsuario) ||
            ($rol === 'cliente' && $miId == $clienteIdDelUsuario)
        ) {
            if ($modelo->eliminarPorId($id)) {
                echo "<script>alert('Usuario eliminado correctamente'); window.location='" . RUTA_URL . "/usuarioscliente/listar';</script>";
            } else {
                echo "<script>alert('Error al eliminar usuario'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('No tienes permisos para eliminar este usuario'); window.history.back();</script>";
        }
    }

    public function buscar()
    {
        Sesion::iniciarSesionLarga();

        if (!isset($_SESSION['usuario'])) {
            echo "<script>alert('Debe iniciar sesión'); window.location.href = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $usuario = $_SESSION['usuario'];
        $clienteId = null;

        // Solo los clientes y usuarioscliente deben estar limitados por cliente_id
        if ($usuario['tipo_usuario'] === 'usuarios' && $usuario['rol'] === 'cliente') {
            $clienteId = (int)$usuario['id'];
        } elseif ($usuario['tipo_usuario'] === 'usuarioscliente') {
            $clienteId = (int)$usuario['cliente_id'];
        }

        require_once BASE_PATH . '/aplicacion/modelos/UsuariosCliente.php';
        $modelo = new \App\modelos\UsuariosCliente(\App\configuracion\BaseDatos::conectar());

        $query = trim($_GET['q'] ?? '');
        $usuarios = $modelo->buscarPorNombreCorreoTelefono($query, $clienteId);

        require BASE_PATH . '/aplicacion/vistas/usuarioscliente/listar.php';
    }
}
