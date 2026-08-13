<?php

namespace App\controladores;


require_once BASE_PATH . '/nucleo/Controlador.php';

use App\nucleo\Sesion;
use App\modelos\Usuario;
use App\modelos\Sesiones;
use App\configuracion\BaseDatos;
use App\configuracion\Correo;
use App\nucleo\Controlador;


class UsuariosControlador extends Controlador
{

    private $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::conectar();
    }


    public function registro()
    {
        include BASE_PATH . '/aplicacion/vistas/usuarios/registro.php';
    }

    public function guardarRegistro()
    {
        require_once BASE_PATH . '/configuracion/Correo.php';
        require_once BASE_PATH . '/configuracion/BaseDatos.php';

        $conexion = BaseDatos::conectar();
        $usuario = new Usuario($conexion);

        $nombre = ucwords(strtolower(trim($_POST['nombre'])));
        $documento = trim($_POST['documento']);
        $correo = strtolower(trim($_POST['correo']));
        $telefono = trim($_POST['telefono']);
        $direccion = ucwords(strtolower(trim($_POST['direccion'])));
        $clave = $_POST['clave'];
        $rol = 'usuario';

        if (!ctype_digit($documento) || !ctype_digit($telefono)) {
            echo "<script>alert('Documento y teléfono deben contener solo números.'); window.history.back();</script>";
            exit;
        }

        if ($usuario->existeCorreoODocumento($correo, $documento)) {
            echo "<script>alert('Ya existe un usuario con este correo o documento de identidad.'); window.history.back();</script>";
            exit;
        }

        $token = bin2hex(random_bytes(32));

        if ($usuario->registrar($nombre, $documento, $correo, $telefono, $direccion, $clave, $rol, $token)) {
            $url = RUTA_URL . '/usuarios/activarCuenta/' . $token;
            Correo::enviarCorreoActivacion($correo, $nombre, $url);

            echo "<script>alert('Registro exitoso. Revisa tu correo para activar la cuenta.'); window.location='" . RUTA_URL . "/usuarios/login';</script>";
        } else {
            echo "<script>alert('Error al registrar.'); window.history.back();</script>";
        }
    }

    public function activarCuenta($token)
    {
        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();
        $usuario = new Usuario($conexion);

        if ($usuario->activarUsuario($token)) {
            echo "<script>alert('Cuenta activada correctamente'); window.location='" . RUTA_URL . "/usuarios/login';</script>";
        } else {
            echo "<script>alert('Token inválido o expirado'); window.location='" . RUTA_URL . "/usuarios/login';</script>";
        }
    }

    public function index()
    {
        Sesion::iniciarSesionLarga();
        if (isset($_SESSION['usuario'])) {
            header("Location: " . RUTA_URL . "/inicio");
        } else {
            $this->login();
        }
        exit;
    }

    public function login()
    {
        include BASE_PATH . '/aplicacion/vistas/usuarios/login.php';
    }

    public function iniciarSesion()
    {
        require_once BASE_PATH . '/configuracion/BaseDatos.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_POST['correo'] ?? '';
            $clave = $_POST['clave'] ?? '';

            $conexion = BaseDatos::conectar();

            // Buscar en tabla usuarios
            $usuarioModelo = new Usuario($conexion);
            $usuario = $usuarioModelo->buscarPorCorreo($correo);

            if ($usuario && password_verify($clave, $usuario['clave'])) {
                if ($usuario['estado'] === 'activo') {
                    Sesion::iniciarSesionLarga();
                    $rolReal = 'usuario';

                    if ($usuario['rol'] === 'SuperU') {
                        $rolReal = 'SuperU';
                    } elseif (
                        $usuario['rol'] === 'cliente' &&
                        !empty($usuario['cliente_hasta']) &&
                        strtotime($usuario['cliente_hasta']) >= strtotime(date('Y-m-d'))
                    ) {
                        $rolReal = 'cliente';
                    }

                    $_SESSION['usuario'] = [
                        'id' => $usuario['id'],
                        'nombre' => $usuario['nombre'],
                        'documento' => $usuario['documento_identidad'],
                        'correo' => $usuario['correo'],
                        'telefono' => $usuario['telefono'],
                        'direccion' => $usuario['direccion'],
                        'rol' => $rolReal,
                        'cliente_hasta' => $usuario['cliente_hasta'],
                        'correo_notificaciones' => $usuario['correo_notificaciones'],
                        'alertas_vencimiento' => $usuario['alertas_vencimiento'],
                        'tipo_usuario' => 'usuarios',
                        'cliente_id' => $usuario['id']
                    ];

                    // Registrar sesión en tabla sesiones
                    require_once BASE_PATH . '/aplicacion/modelos/Sesiones.php';
                    $modeloSesion = new Sesiones($conexion);
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $navegador = $_SERVER['HTTP_USER_AGENT'];
                    $modeloSesion->registrarSesion($usuario['id'], $ip, $navegador);

                    echo "<script>window.location = '" . RUTA_URL . "/modulos/inicio';</script>";
                    return;
                } else {
                    echo "<script>alert('Tu cuenta no está activa. Revisa tu correo.'); window.history.back();</script>";
                    return;
                }
            }

            // Buscar en usuarioscliente si no fue encontrado en usuarios
            require_once BASE_PATH . '/aplicacion/modelos/UsuariosCliente.php';
            $modeloUsuariosCliente = new \App\modelos\UsuariosCliente($conexion);
            $usuarioCliente = $modeloUsuariosCliente->buscarPorCorreo($correo);

            if ($usuarioCliente && password_verify($clave, $usuarioCliente['clave'])) {
                if ($usuarioCliente['estado'] === 'activo') {
                    Sesion::iniciarSesionLarga();

                    $_SESSION['usuario'] = [
                        'id' => $usuarioCliente['id'],
                        'nombre' => $usuarioCliente['nombre'],
                        'documento' => $usuarioCliente['documento_identidad'],
                        'correo' => $usuarioCliente['correo'],
                        'telefono' => $usuarioCliente['telefono'],
                        'direccion' => $usuarioCliente['direccion'],
                        'rol' => $usuarioCliente['rol'],
                        'cliente_id' => $usuarioCliente['cliente_id'],
                        'tipo_usuario' => 'usuarioscliente'
                    ];

                    // Registrar sesión en tabla sesiones_usuarioscliente
                    require_once BASE_PATH . '/aplicacion/modelos/SesionUsuariosCliente.php';
                    $modeloSesion = new \App\modelos\SesionUsuariosCliente($conexion);
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $navegador = $_SERVER['HTTP_USER_AGENT'];
                    $modeloSesion->registrarSesion($usuarioCliente['id'], $ip, $navegador);

                    echo "<script>window.location = '" . RUTA_URL . "/modulos/inicio';</script>";
                    return;
                } else {
                    echo "<script>alert('Tu cuenta de empleado está inactiva'); window.history.back();</script>";
                    return;
                }
            }

            // Si no se encontró en ninguna tabla
            echo "<script>alert('Credenciales incorrectas'); window.history.back();</script>";
        }
    }




    public function cerrarSesion()
    {
        Sesion::iniciarSesionLarga();
        unset($_SESSION['usuario']);
        echo "<script>window.location = '" . RUTA_URL . "/inicio?mensaje=sesion_cerrada';</script>";
    }

    public function perfil()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario'])) {
            echo "<script>alert('Debes iniciar sesión'); window.location = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        include BASE_PATH . '/aplicacion/vistas/usuarios/perfil.php';
    }

    public function actualizarPerfil()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario'])) {
            echo "<script>alert('Debes iniciar sesión'); window.location='" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();
        $usuarioModelo = new Usuario($conexion);

        $id = $_SESSION['usuario']['id'];
        $nombre = ucwords(strtolower(trim($_POST['nombre'])));
        $telefono = preg_replace('/\D/', '', $_POST['telefono']);
        $direccion = ucwords(strtolower(trim($_POST['direccion'])));

        if (empty($nombre) || empty($telefono) || empty($direccion)) {
            echo "<script>alert('Todos los campos son obligatorios'); window.history.back();</script>";
            exit;
        }

        if (!ctype_digit($telefono)) {
            echo "<script>alert('El teléfono solo debe contener números'); window.history.back();</script>";
            exit;
        }

        if ($usuarioModelo->actualizarPerfil($id, $nombre, $telefono, $direccion)) {
            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['telefono'] = $telefono;
            $_SESSION['usuario']['direccion'] = $direccion;

            if (!empty($_POST['clave_actual']) && !empty($_POST['clave_nueva']) && !empty($_POST['clave_confirmar'])) {
                $clave_actual = $_POST['clave_actual'];
                $clave_nueva = $_POST['clave_nueva'];
                $clave_confirmar = $_POST['clave_confirmar'];

                if ($clave_nueva !== $clave_confirmar) {
                    echo "<script>alert('La nueva contraseña y su confirmación no coinciden.'); window.history.back();</script>";
                    exit;
                }

                if (!$usuarioModelo->verificarClave($id, $clave_actual)) {
                    echo "<script>alert('La contraseña actual es incorrecta.'); window.history.back();</script>";
                    exit;
                }

                $usuarioModelo->cambiarClave($id, $clave_nueva);
            }

            echo "<script>alert('Perfil actualizado correctamente'); window.location='" . RUTA_URL . "/usuarios/perfil';</script>";
        } else {
            echo "<script>alert('Error al actualizar el perfil'); window.history.back();</script>";
        }
    }

    public function roles()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'SuperU') {
            echo "<script>alert('Acceso denegado'); window.location='" . RUTA_URL . "/inicio';</script>";
            exit;
        }

        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();
        $usuarioModelo = new Usuario($conexion);

        $filtro = $_GET['filtro'] ?? '';
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 10;

        $usuarios = $usuarioModelo->listarUsuariosConFiltros($filtro, $pagina, $limite);
        $totalUsuarios = $usuarioModelo->contarUsuariosConFiltros($filtro);
        $totalPaginas = ceil($totalUsuarios / $limite);

        include BASE_PATH . '/aplicacion/vistas/usuarios/roles.php';
    }

    public function cambiarRol()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'SuperU') {
            echo "<script>alert('Acceso denegado'); window.location='" . RUTA_URL . "/inicio';</script>";
            exit;
        }

        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();
        $usuarioModelo = new Usuario($conexion);

        $id = $_POST['id'] ?? null;
        $rol = $_POST['rol'] ?? null;
        $clienteHasta = $_POST['cliente_hasta'] ?? null;

        if (!$id || !in_array($rol, ['usuario', 'cliente'])) {
            echo "<script>alert('Datos inválidos'); window.history.back();</script>";
            exit;
        }

        $clienteHasta = empty($clienteHasta) ? null : $clienteHasta;

        if ($usuarioModelo->actualizarRolYFecha($id, $rol, $clienteHasta)) {
            echo "<script>alert('Rol y fecha actualizados correctamente'); window.location='" . RUTA_URL . "/usuarios/roles';</script>";
        } else {
            echo "<script>alert('Error al actualizar los datos'); window.history.back();</script>";
        }
        // Si el usuario modificado es el mismo que el de la sesión, actualizamos su rol y cliente_hasta en la sesión


    }

    public function notificaciones()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario'])) {
            echo "<script>alert('Debes iniciar sesión'); window.location='" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();
        $usuarioModelo = new Usuario($conexion);

        $notificaciones = [];

        if ($_SESSION['usuario']['rol'] === 'SuperU') {
            $notificaciones = $usuarioModelo->obtenerClientesVencidos();
        } elseif ($_SESSION['usuario']['rol'] === 'cliente') {
            $notificaciones = ['cliente_hasta' => $_SESSION['usuario']['cliente_hasta']];
        }

        include BASE_PATH . '/aplicacion/vistas/usuarios/notificaciones.php';
    }

    public function configuracion()
    {
        require_once BASE_PATH . '/aplicacion/vistas/usuarios/configuracion.php';
    }

    public function actividad()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario'])) {
            echo "<script>window.location = '" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        require_once BASE_PATH . '/configuracion/BaseDatos.php';
        $conexion = BaseDatos::conectar();

        $tipo = $_SESSION['usuario']['tipo_usuario'] ?? 'usuarios';
        $usuarioId = $_SESSION['usuario']['id'];

        if ($tipo === 'usuarioscliente') {
            // Cargar sesiones desde la tabla sesiones_usuarioscliente
            require_once BASE_PATH . '/aplicacion/modelos/SesionUsuariosCliente.php';
            $modeloSesion = new \App\modelos\SesionUsuariosCliente($conexion);
        } else {
            // Cargar sesiones desde la tabla sesiones
            require_once BASE_PATH . '/aplicacion/modelos/Sesiones.php';
            $modeloSesion = new Sesiones($conexion);
        }

        $sesiones = $modeloSesion->obtenerSesionesUsuario($usuarioId);

        $this->vista('usuarios/actividad', ['sesiones' => $sesiones]);
    }



    public function guardarPreferencias()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario'])) {
            echo "<script>window.location='" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $correo_notificaciones = isset($_POST['correo_notificaciones']) ? 1 : 0;
        $alertas_vencimiento = isset($_POST['alertas_vencimiento']) ? 1 : 0;

        $id = $_SESSION['usuario']['id'];


        $usuarioModelo = new Usuario($this->conexion);

        $usuarioModelo->actualizarPreferencias($id, $correo_notificaciones, $alertas_vencimiento);

        // Actualizar sesión
        $_SESSION['usuario']['correo_notificaciones'] = $correo_notificaciones;
        $_SESSION['usuario']['alertas_vencimiento'] = $alertas_vencimiento;

        echo "<script>alert('Preferencias actualizadas'); window.location='" . RUTA_URL . "/usuarios/configuracion';</script>";
    }


    public function eliminar()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'SuperU') {
            echo "<script>alert('Acceso denegado'); window.location='" . RUTA_URL . "/inicio';</script>";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];

            require_once BASE_PATH . '/configuracion/BaseDatos.php';
            $conexion = BaseDatos::conectar();
            $usuarioModelo = new Usuario($conexion);

            if ($usuarioModelo->eliminarUsuarioPorId($id)) {
                echo "<script>alert('Usuario eliminado correctamente'); window.location='" . RUTA_URL . "/usuarios/roles';</script>";
            } else {
                echo "<script>alert('Error al eliminar usuario'); window.location='" . RUTA_URL . "/usuarios/roles';</script>";
            }
        } else {
            echo "<script>alert('Solicitud inválida'); window.location='" . RUTA_URL . "/usuarios/roles';</script>";
        }
    }
    public function cambiarModo()
    {
        Sesion::iniciarSesionLarga();
        if (!isset($_SESSION['usuario'])) {
            echo "<script>window.location='" . RUTA_URL . "/usuarios/login';</script>";
            exit;
        }

        $modo_oscuro = (isset($_POST['modo_oscuro']) && $_POST['modo_oscuro'] === '1') ? 1 : 0;

        $usuarioModelo = new Usuario($this->conexion);
        $usuarioModelo->actualizarModoOscuro($_SESSION['usuario']['id'], $modo_oscuro);

        $_SESSION['usuario']['modo_oscuro'] = $modo_oscuro;

        echo "<script>window.location='" . RUTA_URL . "/usuarios/configuracion';</script>";
    }
}
