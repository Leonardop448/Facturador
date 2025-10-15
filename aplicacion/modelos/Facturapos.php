<?php

namespace App\modelos;

use App\configuracion\BaseDatos;
use App\nucleo\Controlador;
use PDO;

class Facturapos extends Controlador
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::conectar();
    }

    /**
     * Obtener todas las facturas según el rol del usuario
     */
    public function obtenerFacturas($rol, $usuario)
    {
        if ($rol === 'SuperU') {
            $sql = "SELECT f.*, c.nombre AS cliente, u.nombre AS usuario
                    FROM facturas_pos f
                    JOIN clientes c ON f.id_cliente = c.id
                    JOIN usuarios u ON f.usuario_id = u.id
                    ORDER BY f.fecha DESC";
            $stmt = $this->conexion->query($sql);
        } else {
            $sql = "SELECT f.*, c.nombre AS cliente_nombre
                    FROM facturas_pos f
                    JOIN clientes c ON f.id_cliente = c.id
                    WHERE f.usuario_id = :usuario_id
                    ORDER BY f.fecha DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':usuario_id' => $usuario['id']]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Obtener clientes disponibles según el rol
     * 
     * @param string $rol Rol del usuario (SuperU, cliente, admin, vendedor)
     * @param array $usuario Datos del usuario en sesión
     * @param int|null $usuarioId ID del usuario seleccionado por SuperU (opcional)
     * @return array Clientes filtrados
     */
    public function obtenerClientes($rol, $usuario, $usuarioSeleccionado = null)
    {
        $conexion = $this->conexion;

        if ($rol === 'SuperU') {
            if ($usuarioSeleccionado) {
                $sql = "SELECT * FROM clientes WHERE cliente_id = :usuario_id ORDER BY nombre ASC";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([':usuario_id' => $usuarioSeleccionado]);
            } else {
                return [];
            }
        } else {
            // Cliente/Admin/Vendedor: solo sus clientes
            $clienteIdSesion = $usuario['cliente_id'] ?? $usuario['id'];
            $sql = "SELECT * FROM clientes WHERE cliente_id = :cliente_id ORDER BY nombre ASC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':cliente_id' => $clienteIdSesion]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**
     * Obtener listado de productos
     */
    public function obtenerProductos($usuario, $usuarioSeleccionado = null)
    {
        $rol = $usuario['rol'];

        if ($rol === 'SuperU') {
            if ($usuarioSeleccionado) {
                // Para SuperU el cliente_id es directamente el id del usuario seleccionado
                $clienteId = $usuarioSeleccionado;

                $sql = "SELECT * FROM productos WHERE id = :cliente_id ORDER BY nombre ASC";
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute([':cliente_id' => $clienteId]);
            } else {
                // No seleccionó usuario → sin productos
                return [];
            }
        } else {
            // Para admin, cliente, vendedor → productos de su cliente_id
            $clienteId = $usuario['cliente_id'];
            $sql = "SELECT * FROM productos WHERE cliente_id = :cliente_id ORDER BY nombre ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':cliente_id' => $clienteId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Guardar factura y sus detalles
     */
    public function guardarFactura($datos, $usuario, $rol)
    {
        try {
            $this->conexion->beginTransaction();

            // Determinar usuario_id según rol
            $usuarioId = ($rol === 'SuperU' && isset($datos['usuario_id']))
                ? $datos['usuario_id']
                : $usuario['id'];

            // Insertar factura
            $sqlFactura = "INSERT INTO facturas_pos (id_cliente, usuario_id, total, fecha) 
                       VALUES (:cliente_id, :usuario_id, :total, NOW())";
            $stmt = $this->conexion->prepare($sqlFactura);
            $stmt->execute([
                ':cliente_id' => $datos['cliente_id'],
                ':usuario_id' => $usuarioId,
                ':total'      => $datos['total_general'] ?? 0   // << usar el campo correcto
            ]);
            $facturaId = $this->conexion->lastInsertId();

            // Insertar detalles en la tabla correcta
            if (!empty($datos['productos'])) {
                $sqlDetalle = "INSERT INTO detalle_factura_pos (factura_id, producto_id, cantidad, precio_unitario, subtotal) 
                           VALUES (:factura_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
                $stmtDetalle = $this->conexion->prepare($sqlDetalle);

                foreach ($datos['productos'] as $prod) {
                    $stmtDetalle->execute([
                        ':factura_id'      => $facturaId,
                        ':producto_id'     => $prod['producto_id'],   // << corregido
                        ':cantidad'        => $prod['cantidad'],
                        ':precio_unitario' => $prod['precio_venta'],  // << corregido
                        ':subtotal'        => $prod['subtotal']
                    ]);

                    // Actualizar stock del producto
                    $this->conexion->prepare("UPDATE productos SET cantidad_en_stock = cantidad_en_stock - :cantidad WHERE id = :id")
                        ->execute([
                            ':cantidad' => $prod['cantidad'],
                            ':id'       => $prod['producto_id']         // << corregido
                        ]);
                }
            }

            $this->conexion->commit();
            return $facturaId;
        } catch (\Exception $e) {
            $this->conexion->rollBack();
            throw new \Exception("Error al guardar la factura: " . $e->getMessage());
        }
    }


    /**
     * Obtener factura por id
     */
    public function obtenerFactura($id)
    {
        $sql = "SELECT f.*, c.nombre AS cliente_nombre, u.usuario AS usuario_nombre
                FROM facturas_pos f
                JOIN clientes c ON f.id_cliente = c.id
                JOIN usuarios u ON f.usuario_id = u.id
                WHERE f.id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener detalle de factura por id
     */
    public function obtenerDetalleFactura($id)
    {
        $sql = "SELECT d.*, p.nombre AS producto_nombre
                FROM facturas_pos_detalle d
                JOIN productos p ON d.producto_id = p.id
                WHERE d.factura_id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerUsuarios()
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT id, nombre, correo 
            FROM usuarios 
            WHERE estado = 'activo' 
              AND rol = 'cliente'";
        $stmt = $conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
