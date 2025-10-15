<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/../../../'));
    require BASE_PATH . '/vendor/autoload.php';
}

use App\configuracion\BaseDatos;

$conexion = BaseDatos::conectar();

use App\modelos\Usuario;
use App\nucleo\Sesion;

if (session_status() === PHP_SESSION_NONE) {
    Sesion::iniciarSesionLarga();
}


$notificacionesPendientes = 0;
$mostrarMenu = false;

if (isset($_SESSION['usuario'])) {
    $rol = $_SESSION['usuario']['rol'];
    $clienteHasta = $_SESSION['usuario']['cliente_hasta'] ?? null;
    $clienteId = $_SESSION['usuario']['cliente_id'] ?? null;

    if ($rol === 'SuperU') {
        $mostrarMenu = true;
        $modeloUsuario = new Usuario($conexion);
        $notificacionesPendientes = $modeloUsuario->contarClientesVencidos();
    } elseif ($rol === 'cliente') {
        if ($clienteHasta && strtotime($clienteHasta) >= strtotime(date('Y-m-d'))) {
            $mostrarMenu = true;
            if ($clienteHasta <= date('Y-m-d', strtotime('+3 days'))) {
                $notificacionesPendientes = 1;
            }
        }
    } elseif ($rol === 'admin') {
        // Obtener datos del cliente al que pertenece
        if ($clienteId) {
            $stmt = $conexion->prepare("SELECT cliente_hasta FROM usuarios WHERE id = :id AND rol = 'cliente'");
            $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente && isset($cliente['cliente_hasta'])) {
                $clienteHastaAdmin = $cliente['cliente_hasta'];

                if (strtotime($clienteHastaAdmin) >= strtotime(date('Y-m-d'))) {
                    $mostrarMenu = true;

                    if ($clienteHastaAdmin <= date('Y-m-d', strtotime('+3 days'))) {
                        $notificacionesPendientes = 1;
                    }
                }
            }
        }
    } elseif ($rol === 'usuario') {
        if ($clienteHasta && strtotime($clienteHasta) <= strtotime(date('Y-m-d', strtotime('+3 days')))) {
            $notificacionesPendientes = 1;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Facturador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Comic+Relief:wght@400;700&family=Delius&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= RUTA_URL ?>/imagenes/icono.png">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-4VFG5S4BL7"></script>
    <script>
        // Opcional: Si quieres aplicar el modo oscuro inmediatamente desde localStorage
        if (localStorage.getItem('modo_oscuro') === '1') {
            document.documentElement.classList.add('modo-oscuro');
        }
    </script>
    <style>
        .modo-oscuro {
            background-color: #121212 !important;
            color: #f1f1f1 !important;
        }

        .modo-oscuro .card,
        .modo-oscuro .modal-content,
        .modo-oscuro .table,
        .modo-oscuro .form-control,
        .modo-oscuro .form-select,
        .modo-oscuro .dropdown-menu {
            background-color: #2b2b2b !important;
            color: #fff !important;
        }

        .modo-oscuro .table-light,
        .modo-oscuro .table th,
        .modo-oscuro .table td {
            background-color: #2b2b2b !important;
            border-color: #444 !important;
            color: #fff !important;
        }

        .modo-oscuro .bg-light {
            background-color: #1f1f1f !important;
            color: #fff !important;
        }

        .modo-oscuro a {
            color: #9ecfff;
        }
    </style>


    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-4VFG5S4BL7');
    </script>
</head>

<body class="<?= (!empty($_SESSION['usuario']['modo_oscuro']) && $_SESSION['usuario']['modo_oscuro'] == 1) ? 'modo-oscuro' : '' ?>">



    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="<?= isset($_SESSION['usuario']) ? RUTA_URL . '/modulos/inicio' : RUTA_URL . '/inicio' ?>">
            <img src="<?= RUTA_URL ?>/imagenes/logo.png" alt="Logo" width="90" class="rounded-4 me-2" style="object-fit: cover;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <?php if ($mostrarMenu): ?>
                <ul class="navbar-nav fw-semibold fs-6">
                    <!-- Menú de ventas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark px-3" href="#" data-bs-toggle="dropdown"><i class="bi bi-cash-coin text-danger me-1"></i> Ventas</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/clientes/index"><i class="bi bi-person-lines-fill text-success me-2"></i> Clientes</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/facturaPOS"><i class="bi bi-receipt text-warning me-2"></i> Factura POS</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/facturaElectronica"><i class="bi bi-file-earmark-check text-info me-2"></i> Factura Electrónica</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/creditos"><i class="bi bi-credit-card text-success me-2"></i> Créditos</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/pedidos"><i class="bi bi-truck text-warning me-2"></i> Pedidos</a></li>
                        </ul>
                    </li>

                    <!-- Inventario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark px-3" href="#" data-bs-toggle="dropdown"><i class="bi bi-box-seam text-warning me-1"></i> Inventario</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/productos/index"><i class="bi bi-box-seam text-primary me-2"></i> Productos</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/proveedores/index"><i class="bi bi-person-vcard text-danger me-2"></i>Proveedores</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/inventario/index"><i class="bi bi-clipboard-data text-secondary me-2"></i> Inventario</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/compras/index"><i class="bi bi-cart-check text-danger me-2"></i> Compras</a></li>
                        </ul>
                    </li>

                    <!-- Finanzas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark px-3" href="#" data-bs-toggle="dropdown"><i class="bi bi-graph-up-arrow text-primary me-1"></i> Finanzas</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/gastos"><i class="bi bi-cash-coin text-dark me-2"></i> Gastos</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/reportes"><i class="bi bi-graph-up-arrow text-primary me-2"></i> Reportes</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/contabilidad"><i class="bi bi-journal-text text-info me-2"></i> Contabilidad</a></li>
                        </ul>
                    </li>

                    <!-- Configuración -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark px-3" href="#" data-bs-toggle="dropdown"><i class="bi bi-gear text-success me-1"></i>Administración</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/empresa"><i class="bi bi-building me-2"></i> Datos de la empresa</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/resoluciones"><i class="bi bi-list-check me-2"></i> Resoluciones</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/parametros"><i class="bi bi-sliders2 me-2"></i> Parámetros</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/mediosPago"><i class="bi bi-credit-card me-2"></i> Medios de pago</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/categorias"><i class="bi bi-tags me-2"></i> Categorías</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/unidades"><i class="bi bi-rulers me-2"></i> Unidades</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/usuarioscliente/crear"><i class="bi bi-person-fill-gear me-2 text-primary"></i>Crear Usuarios</a></li>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/usuarioscliente/listar"><i class="bi bi-shield-lock me-2 text-primary"></i>Editar Usuarios</a></li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>

            <!-- Menú a la derecha -->
            <div class="ms-auto d-flex align-items-center flex-column flex-lg-row">

                <?php if (isset($_SESSION['usuario'])): ?>
                    <?php if (in_array($rol, ['SuperU', 'cliente', 'admin'])): ?>
                        <!-- 🔔 Notificaciones -->
                        <div class="dropdown me-3">
                            <a class="btn btn-light position-relative" href="#" id="dropdownNotificaciones" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell-fill text-danger position-relative ms-2" style="font-size: 1rem;">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.25em 0.4em;">
                                        <?= $notificacionesPendientes ?>
                                    </span>
                                </i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelleconexiony="dropdownNotificaciones" style="min-width: 300px;">
                                <li class="dropdown-header fw-semibold">Notificaciones</li>

                                <?php if ($_SESSION['usuario']['rol'] === 'SuperU' && $notificacionesPendientes > 0): ?>
                                    <li><a class="dropdown-item small" href="<?= RUTA_URL ?>/usuarios/notificaciones">
                                            <i class="bi bi-exclamation-triangle text-warning me-2"></i> Clientes con suscripción vencida
                                        </a></li>
                                <?php endif; ?>

                                <?php if (in_array($_SESSION['usuario']['rol'], ['cliente', 'admin', 'usuario']) && $notificacionesPendientes > 0): ?>
                                    <li><a class="dropdown-item small" href="<?= RUTA_URL ?>/usuarios/notificaciones">
                                            <i class="bi bi-clock-history text-info me-2"></i> Tu suscripción está por vencer o ya venció
                                        </a></li>
                                <?php endif; ?>


                                <?php if ($notificacionesPendientes === 0): ?>
                                    <li><span class="dropdown-item text-muted small">No hay notificaciones nuevas</span></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Usuario -->
                    <div class="dropdown">
                        <a class="btn btn-light dropdown-toggle fw-semibold" style="color: #0d6efd;" href="#" id="dropdownUsuario" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> <?= $_SESSION['usuario']['nombre'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelleconexiony="dropdownUsuario">
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/usuarios/perfil"><i class="bi bi-person me-2"></i> Mi perfil</a></li>
                            <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                <li><a class="dropdown-item" href="<?= RUTA_URL ?>/usuarios/roles"><i class="bi bi-lock me-2"></i> Cambiar Roles</a></li>
                            <?php endif; ?>
                            <?php if (in_array($rol, ['SuperU', 'cliente', 'admin'])): ?>
                                <li><a class="dropdown-item" href="<?= RUTA_URL ?>/usuarios/notificaciones"><i class="bi bi-bell me-2"></i> Notificaciones</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= RUTA_URL ?>/usuarios/configuracion"><i class="bi bi-gear me-2"></i> Configuración</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="<?= RUTA_URL ?>/usuarios/cerrarSesion"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- Si no hay sesión -->
                    <style>
                        .btn-outline-indigo {
                            color: #060635;
                            border: 1px solid #060635;
                            background-color: transparent;
                        }

                        .btn-outline-indigo:hover {
                            background-color: #060635;
                            color: #fff;
                        }
                    </style>
                    <div class="d-none d-lg-flex">
                        <a href="<?= RUTA_URL ?>/usuarios/login" class="btn btn-outline-indigo btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                        </a>
                        <a href="<?= RUTA_URL ?>/usuarios/registro" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-person-plus me-1"></i> Registrarse
                        </a>
                    </div>
                    <div class="d-lg-none w-100 mt-2 text-end">
                        <a href="<?= RUTA_URL ?>/usuarios/login" class="btn btn-outline-indigo btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                        </a>
                        <a href="<?= RUTA_URL ?>/usuarios/registro" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-person-plus me-1"></i> Registrarse
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>





    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>