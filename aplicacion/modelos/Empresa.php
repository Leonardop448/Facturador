<?php

namespace App\modelos;

use PDO;

class Empresa
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function obtenerPorCliente($clienteId)
    {
        $sql = "SELECT * FROM empresas WHERE cliente_id = :cliente_id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardar(array $data)
    {
        $sql = "INSERT INTO empresas (nombre, nit, direccion, telefono, correo, logo, cliente_id)
                VALUES (:nombre, :nit, :direccion, :telefono, :correo, :logo, :cliente_id)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':nit' => $data['nit'],
            ':direccion' => $data['direccion'],
            ':telefono' => $data['telefono'],
            ':correo' => $data['correo'],
            ':logo' => $data['logo'],
            ':cliente_id' => $data['cliente_id']
        ]);
    }

    public function actualizar($id, array $data)
    {
        $sql = "UPDATE empresas SET 
                nombre = :nombre, 
                nit = :nit, 
                direccion = :direccion,
                telefono = :telefono, 
                correo = :correo, 
                logo = :logo 
            WHERE id = :id";

        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':nit' => $data['nit'],
            ':direccion' => $data['direccion'],
            ':telefono' => $data['telefono'],
            ':correo' => $data['correo'],
            ':logo' => $data['logo'],
            ':id' => $id
        ]);
    }

    public function existeNit(string $nit, int $clienteId): bool
    {
        $sql = "SELECT id FROM empresas WHERE nit = :nit AND cliente_id != :cliente_id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':nit' => $nit,
            ':cliente_id' => $clienteId
        ]);
        return $stmt->fetch() !== false;
    }
}
