<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);
?>


<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #DC3545;">
            <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Compras registradas</h4>
        </div>
        <div class="card-body">


            <form method="get" action="<?= RUTA_URL ?>/compras/buscar" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por proveedor o fecha..." value="<?= $_GET['q'] ?? '' ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>

            <?php if (!empty($compras)) : ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                    <th>Cliente</th>
                                <?php endif; ?>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $compra): ?>
                                <tr>
                                    <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                        <td><?= htmlspecialchars($compra['usuario_nombre'] ?? 'Cliente no disponible') ?></td>
                                    <?php endif; ?>
                                    <td><?= isset($compra['fecha_compra']) ? date('Y-m-d h:i A', strtotime($compra['fecha_compra'])) : 'Fecha no disponible' ?></td>
                                    <td><?= htmlspecialchars($compra['proveedor_nombre']) ?></td>
                                    <td>$<?= number_format($compra['total'], 0, ',', '.') ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= RUTA_URL ?>/compras/ver/<?= $compra['id'] ?>" class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= RUTA_URL ?>/compras/eliminar/<?= $compra['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar esta compra? Esta acción no se puede deshacer.');" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No se han registrado compras aún.</div>
            <?php endif; ?>

            <div class="text-end mt-4">
                <a href="<?= RUTA_URL ?>/compras/crear" class="btn btn-outline-danger">
                    <i class="fas fa-plus"></i> Nueva Compra
                </a>
                <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

        </div>
    </div>
</div>