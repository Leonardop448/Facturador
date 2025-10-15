<?php

namespace App\controladores;

use App\nucleo\Sesion;
use App\nucleo\Controlador;
use App\modelos\Compra;
use App\modelos\Proveedores;
use App\modelos\Productos;
use App\modelos\Usuario;
use App\configuracion\BaseDatos;
use PDO;
use PDOException;

class ComprasControlador extends Controlador
{
    private $modeloCompra;
    private $modeloProveedores;
    private $modeloProductos;
    private $modeloUsuario;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            Sesion::iniciarSesionLarga();
        }

        $this->modeloCompra = new Compra(BaseDatos::conectar());
        $this->modeloProveedores = new Proveedores(BaseDatos::conectar());
        $this->modeloProductos = new Productos(BaseDatos::conectar());
        $this->modeloUsuario = new Usuario(BaseDatos::conectar());
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

        if ($clienteId === null) {
            $compras = $this->modeloCompra->obtenerTodas(); // Admin
        } else {
            $compras = $this->modeloCompra->obtenerTodas($clienteId);
        }

        require_once BASE_PATH . '/aplicacion/vistas/compras/index.php';
    }

    public function crear()
    {
        $usuario = $_SESSION['usuario'];
        $clientes = [];
        $proveedores = [];
        $productos = [];

        if ($usuario['rol'] === 'SuperU') {
            $clientes = $this->modeloUsuario->obtenerTodosLosClientes();

            if (isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id'])) {
                $clienteId = (int) $_GET['cliente_id'];
                $proveedores = $this->modeloProveedores->obtenerTodosPorCliente($clienteId);
                $productos = $this->modeloProductos->obtenerTodosPorCliente($clienteId);
            }
        } else {
            $clienteId = $this->obtenerClienteIdSesion();
            $proveedores = $this->modeloProveedores->obtenerTodosPorCliente($clienteId);
            $productos = $this->modeloProductos->obtenerTodosPorCliente($clienteId);
        }

        require_once BASE_PATH . '/aplicacion/vistas/compras/crear.php';
    }

    public function guardar()
    {
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['error' => 'Sesión no iniciada.']);
            return;
        }

        $conexion = BaseDatos::conectar();
        $usuario = $_SESSION['usuario'];
        $rol = $usuario['rol'];

        try {
            $conexion->beginTransaction();

            // 1) Determinar cliente_id y usuario_id según rol
            if ($rol === 'SuperU') {
                $clienteId = $_POST['cliente_id'] ?? null;
                $usuarioId = $clienteId; // el usuario_id es el cliente que seleccionó
            } elseif ($rol === 'cliente') {
                $clienteId = $usuario['id'];
                $usuarioId = $usuario['id'];
            } elseif ($rol === 'admin') {
                $clienteId = $usuario['cliente_id'];
                $usuarioId = $usuario['cliente_id'];
            } else {
                throw new \Exception("Rol no reconocido.");
            }

            if (empty($clienteId) || empty($usuarioId)) {
                throw new \Exception("No se pudo determinar el cliente o usuario.");
            }

            // 2) Proveedor: seleccionar existente o crear nuevo
            $proveedorId = $_POST['proveedor_id'] ?? null;
            $nuevoProv   = $_POST['nuevo_proveedor'] ?? [];

            if (empty($proveedorId) && !empty($nuevoProv['nombre'])) {
                // Crear nuevo proveedor
                $stmtProv = $conexion->prepare(
                    "INSERT INTO proveedores 
                (nombre, documento, telefono, direccion, ciudad, email, cliente_id)
             VALUES 
                (:nombre, :documento, :telefono, :direccion, :ciudad, :email, :cliente_id)"
                );
                $stmtProv->execute([
                    ':nombre'     => trim($nuevoProv['nombre']),
                    ':documento'  => trim($nuevoProv['documento'] ?? ''),
                    ':telefono'   => trim($nuevoProv['telefono'] ?? ''),
                    ':direccion'  => trim($nuevoProv['direccion'] ?? ''),
                    ':ciudad'     => trim($nuevoProv['ciudad'] ?? ''),
                    ':email'      => trim($nuevoProv['email'] ?? ''),
                    ':cliente_id' => $clienteId
                ]);
                $proveedorId = $conexion->lastInsertId();
            }

            if (empty($proveedorId)) {
                throw new \Exception("Debe seleccionar un proveedor o crear uno nuevo.");
            }

            // 3) Insertar compra
            $stmtCompra = $conexion->prepare(
                "INSERT INTO compras 
            (fecha_compra, total, proveedor_id, cliente_id, usuario_id)
         VALUES 
            (:fecha_compra, 0, :proveedor_id, :cliente_id, :usuario_id)"
            );
            $stmtCompra->execute([
                ':fecha_compra' => date('Y-m-d H:i:s'),
                ':proveedor_id' => $proveedorId,
                ':cliente_id'   => $clienteId,
                ':usuario_id'   => $usuarioId
            ]);
            $compraId = $conexion->lastInsertId();

            $totalCompra = 0;

            // 4) Procesar productos
            foreach ($_POST['productos'] as $producto) {
                $productoId        = (int) ($producto['producto_id'] ?? 0);
                $cantidad          = (int) ($producto['cantidad'] ?? 0);
                $precioCompraForm  = (int) ($producto['precio_compra'] ?? 0);
                $porcentajeGananc  = (int) ($producto['porcentaje_ganancia'] ?? 0);
                $precioVentaFormulario = isset($producto['precio_venta']) ? (float) $producto['precio_venta'] : 0;

                $fechaVenc         = $producto['fecha_vencimiento'] ?? null;
                $nombreProveedor   = $producto['nombre_proveedor'] ?? null;

                if ($productoId <= 0 || $cantidad <= 0) {
                    continue;
                }

                // Datos actuales del producto
                $stmtProd = $conexion->prepare(
                    "SELECT cantidad_en_stock, precio_compra, impuesto_aplicable 
                 FROM productos 
                 WHERE id = :id AND cliente_id = :cliente_id"
                );
                $stmtProd->execute([
                    ':id'         => $productoId,
                    ':cliente_id' => $clienteId
                ]);
                $prodActual = $stmtProd->fetch(PDO::FETCH_ASSOC);
                if (!$prodActual) {
                    continue;
                }

                $stockAnt   = (float) $prodActual['cantidad_en_stock'];
                $precioAnt  = (float) $prodActual['precio_compra'];
                $nuevoStock = $stockAnt + $cantidad;

                $nuevoPrecioCompra = $nuevoStock > 0
                    ? (($stockAnt * $precioAnt) + ($cantidad * $precioCompraForm)) / $nuevoStock
                    : $precioCompraForm;

                $precioVentaEstim = $nuevoPrecioCompra * (1 + $porcentajeGananc / 100);
                if (!empty($prodActual['impuesto_aplicable'])) {
                    preg_match('/(\d+)/', $prodActual['impuesto_aplicable'], $m);
                    $impVal = isset($m[1]) ? (int)$m[1] : 0;
                    $precioVentaEstim *= (1 + $impVal / 100);
                }
                // Usar el precio del formulario solo si es distinto al estimado (modificado manualmente)
                $precioVentaFinal = abs($precioVentaFormulario - $precioVentaEstim) > 0.01
                    ? $precioVentaFormulario
                    : $precioVentaEstim;


                // Obtener datos actuales del producto antes de actualizarlos
                $consultaProducto = $conexion->prepare("SELECT precio_compra, porcentaje_ganancia, precio_venta, impuesto_aplicable, fecha_vencimiento FROM productos WHERE id = ?");
                $consultaProducto->execute([$productoId]);
                $productoActual = $consultaProducto->fetch(PDO::FETCH_ASSOC);

                // Guardar respaldo
                $insertRespaldo = $conexion->prepare("
                        INSERT INTO respaldo_producto_compra (producto_id, compra_id, precio_compra_anterior, porcentaje_ganancia_anterior, precio_venta_anterior, impuesto_aplicable_anterior, fecha_vencimiento_anterior)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                $insertRespaldo->execute([
                    $productoId,
                    $compraId,
                    $productoActual['precio_compra'],
                    $productoActual['porcentaje_ganancia'],
                    $productoActual['precio_venta'],
                    $productoActual['impuesto_aplicable'],
                    $productoActual['fecha_vencimiento']
                ]);

                // Actualizar producto
                $stmtUpd = $conexion->prepare(
                    "UPDATE productos 
                     SET cantidad_en_stock = :stock, 
                         precio_compra      = :pc, 
                         precio_venta       = :pv, 
                         fecha_vencimiento  = :fv, 
                         porcentaje_ganancia= :pg,
                         nombre_proveedor   = :np,
                         fecha_ultima_actualizacion = NOW()
                     WHERE id = :id"
                );
                $stmtUpd->execute([
                    ':stock' => $nuevoStock,
                    ':pc' => round($nuevoPrecioCompra),
                    ':pv' => round($precioVentaFinal),
                    ':fv'    => $fechaVenc ?: null,
                    ':pg'    => $porcentajeGananc,
                    ':np'    => $nombreProveedor,
                    ':id'    => $productoId
                ]);

                // Insertar detalle
                $subtotal = $cantidad * $precioCompraForm;
                $stmtDet = $conexion->prepare(
                    "INSERT INTO detalle_compras 
                 (compra_id, producto_id, cantidad, precio_unitario, subtotal) 
                 VALUES 
                 (:compra, :prod, :cant, :pu, :sub)"
                );
                $stmtDet->execute([
                    ':compra' => $compraId,
                    ':prod'   => $productoId,
                    ':cant'   => $cantidad,
                    ':pu'     => $precioCompraForm,
                    ':sub'    => $subtotal
                ]);

                $totalCompra = ($_POST['total_general'] ?? 0);
            }

            // 5) Actualizar total
            $stmtUpdTot = $conexion->prepare(
                "UPDATE compras SET total = :tot WHERE id = :id"
            );
            $stmtUpdTot->execute([
                ':tot' => round($totalCompra, 0),
                ':id'  => $compraId
            ]);

            $conexion->commit();

            // Redireccionar después de guardar
            echo "<script>window.location.href = '/facturador/publico/compras/index';</script>";
            return;
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function extraerPorcentajeImpuesto(string $texto): int
    {
        if (preg_match('/(\d+)%/', $texto, $coincidencias)) {
            return (int) $coincidencias[1];
        }
        return 0;
    }




    public function eliminar($id = null)
    {
        if (!$id) {
            echo "ID no proporcionado.";
            return;
        }

        $compraModelo = new Compra(BaseDatos::conectar());

        try {
            $compraModelo->eliminar($id);
            echo "<script>window.location.href = '" . RUTA_URL . "/compras';</script>";
        } catch (PDOException $e) {
            echo "Error al eliminar la compra: " . $e->getMessage();
        }
    }


    public function ver($id)
    {
        if (!isset($_SESSION['usuario'])) {
            echo "Sesión no iniciada.";
            return;
        }

        $conexion = BaseDatos::conectar();

        // Obtener datos de la compra
        $stmtCompra = $conexion->prepare("
        SELECT compras.*, proveedores.nombre AS proveedor_nombre
        FROM compras
        JOIN proveedores ON compras.proveedor_id = proveedores.id
        WHERE compras.id = ?
    ");
        $stmtCompra->execute([$id]);
        $compra = $stmtCompra->fetch(PDO::FETCH_ASSOC);

        if (!$compra) {
            echo "Compra no encontrada.";
            return;
        }

        // Obtener detalles de la compra
        $stmtDetalles = $conexion->prepare("
        SELECT detalle_compras.*, productos.nombre AS producto_nombre
        FROM detalle_compras
        JOIN productos ON detalle_compras.producto_id = productos.id
        WHERE detalle_compras.compra_id = ?
    ");
        $stmtDetalles->execute([$id]);
        $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

        // Calcular subtotal sin impuestos y valor de impuestos
        $subtotalSinImpuestos = 0;
        $valorImpuestos = 0;

        foreach ($detalles as &$detalle) {
            // Obtener el impuesto_aplicable del producto
            $stmt = $conexion->prepare("SELECT impuesto_aplicable FROM productos WHERE id = ?");
            $stmt->execute([$detalle['producto_id']]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            // Extraer porcentaje numérico
            $porcentajeImpuesto = $this->extraerPorcentajeImpuesto($producto['impuesto_aplicable'] ?? '');

            $precioUnitario = (float)$detalle['precio_unitario'];
            $cantidad = (int)$detalle['cantidad'];

            $subtotalProducto = $precioUnitario * $cantidad;
            $impuestoProducto = $subtotalProducto * ($porcentajeImpuesto / 100);

            $subtotalSinImpuestos += $subtotalProducto;
            $valorImpuestos += $impuestoProducto;
        }

        // Cargar la vista
        require BASE_PATH . '/aplicacion/vistas/compras/ver.php';
    }



    public function imprimir($id)
    {
        if (!$id) {
            echo "ID no proporcionado.";
            return;
        }

        $conexion = BaseDatos::conectar();
        $modelo = new Compra($conexion);

        $compra = $modelo->obtenerPorId($id);
        $detalles = $modelo->obtenerDetalles($id);

        if (!$compra) {
            echo "Compra no encontrada.";
            return;
        }

        // Calcular subtotal sin impuestos y valor de impuestos
        $subtotalSinImpuestos = 0;
        $valorImpuestos = 0;

        foreach ($detalles as &$detalle) {
            // Obtener impuesto_aplicable desde productos
            $stmt = $conexion->prepare("SELECT impuesto_aplicable FROM productos WHERE id = ?");
            $stmt->execute([$detalle['producto_id']]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            // Extraer porcentaje del impuesto
            $porcentajeImpuesto = $this->extraerPorcentajeImpuesto($producto['impuesto_aplicable'] ?? '');

            $precioUnitario = (float)$detalle['precio_unitario'];
            $cantidad = (int)$detalle['cantidad'];

            $subtotalProducto = $precioUnitario * $cantidad;
            $impuestoProducto = $subtotalProducto * ($porcentajeImpuesto / 100);

            $subtotalSinImpuestos += $subtotalProducto;
            $valorImpuestos += $impuestoProducto;
        }

        include_once BASE_PATH . '/aplicacion/vistas/compras/imprimir.php';
    }


    public function productosPorCliente($clienteId): void
    {
        header('Content-Type: application/json');

        $conexion = BaseDatos::conectar();
        $modelo = new Productos($conexion);

        $productos = $modelo->obtenerTodosPorCliente((int)$clienteId);
        echo json_encode($productos);
    }

    public function proveedoresPorCliente($clienteId): void
    {
        header('Content-Type: application/json');

        $conexion = BaseDatos::conectar();
        $modelo = new Proveedores($conexion);

        $proveedores = $modelo->obtenerTodosPorCliente((int)$clienteId);
        echo json_encode($proveedores);
    }
}
