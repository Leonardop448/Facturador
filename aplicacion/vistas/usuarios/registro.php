<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0">
                        <i class="bi bi-person-plus me-2"></i> Registro de Usuario
                    </h4>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= RUTA_URL ?>/usuarios/guardarRegistro">
                        <div class="mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento de identidad</label>
                            <input type="text" name="documento" class="form-control" required inputmode="numeric"
                                pattern="\d+" title="Solo números">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" required inputmode="numeric"
                                pattern="\d+" title="Solo números">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="clave" class="form-control" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Registrarse
                            </button>
                            <a href="<?= RUTA_URL ?>/usuarios/login" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Ya tengo cuenta
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/aplicacion/vistas/plantillas/pie.php'; ?>