<?php

namespace App\modelos;

use PDO;

class Clientes
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerTodosPorCliente(?int $clienteId): array
    {
        if ($clienteId === 0 || $clienteId === null) {
            // Superusuario: mostrar todos los clientes con el nombre del cliente dueño
            $sql = "SELECT c.*, u.nombre AS cliente_dueño
                FROM clientes c
                LEFT JOIN usuarios u ON c.cliente_id = u.id
                ORDER BY c.id DESC";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Cliente normal: solo sus propios clientes
        $sql = "SELECT * FROM clientes WHERE cliente_id = :cliente_id ORDER BY id DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function buscarPorNombreODocumento(string $q, ?int $clienteId): array
    {
        $sql = "SELECT * FROM clientes WHERE (nombre LIKE :q OR documento LIKE :q)";
        $params = [':q' => '%' . $q . '%'];

        if ($clienteId !== null) {
            $sql .= " AND id = :cliente_id";
            $params[':cliente_id'] = $clienteId;
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function obtenerPorId(int $id, ?int $clienteId): ?array
    {
        if (is_null($clienteId)) {
            $sql = "SELECT * FROM clientes WHERE id = :id";
            $stmt = $this->conexion->prepare($sql); // ← corregido aquí
            $stmt->execute([':id' => $id]);
        } else {
            $sql = "SELECT * FROM clientes WHERE id = :id AND cliente_id = :cliente_id";
            $stmt = $this->conexion->prepare($sql); // ← corregido aquí también
            $stmt->execute([':id' => $id, ':cliente_id' => $clienteId]);
        }

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }



    public function crear(array $data): bool
    {
        $sql = "INSERT INTO clientes (cliente_id, nombre, documento, correo, telefono, direccion, creado_en)
                VALUES (:cliente_id, :nombre, :documento, :correo, :telefono, :direccion, NOW())";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':cliente_id' => $data['cliente_id'],
            ':nombre' => $data['nombre'],
            ':documento' => $data['documento'],
            ':correo' => $data['correo'],
            ':telefono' => $data['telefono'],
            ':direccion' => $data['direccion']
        ]);
    }

    public function actualizar(int $id, array $data, int $clienteId): bool
    {
        $sql = "UPDATE clientes SET nombre = :nombre, documento = :documento, correo = :correo,
                telefono = :telefono, direccion = :direccion 
                WHERE id = :id AND cliente_id = :cliente_id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':documento' => $data['documento'],
            ':correo' => $data['correo'],
            ':telefono' => $data['telefono'],
            ':direccion' => $data['direccion'],
            ':id' => $id,
            ':cliente_id' => $clienteId
        ]);
    }

    public function eliminar(int $id, ?int $clienteId): bool
    {
        if (is_null($clienteId)) {
            // Superusuario puede eliminar sin restricción
            $sql = "DELETE FROM clientes WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } else {
            // Cliente o usuario debe tener permiso
            $sql = "DELETE FROM clientes WHERE id = :id AND cliente_id = :cliente_id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':cliente_id', $clienteId);
            return $stmt->execute();
        }
    }

    public function existeDocumentoParaCliente(string $documento, int $clienteId, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM clientes WHERE documento = :documento AND cliente_id = :cliente_id";

        if ($excluirId !== null) {
            $sql .= " AND id != :excluir_id";
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':cliente_id', $clienteId);
        if ($excluirId !== null) {
            $stmt->bindParam(':excluir_id', $excluirId);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
