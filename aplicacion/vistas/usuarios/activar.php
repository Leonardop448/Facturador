<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<div class="container py-5 text-center">
    <?php if ($exito): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">¡Cuenta activada correctamente!</h4>
            <p>Ahora puedes iniciar sesión.</p>
            <a href="<?= RUTA_URL ?>/usuarios/login" class="btn btn-primary mt-3">Ir al login</a>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading">Token inválido o expirado</h4>
            <p>No se pudo activar tu cuenta.</p>
        </div>
    <?php endif; ?>
</div>