<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-4">
    <h4 class="mb-3">Detalles del Producto</h4>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tr>
                    <th>Nombre</th>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                </tr>
                <tr>
                    <th>Marca</th>
                    <td><?= htmlspecialchars($producto['marca']) ?: '-' ?></td>
                </tr>
                <tr>
                    <th>Categoría</th>
                    <td><?= htmlspecialchars($producto['categoria']) ?: '-' ?></td>
                </tr>
                <tr>
                    <th>Stock</th>
                    <td><?= (int) $producto['cantidad_en_stock'] ?></td>
                </tr>
                <tr>
                    <th>Punto de Recompra</th>
                    <td><?= (int) $producto['punto_recompra'] ?></td>
                </tr>
                <tr>
                    <th>Ubicación Almacén</th>
                    <td><?= htmlspecialchars($producto['ubicacion_almacen']) ?: '-' ?></td>
                </tr>
                <?php if ($_SESSION['usuario']['rol'] !== 'vendedor') : ?>
                    <tr>
                        <th>Precio Compra</th>
                        <td>$<?= number_format($producto['precio_compra']) ?></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th>Precio Venta</th>
                    <td>$<?= number_format($producto['precio_venta'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <th>% Ganancia</th>
                    <td>$<?= number_format($producto['porcentaje_ganancia'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <th>Impuesto Aplicable</th>
                    <td><?= htmlspecialchars($producto['impuesto_aplicable']) ?: '-' ?></td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-bordered">
                <tr>
                    <th>Fecha Vencimiento</th>
                    <td><?= $producto['fecha_vencimiento'] ?: '-' ?></td>
                </tr>
                <tr>
                    <th>Proveedor</th>
                    <td><?= $producto['nombre_proveedor'] ?: '-' ?></td>
                </tr>
                <tr>
                    <th>Última Actualización</th>
                    <td><?= $producto['fecha_ultima_actualizacion'] ?></td>
                </tr>
                <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                    <tr>
                        <th>Cliente</th>
                        <td><?= htmlspecialchars($producto['cliente_nombre'] ?? '-') ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Notas</th>
                    <td><?= nl2br(htmlspecialchars($producto['notas'])) ?: '-' ?></td>
                </tr>
                <tr>
                    <th>Imagen</th>
                    <td>
                        <?php if (!empty($producto['imagen_url'])) : ?>
                            <img src="<?= $producto['imagen_url'] ?>" class="img-thumbnail" style="max-width: 200px;" alt="Producto">
                        <?php else : ?>
                            <span class="text-muted">Sin imagen</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= RUTA_URL ?>/productos" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>