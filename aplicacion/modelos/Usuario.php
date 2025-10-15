<?php

namespace App\modelos;

use PDO;

class Usuario
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function existeCorreoODocumento(string $correo, string $documento): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE correo = ? OR documento_identidad = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$correo, $documento]);
        return $stmt->fetchColumn() > 0;
    }

    public function registrar(string $nombre, string $documento, string $correo, string $telefono, string $direccion, string $clave, string $rol, string $token): bool
    {
        $sql = "INSERT INTO usuarios (nombre, documento_identidad, correo, telefono, direccion, clave, rol, estado, token_activacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'inactivo', ?)";
        $stmt = $this->conexion->prepare($sql);
        $claveHash = password_hash($clave, PASSWORD_DEFAULT);
        return $stmt->execute([$nombre, $documento, $correo, $telefono, $direccion, $claveHash, $rol, $token]);
    }

    public function activarUsuario(string $token): bool
    {
        $sql = "UPDATE usuarios SET estado = 'activo', token_activacion = NULL WHERE token_activacion = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        $sql = "SELECT id, nombre, documento_identidad, correo, telefono, direccion, clave, rol, estado, cliente_hasta, correo_notificaciones, alertas_vencimiento
            FROM usuarios
            WHERE correo = :correo LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }


    public function validarLogin(string $correo, string $clave): array|false
    {
        $usuario = $this->buscarPorCorreo($correo);
        if ($usuario && password_verify($clave, $usuario['clave']) && $usuario['estado'] === 'activo') {
            return $usuario;
        }
        return false;
    }

    public function actualizarPerfil(int $id, string $nombre, string $telefono, string $direccion): bool
    {
        $sql = "UPDATE usuarios 
                SET nombre = ?, telefono = ?, direccion = ? 
                WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ucwords(strtolower($nombre)),
            $this->sanearTelefono($telefono),
            ucwords(strtolower($direccion)),
            $id
        ]);
    }

    private function sanearTelefono(string $telefono): string
    {
        return preg_replace('/\D/', '', $telefono);
    }

    public function verificarClave(int $id, string $clave_actual): bool
    {
        $stmt = $this->conexion->prepare("SELECT clave FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario && password_verify($clave_actual, $usuario['clave']);
    }

    public function cambiarClave(int $id, string $nueva_clave): bool
    {
        $claveHash = password_hash($nueva_clave, PASSWORD_DEFAULT);
        $stmt = $this->conexion->prepare("UPDATE usuarios SET clave = ? WHERE id = ?");
        return $stmt->execute([$claveHash, $id]);
    }

    public function listarUsuariosConFiltros(string $filtro = '', int $pagina = 1, int $porPagina = 10): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $sql = "SELECT id, nombre, correo, rol, cliente_hasta 
                FROM usuarios 
                WHERE rol IN ('usuario', 'cliente') 
                  AND (nombre LIKE :filtro OR correo LIKE :filtro)
                ORDER BY nombre ASC 
                LIMIT :offset, :porPagina";
        $stmt = $this->conexion->prepare($sql);
        $filtroParam = '%' . $filtro . '%';
        $stmt->bindValue(':filtro', $filtroParam, PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':porPagina', $porPagina, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarUsuariosConFiltros(string $filtro = ''): int
    {
        $sql = "SELECT COUNT(*) 
                FROM usuarios 
                WHERE rol IN ('usuario', 'cliente') 
                  AND (nombre LIKE :filtro OR correo LIKE :filtro)";
        $stmt = $this->conexion->prepare($sql);
        $filtroParam = '%' . $filtro . '%';
        $stmt->bindValue(':filtro', $filtroParam, PDO::PARAM_STR);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function cambiarRol(int $id, string $nuevoRol, ?string $clienteHasta = null): bool
    {
        $sql = "UPDATE usuarios SET rol = :rol, cliente_hasta = :cliente_hasta WHERE id = :id AND rol IN ('usuario', 'cliente')";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':rol' => $nuevoRol,
            ':cliente_hasta' => $clienteHasta,
            ':id' => $id
        ]);
    }

    public function actualizarRolYFecha(int $id, string $rol, ?string $clienteHasta): bool
    {
        $sql = "UPDATE usuarios SET rol = :rol, cliente_hasta = :cliente_hasta WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':rol' => $rol,
            ':cliente_hasta' => $clienteHasta,
            ':id' => $id
        ]);
    }

    public function obtenerClientesVencidos(): array
    {
        $hoy = date('Y-m-d');
        $sql = "SELECT nombre, correo, cliente_hasta 
                FROM usuarios 
                WHERE rol = 'cliente' 
                  AND (cliente_hasta IS NULL OR cliente_hasta < :hoy)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':hoy', $hoy, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarClientesVencidos(): int
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente' AND (cliente_hasta IS NULL OR cliente_hasta < CURDATE())";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function obtenerUsuariosConVencimientoProximo(int $dias = 3): array
    {
        $sql = "SELECT nombre, correo, cliente_hasta 
                FROM usuarios 
                WHERE rol = 'cliente' 
                  AND cliente_hasta IS NOT NULL 
                  AND cliente_hasta BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerClienteHasta(int $id): ?string
    {
        $sql = "SELECT cliente_hasta FROM usuarios WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() ?: null;
    }

    public function actualizarPreferencias($id, $correoNotif, $alertasVenc)
    {
        $sql = "UPDATE usuarios SET correo_notificaciones = :correo, alertas_vencimiento = :alertas WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':correo' => $correoNotif,
            ':alertas' => $alertasVenc,
            ':id' => $id
        ]);
    }

    public function eliminarUsuarioPorId(int $id): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function actualizarModoOscuro($id, $modo_oscuro)
    {
        $sql = "UPDATE usuarios SET modo_oscuro = :modo WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':modo' => $modo_oscuro, ':id' => $id]);
    }


    public function obtenerTodosLosClientes(): array
    {
        $sql = "SELECT id, nombre FROM usuarios WHERE rol = 'cliente'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
