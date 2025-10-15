<?php

namespace App\modelos;

use PDO;

class Sesiones
{
    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Registra una nueva sesión en la base de datos.
     */
    public function registrarSesion($usuario_id, $ip, $navegador)
    {
        $ubicacion = 'Desconocida';

        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,regionName,city");
            $data = json_decode($response, true);

            if ($data && isset($data['country'])) {
                $ubicacion = "{$data['city']}, {$data['regionName']}, {$data['country']}";
            }
        } catch (\Exception $e) {
            $ubicacion = 'Error localizando';
        }

        $sql = "INSERT INTO sesiones (usuario_id, ip, navegador, ubicacion) 
            VALUES (:usuario_id, :ip, :navegador, :ubicacion)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':ip' => $ip,
            ':navegador' => $navegador,
            ':ubicacion' => $ubicacion
        ]);
    }


    /**
     * Obtiene el historial de sesiones del usuario, ordenado por fecha descendente.
     */
    public function obtenerSesionesUsuario($usuarioId): array
    {
        $sql = "SELECT fecha, ip, navegador, ubicacion 
            FROM sesiones 
            WHERE usuario_id = :usuario_id 
            ORDER BY fecha DESC 
            LIMIT 5";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
