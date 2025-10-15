<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);
?>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #DC3545;">
            <h4 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i> Detalle de Compra</h4>
        </div>
        <div class="card-body">

            <?php if (!empty($compra) && !empty($detalles)): ?>
                <div class="mb-4">
                    <p><strong>Fecha:</strong> <?= date('Y-m-d h:i A', strtotime($compra['fecha_compra'])) ?></p>
                    <p><strong>Proveedor:</strong> <?= htmlspecialchars($compra['proveedor_nombre']) ?></p>

                </div>

                <h5 class="mt-4 mb-3">Productos Comprados</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Compra</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['producto_nombre']) ?></td>
                                    <td><?= (int) $item['cantidad'] ?></td>
                                    <td>$<?= number_format($item['precio_unitario'], 0, ',', '.') ?></td>
                                    <td>$<?= number_format($item['precio_unitario'] * $item['cantidad'], 0, ',', '.') ?></td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <table class="table table-borderless w-50 ms-auto">
                        <tr>
                            <td class="text-end"><strong>Subtotal (sin impuestos):</strong></td>
                            <td class="text-end">$<?= number_format($subtotalSinImpuestos, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Impuestos:</strong></td>
                            <td class="text-end">$<?= number_format($valorImpuestos, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>$<?= number_format($compra['total'], 0, ',', '.') ?></strong></td>
                        </tr>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <a href="<?= RUTA_URL ?>/compras/imprimir/<?= $compra['id'] ?>" target="_blank" class="btn btn-secondary">
                        <i class="bi bi-printer"></i> Imprimir
                    </a>

                    <a href="<?= RUTA_URL ?>/compras/eliminar/<?= $compra['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar esta compra? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                    <a href="<?= RUTA_URL ?>/compras" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

            <?php else: ?>
                <div class="alert alert-warning">No se encontró información de la compra.</div>
            <?php endif; ?>

        </div>
    </div>
</div>