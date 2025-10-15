<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<?php
$esEdicion = isset($cliente);
$accion = $esEdicion ? RUTA_URL . '/clientes/actualizar' : RUTA_URL . '/clientes/guardar';
$titulo = $esEdicion ? 'Editar Cliente' : 'Registrar Nuevo Cliente';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><?= $titulo ?></h4>
        <a href="<?= RUTA_URL ?>/clientes/index" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <form action="<?= $accion ?>" method="post" class="card card-body shadow-sm p-4">
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?= $cliente['id'] ?>">
        <?php endif; ?>

        <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'SuperU'): ?>
            <?php if ($esEdicion): ?>
                <!-- Campo oculto con el cliente_id existente -->
                <input type="hidden" name="cliente_id" value="<?= $cliente['cliente_id'] ?>">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cliente asignado</label>
                    <input type="text" class="form-control bg-light" value="ID: <?= $cliente['cliente_id'] ?>" readonly>
                </div>
            <?php elseif (isset($clientesDisponibles)): ?>
                <div class="col-md-6 mb-3">
                    <label for="cliente_id" class="form-label">Asignar al Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-select" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientesDisponibles as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required
                    value="<?= $esEdicion ? htmlspecialchars($cliente['nombre']) : '' ?>">
            </div>

            <div class="col-md-6">
                <label for="documento" class="form-label">Documento</label>
                <input type="text" name="documento" id="documento" class="form-control" required
                    value="<?= $esEdicion ? htmlspecialchars($cliente['documento']) : '' ?>">
            </div>

            <div class="col-md-6">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control"
                    value="<?= $esEdicion ? htmlspecialchars($cliente['correo']) : '' ?>">
            </div>

            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control"
                    value="<?= $esEdicion ? htmlspecialchars($cliente['telefono']) : '' ?>">
            </div>

            <div class="col-12">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" name="direccion" id="direccion" class="form-control"
                    value="<?= $esEdicion ? htmlspecialchars($cliente['direccion']) : '' ?>">
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Guardar
                </button>
            </div>
        </div>
    </form>
</div>