<?php

namespace App\modelos;

use PDO;
use App\configuracion\BaseDatos;


class Productos
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodosPorCliente(?int $clienteId): array
    {
        if ($clienteId === null) {
            $sql = "SELECT 
                    p.id,
                    p.cliente_id,
                    p.nombre,
                    p.marca,
                    p.categoria,
                    p.cantidad_en_stock,
                    p.punto_recompra,
                    p.ubicacion_almacen,
                    p.precio_compra,
                    p.precio_venta,
                    p.impuesto_aplicable,
                    p.fecha_ultima_actualizacion,
                    p.fecha_vencimiento,
                    p.nombre_proveedor,
                    p.imagen_url,
                    p.notas,
                    p.porcentaje_ganancia,
                    u.nombre AS cliente_nombre
                FROM productos p
                LEFT JOIN usuarios u ON p.cliente_id = u.id
                ORDER BY p.id DESC";
            return $this->conexion->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        }

        $sql = "SELECT 
                id,
                cliente_id,
                nombre,
                marca,
                categoria,
                cantidad_en_stock,
                punto_recompra,
                ubicacion_almacen,
                precio_compra,
                precio_venta,
                impuesto_aplicable,
                fecha_ultima_actualizacion,
                fecha_vencimiento,
                nombre_proveedor,
                imagen_url,
                notas,
                porcentaje_ganancia
            FROM productos
            WHERE cliente_id = :cliente_id
            ORDER BY id DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    public function obtenerPorId(int $id, ?int $clienteId): ?array
    {
        $id = (int) $id;
        $clienteId = $clienteId !== null ? (int) $clienteId : null;

        if ($clienteId === null) {
            $sql = "SELECT p.*, u.nombre AS cliente_nombre
                FROM productos p
                LEFT JOIN usuarios u ON p.cliente_id = u.id
                WHERE p.id = :id";
            $params = [':id' => $id];
        } else {
            $sql = "SELECT p.*, u.nombre AS cliente_nombre
                FROM productos p
                LEFT JOIN usuarios u ON p.cliente_id = u.id
                WHERE p.id = :id AND p.cliente_id = :cliente_id";
            $params = [
                ':id' => $id,
                ':cliente_id' => $clienteId
            ];
        }


        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }




    public function crear(array $data): ?int
    {
        // Valores por defecto
        $data = array_merge([
            'marca' => '',
            'categoria' => '',
            'cantidad_en_stock' => 0,
            'punto_recompra' => 0,
            'ubicacion_almacen' => '',
            'precio_compra' => 0,
            'precio_venta' => 0,
            'impuesto_aplicable' => '',
            'fecha_vencimiento' => null,
            'nombre_proveedor' => '',
            'imagen_url' => '',
            'notas' => '',
            'porcentaje_ganancia' => 0,
        ], $data);

        $sql = "INSERT INTO productos (
        cliente_id, nombre, marca, categoria, cantidad_en_stock, punto_recompra,
        ubicacion_almacen, precio_compra, precio_venta, impuesto_aplicable,
        fecha_ultima_actualizacion, fecha_vencimiento, nombre_proveedor, imagen_url, notas,
        porcentaje_ganancia
    ) VALUES (
        :cliente_id, :nombre, :marca, :categoria, :cantidad_en_stock, :punto_recompra,
        :ubicacion_almacen, :precio_compra, :precio_venta, :impuesto_aplicable,
        NOW(), :fecha_vencimiento, :nombre_proveedor, :imagen_url, :notas,
        :porcentaje_ganancia
    )";

        $stmt = $this->conexion->prepare($sql);
        $exito = $stmt->execute([
            ':cliente_id' => $data['cliente_id'],
            ':nombre' => $data['nombre'],
            ':marca' => $data['marca'],
            ':categoria' => $data['categoria'],
            ':cantidad_en_stock' => $data['cantidad_en_stock'],
            ':punto_recompra' => $data['punto_recompra'],
            ':ubicacion_almacen' => $data['ubicacion_almacen'],
            ':precio_compra' => $data['precio_compra'],
            ':precio_venta' => $data['precio_venta'],
            ':impuesto_aplicable' => $data['impuesto_aplicable'],
            ':fecha_vencimiento' => $data['fecha_vencimiento'],
            ':nombre_proveedor' => $data['nombre_proveedor'],
            ':imagen_url' => $data['imagen_url'],
            ':notas' => $data['notas'],
            ':porcentaje_ganancia' => $data['porcentaje_ganancia'],
        ]);

        if ($exito) {
            return (int)$this->conexion->lastInsertId(); // ← retorna el ID real
        }

        return null;
    }




    public function actualizar(int $id, array $data, int $clienteId): bool
    {
        $sql = "UPDATE productos SET
                    nombre = :nombre,
                    marca = :marca,
                    categoria = :categoria,
                    cantidad_en_stock = :cantidad_en_stock,
                    ubicacion_almacen = :ubicacion_almacen,
                    precio_compra = :precio_compra,
                    precio_venta = :precio_venta,
                    impuesto_aplicable = :impuesto_aplicable,
                    fecha_vencimiento = :fecha_vencimiento,
                    nombre_proveedor = :nombre_proveedor,
                    imagen_url = :imagen_url,
                    notas = :notas,
                    porcentaje_ganancia = :porcentaje_ganancia,
                    fecha_ultima_actualizacion = NOW()
                WHERE id = :id AND cliente_id = :cliente_id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':marca' => $data['marca'],
            ':categoria' => $data['categoria'],
            ':cantidad_en_stock' => $data['cantidad_en_stock'],
            ':ubicacion_almacen' => $data['ubicacion_almacen'],
            ':precio_compra' => $data['precio_compra'],
            ':precio_venta' => $data['precio_venta'],
            ':impuesto_aplicable' => $data['impuesto_aplicable'],
            ':fecha_vencimiento' => $data['fecha_vencimiento'],
            ':nombre_proveedor' => $data['nombre_proveedor'],
            ':imagen_url' => $data['imagen_url'],
            ':notas' => $data['notas'],
            ':porcentaje_ganancia' => $data['porcentaje_ganancia'],
            ':id' => $id,
            ':cliente_id' => $clienteId
        ]);
    }

    public function eliminar(int $id, ?int $clienteId): bool
    {
        if ($clienteId === null) {
            $sql = "DELETE FROM productos WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
        } else {
            $sql = "DELETE FROM productos WHERE id = :id AND cliente_id = :cliente_id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':cliente_id', $clienteId);
        }

        return $stmt->execute();
    }

    public function buscarPorNombre(string $q, ?int $clienteId): array
    {
        if ($clienteId === null) {
            // SuperU: buscar en todos los productos
            $sql = "
        SELECT p.*, u.nombre AS cliente_nombre
        FROM productos p
        LEFT JOIN usuarios u ON p.cliente_id = u.id
        WHERE p.nombre LIKE :q
        ORDER BY p.id DESC
        ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':q' => "%$q%"
            ]);
        } else {
            // Clientes comunes: buscar solo en sus productos
            $sql = "
        SELECT p.*, u.nombre AS cliente_nombre
        FROM productos p
        LEFT JOIN usuarios u ON p.cliente_id = u.id
        WHERE p.nombre LIKE :q AND p.cliente_id = :cliente_id
        ORDER BY p.id DESC
        ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':q' => "%$q%",
                ':cliente_id' => $clienteId
            ]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function actualizarDatosCompraVenta($data)
    {
        $sql = "UPDATE productos SET 
                precio_compra = :precio_compra,
                precio_venta = :precio_venta,
                porcentaje_ganancia = :porcentaje_ganancia,
                fecha_vencimiento = :fecha_vencimiento
            WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':precio_compra' => $data['precio_compra'],
            ':precio_venta' => $data['precio_venta'],
            ':porcentaje_ganancia' => $data['porcentaje_ganancia'],
            ':fecha_vencimiento' => $data['fecha_vencimiento'],
            ':id' => $data['id']
        ]);
    }

    public static function obtenerPorcentajeImpuesto($impuestoAplicable)
    {
        if (preg_match('/(\d+)%/', $impuestoAplicable, $m)) {
            return (int) $m[1];
        }
        return 0;
    }
}