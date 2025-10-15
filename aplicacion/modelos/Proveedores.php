<?php

namespace App\modelos;

use PDO;

class Proveedores
{
    private $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodosPorCliente(?int $clienteId): array
    {
        if ($clienteId === null) {
            // SuperU: traer todos con nombre del cliente
            $sql = "SELECT p.*, u.nombre AS cliente_nombre
                FROM proveedores p
                LEFT JOIN usuarios u ON p.cliente_id = u.id
                ORDER BY p.id DESC";
            return $this->conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT * FROM proveedores WHERE cliente_id = :cliente_id ORDER BY id DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function obtenerPorId(int $id, ?int $clienteId = null): ?array
    {
        $sql = "SELECT * FROM proveedores WHERE id = :id";
        $params = [':id' => $id];

        if ($clienteId !== null) {
            $sql .= " AND cliente_id = :cliente_id";
            $params[':cliente_id'] = $clienteId;
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

        return $proveedor ?: null;
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO proveedores (cliente_id, nombre, documento, telefono, email, ciudad, direccion)
                VALUES (:cliente_id, :nombre, :documento, :telefono, :email, :ciudad, :direccion)";

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':cliente_id' => $datos['cliente_id'],
            ':nombre' => $datos['nombre'],
            ':documento' => $datos['documento'],
            ':telefono' => $datos['telefono'],
            ':email' => $datos['email'],
            ':ciudad' => $datos['ciudad'],
            ':direccion' => $datos['direccion'],
        ]);
    }

    public function actualizar(int $id, array $datos, int $clienteId): bool
    {
        $sql = "UPDATE proveedores SET
            cliente_id = :cliente_id,
            nombre = :nombre,
            documento = :documento,
            telefono = :telefono,
            email = :email,
            ciudad = :ciudad,
            direccion = :direccion
        WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':cliente_id' => $clienteId,
            ':nombre' => $datos['nombre'],
            ':documento' => $datos['documento'],
            ':telefono' => $datos['telefono'],
            ':email' => $datos['email'],
            ':ciudad' => $datos['ciudad'],
            ':direccion' => $datos['direccion'],
            ':id' => $id
        ]);
    }

    public function eliminar(int $id, ?int $clienteId): bool
    {
        $sql = "DELETE FROM proveedores WHERE id = :id";
        $params = [':id' => $id];

        if ($clienteId !== null) {
            $sql .= " AND cliente_id = :cliente_id";
            $params[':cliente_id'] = $clienteId;
        }

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($params);
    }

    public function buscar(string $termino, ?int $clienteId): array
    {
        $sql = "SELECT * FROM proveedores WHERE (nombre LIKE :termino OR documento LIKE :termino)";
        $params = [':termino' => "%$termino%"];

        if ($clienteId !== null) {
            $sql .= " AND cliente_id = :cliente_id";
            $params[':cliente_id'] = $clienteId;
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeDocumento(string $documento, int $clienteId, int $excluirId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM proveedores 
                WHERE documento = :documento AND cliente_id = :cliente_id";

        if ($excluirId > 0) {
            $sql .= " AND id != :excluir_id";
        }

        $stmt = $this->conexion->prepare($sql);

        $params = [
            ':documento' => $documento,
            ':cliente_id' => $clienteId
        ];

        if ($excluirId > 0) {
            $params[':excluir_id'] = $excluirId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
