<?php

namespace App\modelos;

use PDO;

class UsuariosCliente
{
    private $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Para rol SuperU: obtener todos los usuarios internos junto con el nombre del cliente
     */
    public function obtenerTodosConNombreCliente(): array
    {
        $sql = "SELECT uc.*, u.nombre AS nombre_cliente 
            FROM usuarioscliente uc 
            LEFT JOIN usuarios u ON uc.cliente_id = u.id 
            ORDER BY uc.nombre ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Para rol cliente: ver usuarios creados por sí mismo y por sus admins
     */
    public function obtenerUsuariosDeClienteYAdmins(int $cliente_id): array
    {
        $sql = "SELECT * FROM usuarioscliente 
                WHERE cliente_id = :cliente_id
                ORDER BY id DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Para rol admin: ver todos los usuarios internos de su cliente
     */
    public function obtenerUsuariosPorCliente(int $cliente_id): array
    {
        $sql = "SELECT * FROM usuarioscliente 
                WHERE cliente_id = :cliente_id
                ORDER BY id DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(int $cliente_id, string $nombre, string $documento, string $correo, string $clave, string $telefono, string $direccion, string $rol): bool
    {
        $sql = "INSERT INTO usuarioscliente (
                    cliente_id, nombre, documento_identidad, correo, clave, telefono, direccion, rol, estado, creado_en
                ) VALUES (
                    :cliente_id, :nombre, :documento, :correo, :clave, :telefono, :direccion, :rol, 'activo', NOW()
                )";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':rol', $rol);
        return $stmt->execute();
    }

    public function actualizar(int $id, int $cliente_id, string $nombre, string $correo, string $telefono, string $direccion, string $rol): bool
    {
        $sql = "UPDATE usuarioscliente 
                SET nombre = :nombre, correo = :correo, telefono = :telefono, 
                    direccion = :direccion, rol = :rol 
                WHERE id = :id AND cliente_id = :cliente_id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizarClave(int $id, int $cliente_id, string $clave): bool
    {
        $sql = "UPDATE usuarioscliente SET clave = :clave WHERE id = :id AND cliente_id = :cliente_id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':cliente_id', $cliente_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarPorId(int $id): bool
    {
        $sql = "DELETE FROM usuarioscliente WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function correoExiste(string $correo, int $cliente_id): bool
    {
        $sql = "SELECT id FROM usuarioscliente WHERE correo = :correo AND cliente_id = :cliente_id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT * FROM usuarioscliente WHERE id = :id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ?: null;
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        $sql = "SELECT * FROM usuarioscliente WHERE correo = :correo LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ?: null;
    }

    public function buscarPorNombreCorreoTelefono(string $query, ?int $clienteId): array
    {
        $sql = "SELECT uc.*, u.nombre AS nombre_cliente
            FROM usuarioscliente uc
            LEFT JOIN usuarios u ON uc.cliente_id = u.id
            WHERE (uc.nombre LIKE :query OR uc.correo LIKE :query OR uc.telefono LIKE :query)";

        if ($clienteId !== null) {
            $sql .= " AND uc.cliente_id = :cliente_id";
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%');

        if ($clienteId !== null) {
            $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
