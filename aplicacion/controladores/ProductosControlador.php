<?php

namespace App\controladores;

use App\modelos\Productos;
use App\configuracion\BaseDatos;
use App\nucleo\Sesion;
use App\nucleo\Controlador;

require_once BASE_PATH . '/configuracion/BaseDatos.php';
require_once BASE_PATH . '/aplicacion/modelos/Productos.php';
require_once BASE_PATH . '/configuracion/config.php';

if (session_status() === PHP_SESSION_NONE) {
    Sesion::iniciarSesionLarga();
}

class ProductosControlador extends Controlador
{
    private $productosModelo;

    public function __construct()
    {
        $conexion = BaseDatos::conectar();
        $this->productosModelo = new Productos($conexion);
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
            return (int) $usuario['id'];
        }

        if ($usuario['tipo_usuario'] === 'usuarioscliente' && isset($usuario['cliente_id'])) {
            return (int) $usuario['cliente_id'];
        }

        echo "<script>alert('No se pudo identificar el cliente.');</script>";
        exit;
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteIdSesion();
        $productos = $this->productosModelo->obtenerTodosPorCliente($clienteId);
        require_once BASE_PATH . '/aplicacion/vistas/productos/index.php';
    }

    public function crear()
    {
        $usuario = $_SESSION['usuario'];
        $clientesDisponibles = [];
        $proveedoresDisponibles = [];

        $conexion = BaseDatos::conectar();
        require_once BASE_PATH . '/aplicacion/modelos/Proveedores.php';
        $proveedoresModelo = new \App\modelos\Proveedores($conexion);

        if ($usuario['rol'] === 'SuperU') {
            require_once BASE_PATH . '/aplicacion/modelos/Usuario.php';
            $usuariosModelo = new \App\modelos\Usuario($conexion);
            $clientesDisponibles = $usuariosModelo->obtenerTodosLosClientes();
            $proveedoresDisponibles = []; // ✅ No cargues ninguno al inicio
        } else {

            $clienteId = $this->obtenerClienteIdSesion();
            $proveedoresDisponibles = $proveedoresModelo->obtenerTodosPorCliente($clienteId);
        }

        require BASE_PATH . '/aplicacion/vistas/productos/formulario.php';
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_SESSION['usuario'];
            $clienteId = ($usuario['rol'] === 'SuperU') ? (int) $_POST['cliente_id'] : $this->obtenerClienteIdSesion();

            $datos = [
                'cliente_id' => $clienteId,
                'nombre' => trim($_POST['nombre']),
                'marca' => trim($_POST['marca'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'cantidad_en_stock' => (int) ($_POST['cantidad_en_stock'] ?? 0),
                'punto_recompra' => 0,
                'ubicacion_almacen' => trim($_POST['ubicacion_almacen'] ?? ''),
                'precio_compra' => (float) $_POST['precio_compra'],
                'precio_venta' => (float) $_POST['precio_venta'],
                'impuesto_aplicable' => trim($_POST['impuesto_aplicable'] ?? 'Tarifa General'),
                'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? null,
                'nombre_proveedor' => trim($_POST['nombre_proveedor'] ?? ''),
                'imagen_url' => trim($_POST['imagen_url'] ?? ''),
                'notas' => trim($_POST['notas'] ?? ''),
                'porcentaje_ganancia' => (int)($_POST['porcentaje_ganancia'] ?? 0),
            ];

            $this->productosModelo->crear($datos);

            echo "<script>window.location.href = '" . RUTA_URL . "/productos/index';</script>";
        }
    }

    public function editar($id)
    {
        $usuario = $_SESSION['usuario'];
        $clienteId = ($usuario['rol'] === 'SuperU') ? null : $this->obtenerClienteIdSesion();

        $producto = $this->productosModelo->obtenerPorId($id, $clienteId);

        if (!$producto) {
            echo "<script>alert('Producto no encontrado'); window.location.href = '" . RUTA_URL . "/productos/index';</script>";
            return;
        }

        $clientesDisponibles = [];
        $proveedoresDisponibles = [];

        // Determinar cliente_id real del producto
        $clienteIdReal = $producto['cliente_id'];

        require_once BASE_PATH . '/aplicacion/modelos/Proveedores.php';
        $proveedoresModelo = new \App\modelos\Proveedores(BaseDatos::conectar());
        $proveedoresDisponibles = $proveedoresModelo->obtenerTodosPorCliente($clienteIdReal);

        if ($usuario['rol'] === 'SuperU') {
            require_once BASE_PATH . '/aplicacion/modelos/Usuario.php';
            $usuariosModelo = new \App\modelos\Usuario(BaseDatos::conectar());
            $clientesDisponibles = $usuariosModelo->obtenerTodosLosClientes();
        }

        require BASE_PATH . '/aplicacion/vistas/productos/formulario.php';
    }


    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_SESSION['usuario'];
            $id = (int) $_POST['id'];
            $clienteId = ($usuario['rol'] === 'SuperU') ? (int) $_POST['cliente_id'] : $this->obtenerClienteIdSesion();

            $datos = [
                'nombre' => trim($_POST['nombre']),
                'marca' => trim($_POST['marca'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'cantidad_en_stock' => (int) ($_POST['cantidad_en_stock'] ?? 0),
                'ubicacion_almacen' => trim($_POST['ubicacion_almacen'] ?? ''),
                'precio_compra' => (float) $_POST['precio_compra'],
                'precio_venta' => (float) $_POST['precio_venta'],
                'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? null,
                'nombre_proveedor' => trim($_POST['nombre_proveedor'] ?? ''),
                'imagen_url' => trim($_POST['imagen_url'] ?? ''),
                'notas' => trim($_POST['notas'] ?? ''),
                'porcentaje_ganancia' => (int)($_POST['porcentaje_ganancia'] ?? 0),
                'impuesto_aplicable' => trim($_POST['impuesto_aplicable'] ?? 'Tarifa General')
            ];

            $this->productosModelo->actualizar($id, $datos, $clienteId);

            echo "<script>window.location.href = '" . RUTA_URL . "/productos/index';</script>";
        }
    }

    public function eliminar($id)
    {
        $usuario = $_SESSION['usuario'];

        if ($usuario['rol'] === 'vendedor') {
            echo "<script>alert('No tiene permisos para eliminar productos'); window.location.href = '" . RUTA_URL . "/productos/index';</script>";
            return;
        }

        $clienteId = $this->obtenerClienteIdSesion();
        $this->productosModelo->eliminar($id, $clienteId);

        echo "<script>window.location.href = '" . RUTA_URL . "/productos/index';</script>";
    }

    public function ver($id)
    {
        $clienteId = $this->obtenerClienteIdSesion();
        $producto = $this->productosModelo->obtenerPorId($id, $clienteId);
        require_once BASE_PATH . '/aplicacion/vistas/productos/ver.php';
    }

    public function buscar()
    {
        $query = $_GET['q'] ?? '';
        $usuario = $_SESSION['usuario'];

        $clienteId = null;

        if ($usuario['rol'] !== 'SuperU') {
            $clienteId = $this->obtenerClienteIdSesion();
        }

        $productos = $this->productosModelo->buscarPorNombre($query, $clienteId);

        require_once BASE_PATH . '/aplicacion/vistas/productos/index.php';
    }


    public function obtenerProveedoresPorCliente()
    {
        if (!isset($_GET['cliente_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta cliente_id']);
            return;
        }

        $clienteId = (int) $_GET['cliente_id'];

        require_once BASE_PATH . '/aplicacion/modelos/Proveedores.php';
        $proveedorModelo = new \App\modelos\Proveedores(BaseDatos::conectar());
        $proveedores = $proveedorModelo->obtenerTodosPorCliente($clienteId);

        header('Content-Type: application/json');
        echo json_encode($proveedores);
    }

    public function obtenerInfo($id)
    {
        $conexion = BaseDatos::conectar();
        $productoModelo = new Productos($conexion);
        $clienteId = $_SESSION['usuario']['cliente_id'] ?? null;

        $producto = $productoModelo->obtenerPorId($id, $clienteId);

        if ($producto) {
            echo json_encode([
                'impuesto_aplicable' => $producto['impuesto_aplicable'],
                'porcentaje_impuesto' => $producto['porcentaje_impuesto']
            ]);
        } else {
            echo json_encode(['error' => 'Producto no encontrado']);
        }
    }

    public function crearDesdeCompra()
    {
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        // Capturamos cualquier salida accidental
        ob_start();

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            ob_end_clean(); // limpiamos cualquier cosa enviada antes
            echo json_encode(['error' => 'No se pudo interpretar JSON']);
            exit;
        }

        if (empty($data['nombre'])) {
            ob_end_clean();
            echo json_encode(['error' => 'Faltan datos: nombre']);
            exit;
        }

        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'];

        if ($rol === 'SuperU') {
            $clienteId = $data['cliente_id'] ?? null;
        } else {
            $clienteId = $usuario['cliente_id'] ?? null;
        }

        if (!$clienteId) {
            ob_end_clean();
            echo json_encode(['error' => 'cliente_id no definido']);
            exit;
        }

        $conexion = BaseDatos::conectar();
        $modelo = new Productos($conexion);

        $nuevoID = $modelo->crear([
            'nombre' => $data['nombre'],
            'marca' => $data['marca'] ?? '',
            'categoria' => $data['categoria'] ?? '',
            'ubicacion_almacen' => $data['ubicacion_almacen'] ?? '',
            'impuesto_aplicable' => $data['impuesto_aplicable'] ?? '',
            'punto_recompra' => $data['punto_recompra'] ?? 0,
            'cliente_id' => $clienteId,
            'cantidad_en_stock' => 0,
            'precio_compra' => $data['precio_compra'] ?? 0,
            'precio_venta' => $data['precio_venta'] ?? 0,
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
            'nombre_proveedor' => $data['nombre_proveedor'] ?? '',
            'imagen_url' => $data['imagen_url'] ?? '',
            'notas' => $data['notas'] ?? '',
            'porcentaje_ganancia' => $data['porcentaje_ganancia'] ?? 0
        ]);

        if (!$nuevoID) {
            ob_end_clean();
            echo json_encode(['error' => 'No se pudo crear el producto']);
            exit;
        }

        $producto = $modelo->obtenerPorId($nuevoID, (int)$clienteId);

        if (!$producto) {
            ob_end_clean();
            echo json_encode(['error' => 'Producto creado pero no se pudo recuperar']);
            exit;
        }

        // Limpiamos cualquier salida accidental antes de enviar el JSON final
        ob_end_clean();
        echo json_encode($producto);
        exit;
    }
}
