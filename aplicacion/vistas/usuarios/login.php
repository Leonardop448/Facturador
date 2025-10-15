<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-header text-white text-center" style="background-color: #060635;">
                    <h4 class="mb-0">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                    </h4>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= RUTA_URL ?>/usuarios/iniciarSesion">
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="correo" class="form-control" placeholder="correo@ejemplo.com"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="clave" class="form-control" placeholder="Tu contraseña"
                                required>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn text-white" style="background-color: #060635;">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                            </button>
                            <a href="<?= RUTA_URL ?>/usuarios/registro" class="btn " style="color: #198754; border-color: #198754;">
                                <i class="bi bi-person-plus me-1"></i> Registrarse
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>