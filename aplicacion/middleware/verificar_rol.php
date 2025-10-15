<?php

if (!isset($_SESSION['usuario'])) {
    echo "<script>window.location = '" . RUTA_URL . "/usuarios/login';</script>";
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';
$tipo_usuario = $_SESSION['usuario']['tipo_usuario'] ?? 'usuarios'; // por si lo usas después

// Función que valida el nivel de acceso por rol
function verificarNivelAcceso(int $nivel): void
{
    $rol = $_SESSION['usuario']['rol'] ?? '';

    $niveles = [
        1 => ['SuperU'],
        2 => ['SuperU', 'cliente', 'admin'],
        3 => ['SuperU', 'cliente', 'admin', 'vendedor'],
    ];

    if (!in_array($rol, $niveles[$nivel] ?? [])) {
        echo "<script>alert('No tienes permisos para acceder a esta sección'); window.location = '" . RUTA_URL . "/inicio';</script>";
        exit;
    }
}
