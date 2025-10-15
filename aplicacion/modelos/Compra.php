<?php

namespace App\modelos;

use PDO;
use PDOException;

class Compra
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }



    public function obtenerTodas($clienteId = null)
    {
        $sql = "SELECT c.*, 
                       p.nombre AS proveedor_nombre, 
                       u.nombre AS usuario_nombre 
                FROM compras c
                JOIN proveedores p ON c.proveedor_id = p.id
                JOIN usuarios u ON c.usuario_id = u.id";

        $params = [];

        if ($clienteId !== null) {
            $sql .= " WHERE c.cliente_id = ?";
            $params[] = $clienteId;
        }

        $sql .= " ORDER BY c.id DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->conexion->prepare("
            SELECT c.*, 
                   p.nombre AS proveedor_nombre, 
                   u.nombre AS usuario_nombre 
            FROM compras c
            JOIN proveedores p ON c.proveedor_id = p.id
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalles($compraId)
    {
        $stmt = $this->conexion->prepare("
            SELECT d.*, pr.nombre AS producto_nombre 
            FROM detalle_compras d
            JOIN productos pr ON d.producto_id = pr.id
            WHERE d.compra_id = ?
        ");
        $stmt->execute([$compraId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id)
    {
        try {
            $this->conexion->beginTransaction();

            // Obtener detalles de la compra para ajustar el stock
            $stmtDetalles = $this->conexion->prepare("SELECT producto_id, cantidad FROM detalle_compras WHERE compra_id = ?");
            $stmtDetalles->execute([$id]);
            $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

            // Restar del stock
            $stmtActualizarStock = $this->conexion->prepare("UPDATE productos SET cantidad_en_stock = cantidad_en_stock - ? WHERE id = ?");
            foreach ($detalles as $detalle) {
                $productoId = $detalle['producto_id'];
                $stmtActualizarStock->execute([
                    $detalle['cantidad'],
                    $productoId
                ]);

                // Obtener respaldo del producto
                $stmtRespaldo = $this->conexion->prepare("SELECT * FROM respaldo_producto_compra WHERE producto_id = ? AND compra_id = ?");
                $stmtRespaldo->execute([$productoId, $id]);
                $respaldo = $stmtRespaldo->fetch(PDO::FETCH_ASSOC);

                if ($respaldo) {
                    // Restaurar valores anteriores del producto
                    $stmtRestaurar = $this->conexion->prepare("
                    UPDATE productos SET
                        precio_compra = ?,
                        porcentaje_ganancia = ?,
                        precio_venta = ?,
                        impuesto_aplicable = ?,
                        fecha_vencimiento = ?
                    WHERE id = ?
                ");
                    $stmtRestaurar->execute([
                        $respaldo['precio_compra_anterior'],
                        $respaldo['porcentaje_ganancia_anterior'],
                        $respaldo['precio_venta_anterior'],
                        $respaldo['impuesto_aplicable_anterior'],
                        $respaldo['fecha_vencimiento_anterior'],
                        $productoId
                    ]);

                    // Borrar respaldo
                    $stmtBorrarRespaldo = $this->conexion->prepare("DELETE FROM respaldo_producto_compra WHERE id = ?");
                    $stmtBorrarRespaldo->execute([$respaldo['id']]);
                }
            }

            // Eliminar detalles primero
            $stmtBorrarDetalles = $this->conexion->prepare("DELETE FROM detalle_compras WHERE compra_id = ?");
            $stmtBorrarDetalles->execute([$id]);

            // Eliminar la compra
            $stmtBorrarCompra = $this->conexion->prepare("DELETE FROM compras WHERE id = ?");
            $stmtBorrarCompra->execute([$id]);

            $this->conexion->commit();
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }
}
