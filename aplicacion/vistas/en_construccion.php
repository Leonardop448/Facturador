<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<style>
    .cont-construccion {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 70vh;
        text-align: center;
        color: #333;
    }

    .icon-box {
        font-size: 5rem;
        color: #ffc107;
        /* Color naranja/amarillo de advertencia */
        margin-bottom: 1rem;
    }
</style>

<div class="container">
    <div class="cont-construccion">
        <div class="icon-box">
            <i class="bi bi-tools"></i>
        </div>
        <h1 class="fw-bold">¡Estamos trabajando en esto!</h1>
        <p class="fs-4">El módulo de <strong><?= $modulo ?? 'actual' ?></strong> se encuentra actualmente en desarrollo o la estamos mejorando.</p>
        <p class="text-muted">Estamos esforzándonos para brindarte la mejor herramienta. Vuelve pronto.</p>

        <div class="mt-4">
            <a href="<?= RUTA_URL ?>/inicio" class="btn btn-primary btn-lg rounded-pill px-4">
                <i class="bi bi-house-door"></i> Volver a Inicio
            </a>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/aplicacion/vistas/plantillas/pie.php'; ?>