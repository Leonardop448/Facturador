<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2); // SuperU, cliente, admin
?>

<div class="container py-4">
    <h4><?= isset($proveedor) ? 'Editar Proveedor' : 'Registrar Proveedor' ?></h4>
    <p class="text-danger">Los campos con * son obligatorios</p>

    <form method="post" action="<?= RUTA_URL ?>/proveedores/<?= isset($proveedor) ? 'actualizar' : 'guardar' ?>">
        <?php if (isset($proveedor)) : ?>
            <input type="hidden" name="id" value="<?= $proveedor['id'] ?>">
        <?php endif; ?>

        <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente *</label>
                <select name="cliente_id" id="cliente_id" class="form-select" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientesDisponibles as $cliente) : ?>
                        <option value="<?= $cliente['id'] ?>" <?= (isset($proveedor) && $proveedor['cliente_id'] == $cliente['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>


        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required value="<?= $proveedor['nombre'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="documento" class="form-label">NIT *</label>
            <input type="text" name="documento" id="documento" class="form-control" required value="<?= $proveedor['documento'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" name="telefono" id="telefono" class="form-control" value="<?= $proveedor['telefono'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= $proveedor['email'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion"
                value="<?= isset($proveedor['direccion']) ? htmlspecialchars($proveedor['direccion']) : '' ?>">
        </div>

        <div class="mb-3">
            <label for="ciudad" class="form-label">Ciudad</label>
            <input type="text" name="ciudad" id="ciudad" class="form-control" value="<?= $proveedor['ciudad'] ?? '' ?>">
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-1"></i> Guardar
        </button>
        <a href="<?= RUTA_URL ?>/proveedores/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>