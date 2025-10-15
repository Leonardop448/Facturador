<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2); // Solo SuperU, cliente, admin 
?>
<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-building me-2 text-primary"></i>Datos de la Empresa</h2>

    <form action="<?= RUTA_URL ?>/empresa/guardar" method="POST" enctype="multipart/form-data" class="card shadow-sm p-4">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la empresa</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($empresa['nombre'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nit" class="form-label">NIT</label>
                    <input type="text" class="form-control" id="nit" name="nit" value="<?= htmlspecialchars($empresa['nit'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?= htmlspecialchars($empresa['direccion'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?= htmlspecialchars($empresa['correo'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-4 text-center">
                <label for="logo" class="form-label d-block">Logo actual</label>
                <?php if (!empty($empresa['logo'])): ?>
                    <img src="<?= RUTA_URL ?>/<?= $empresa['logo'] ?>" class="img-fluid mb-3 border rounded" style="max-height: 150px;" alt="Logo de la empresa">
                <?php else: ?>
                    <div class="text-muted mb-3">Sin logo</div>
                <?php endif; ?>
                <input type="file" class="form-control" name="logo" id="logo" accept="image/*">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Guardar cambios</button>
        </div>
    </form>
</div>