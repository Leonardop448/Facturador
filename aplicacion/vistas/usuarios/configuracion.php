<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<?php

use App\nucleo\Sesion;

if (session_status() === PHP_SESSION_NONE) Sesion::iniciarSesionLarga();

if (!isset($_SESSION['usuario'])) {
    echo "<script>alert('Debes iniciar sesión para acceder a esta página'); window.location = '" . RUTA_URL . "/usuarios/login';</script>";
    exit;
}

if (!isset($_SESSION['usuario']['correo_notificaciones'])) $_SESSION['usuario']['correo_notificaciones'] = 0;
if (!isset($_SESSION['usuario']['alertas_vencimiento'])) $_SESSION['usuario']['alertas_vencimiento'] = 0;

$rol = $_SESSION['usuario']['rol'] ?? 'usuario';
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-gear-fill me-2 text-primary"></i>Configuración del Sistema</h2>

    <!-- Seguridad y cuenta -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-shield-lock-fill me-2"></i> Seguridad y Cuenta
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                <li><a href="<?= RUTA_URL ?>/usuarios/perfil" class="text-decoration-none"><i class="bi bi-key me-2 text-secondary"></i>Actualizar Datos</a></li>
                <li><a href="<?= RUTA_URL ?>/usuarios/actividad" class="text-decoration-none"><i class="bi bi-clock-history me-2 text-secondary"></i>Ver actividad de inicio de sesión</a></li>
                <li><a href="#" class="text-decoration-none"><i class="bi bi-shield-check me-2 text-secondary"></i>Activar autenticación en dos pasos (2FA)</a></li>
            </ul>
        </div>
    </div>

    <!-- Preferencias de interfaz -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white fw-semibold">
            <i class="bi bi-palette-fill me-2"></i> Preferencias de Interfaz
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                <form action="<?= RUTA_URL ?>/usuarios/cambiarModo" method="POST" id="formModoOscuro">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="modo_oscuro" name="modo_oscuro"
                            value="1"
                            <?= (!empty($_SESSION['usuario']['modo_oscuro']) && $_SESSION['usuario']['modo_oscuro'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="modo_oscuro">Activar modo oscuro</label>
                    </div>
                </form>

                <script>
                    document.getElementById('modo_oscuro').addEventListener('change', function() {
                        const form = document.getElementById('formModoOscuro');
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'modo_oscuro';
                        hiddenInput.value = this.checked ? '1' : '0';
                        form.appendChild(hiddenInput);
                        localStorage.setItem('modo_oscuro', this.checked ? '1' : '0');
                        form.submit();
                    });
                </script>
            </ul>
        </div>
    </div>
    <?php if (in_array($rol, ['SuperU', 'cliente', 'admin'])): ?>
        <!-- Notificaciones -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <strong>Notificaciones</strong>
            </div>
            <div class="card-body">
                <form action="<?= RUTA_URL ?>/usuarios/guardarPreferencias" method="POST">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="correo_notificaciones" name="correo_notificaciones"
                            <?= ($_SESSION['usuario']['correo_notificaciones'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="correo_notificaciones">
                            Activar notificaciones por correo
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="alertas_vencimiento" name="alertas_vencimiento"
                            <?= ($_SESSION['usuario']['alertas_vencimiento'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="alertas_vencimiento">
                            Activar alertas por vencimiento de suscripción, pagos, etc..
                        </label>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary">Guardar cambios</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Preferencias de Facturación -->
    <?php if (in_array($rol, ['SuperU', 'cliente', 'admin'])): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white fw-semibold">
                <i class="bi bi-receipt-cutoff me-2"></i> Preferencias de Facturación
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li><a href="#" class="text-decoration-none"><i class="bi bi-file-earmark-text me-2 text-secondary"></i>Personalizar formato de factura</a></li>
                    <li><a href="#" class="text-decoration-none"><i class="bi bi-pencil-square me-2 text-secondary"></i>Editar textos legales</a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Roles Avanzados -->
    <?php if (in_array($rol, ['SuperU', 'cliente', 'admin'])): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark fw-semibold">
                <i class="bi bi-person-badge-fill me-2"></i> Roles Avanzados
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= RUTA_URL ?>/usuarioscliente/listar" class="text-decoration-none"><i class="bi bi-person-lines-fill me-2 text-secondary"></i>Gestionar usuarios internos</a></li>
                    <li><a href="#" class="text-decoration-none"><i class="bi bi-lock-fill me-2 text-secondary"></i>Permisos del sistema</a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Conectividad e Integraciones -->
    <?php if (in_array($rol, ['SuperU', 'cliente', 'admin'])): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-dark fw-semibold">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i> Conectividad e Integraciones
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li><a href="#" class="text-decoration-none"><i class="bi bi-envelope-at me-2 text-secondary"></i>Correo remitente predeterminado</a></li>
                    <li><a href="#" class="text-decoration-none"><i class="bi bi-code-slash me-2 text-secondary"></i>API Keys y Webhooks</a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <?php if (in_array($rol, ['SuperU', 'cliente'])): ?>
        <!-- Zona de peligro -->
        <div class="card mb-5 shadow-sm border border-danger">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Zona de Peligro
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li><a href="#" class="text-danger text-decoration-none fw-semibold"><i class="bi bi-trash me-2"></i>Eliminar cuenta</a></li>
                    <li><a href="#" class="text-danger text-decoration-none fw-semibold"><i class="bi bi-arrow-counterclockwise me-2"></i>Resetear configuración</a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>