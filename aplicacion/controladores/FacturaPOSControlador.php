<?php

namespace App\controladores;

use App\modelos\Facturapos;
use App\configuracion\BaseDatos;
use App\nucleo\Controlador;
use App\nucleo\Sesion;

class FacturaPOSControlador extends Controlador
{
    private $modelo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            Sesion::iniciarSesionLarga();
        }
        $this->modelo = new Facturapos();
    }

    public function index()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'];

        try {
            $facturas = $this->modelo->obtenerFacturas($rol, $usuario);
            require BASE_PATH . "/aplicacion/vistas/facturasPOS/crearFacturaPOS.php";
        } catch (\Exception $e) {
            echo "Error al cargar facturas: " . $e->getMessage();
        }
    }

    public function crear()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'];

        try {
            $clientes = [];
            $usuarios = [];
            $productos = [];

            if ($rol === 'SuperU') {
                // SuperU: solo mostramos el select de usuarios; clientes/productos se cargan vía fetch al cambiar usuario
                $usuarios = $this->modelo->obtenerUsuarios();
            } else {
                // cliente/admin/vendedor: precargar clientes y productos del cliente_id de la sesión
                $clientes  = $this->modelo->obtenerClientes($rol, $usuario);
                $productos = $this->modelo->obtenerProductos($usuario, null); // <-- IMPORTANTE
            }

            require BASE_PATH . "/aplicacion/vistas/facturasPOS/crearFacturaPOS.php";
        } catch (\Exception $e) {
            echo "Error al preparar formulario: " . $e->getMessage();
        }
    }



    public function guardar()
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        try {
            $facturaId = $this->modelo->guardarFactura($_POST, $_SESSION['usuario'], $_SESSION['usuario']['rol']);
            echo "<script>alert('Factura guardada correctamente.'); window.location='" . RUTA_URL . "/facturapos';</script>";
        } catch (\Exception $e) {
            echo "Error al guardar factura: " . $e->getMessage();
        }
    }

    public function ver($id)
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        try {
            $factura = $this->modelo->obtenerFactura($id);
            $detalle = $this->modelo->obtenerDetalleFactura($id);

            if (!$factura) {
                echo "Factura no encontrada.";
                return;
            }

            require BASE_PATH . "/aplicacion/vistas/facturasPOS/ver.php";
        } catch (\Exception $e) {
            echo "Error al obtener la factura: " . $e->getMessage();
        }
    }

    /**
     * AJAX: Obtener clientes según usuario seleccionado (solo SuperU)
     */
    public function obtenerClientesPorUsuario()
    {
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['error' => 'Sesión no iniciada']);
            return;
        }

        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'];

        if ($rol !== 'SuperU') {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $usuarioId = $_POST['usuario_id'] ?? null;

        if (!$usuarioId) {
            echo json_encode([]);
            return;
        }

        try {
            $clientes = $this->modelo->obtenerClientes('SuperU', $usuario, $usuarioId);
            echo json_encode($clientes);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }


    // 👉 Método que devuelve productos por usuario/cliente
    public function obtenerProductosPorUsuario()
    {
        if (!isset($_SESSION['usuario'])) {
            echo json_encode([]);
            return;
        }

        $conexion = BaseDatos::conectar();
        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'];

        // Recibimos el id enviado desde JS (solo si es SuperU)
        $usuarioSeleccionado = $_POST['id'] ?? null;

        try {
            if ($rol === 'SuperU') {
                if (!$usuarioSeleccionado) {
                    echo json_encode([]);
                    return;
                }

                // ⚠️ En tu diseño, el id del usuario actúa como cliente_id
                $clienteId = $usuarioSeleccionado;
            } else {
                // Para admin, cliente y vendedor usamos el cliente_id de la sesión
                $clienteId = $usuario['cliente_id'];
            }

            // Traer productos de ese cliente_id
            $sql = "SELECT id, nombre, precio_venta, impuesto_aplicable 
                FROM productos 
                WHERE cliente_id = :cliente_id
                ORDER BY nombre ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':cliente_id' => $clienteId]);
            $productos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode($productos);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
