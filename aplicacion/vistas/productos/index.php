<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #0D6EFD;">
            <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i> Productos registrados</h4>
        </div>
        <div class="card-body">

            <!-- Buscador -->
            <form method="get" action="<?= RUTA_URL ?>/productos/buscar" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>

            <?php if (empty($productos)) : ?>
                <div class="alert alert-info">No se encontraron productos.</div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Stock</th>
                                <th>Precio Venta</th>
                                <th>Proveedor</th>
                                <th>Vencimiento</th>
                                <th>Última Actualización</th>
                                <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                                    <th>Cliente</th>
                                <?php endif; ?>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto) : ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($producto['nombre']) ?>
                                        <?php if (!empty($producto['imagen_url'])) : ?>
                                            <br>
                                            <img src="<?= $producto['imagen_url'] ?>" alt="Imagen" style="max-height: 40px;">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) $producto['cantidad_en_stock'] ?></td>
                                    <td>$<?= number_format($producto['precio_venta']) ?></td>
                                    <td><?= htmlspecialchars($producto['nombre_proveedor'] ?? '-') ?></td>
                                    <td><?= $producto['fecha_vencimiento'] ?? '-' ?></td>
                                    <td><?= $producto['fecha_ultima_actualizacion'] ?? '-' ?></td>
                                    <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                                        <td><?= htmlspecialchars($producto['cliente_nombre'] ?? '-') ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= RUTA_URL ?>/productos/ver/<?= $producto['id'] ?>" class="btn btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['usuario']['rol'] !== 'vendedor') : ?>
                                                <a href="<?= RUTA_URL ?>/productos/editar/<?= $producto['id'] ?>" class="btn btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= RUTA_URL ?>/productos/eliminar/<?= $producto['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar producto?')" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="text-end mt-4">
                <?php if ($_SESSION['usuario']['rol'] !== 'vendedor') : ?>
                    <a href="<?= RUTA_URL ?>/productos/crear" class="btn btn-outline-primary">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </a>
                <?php endif; ?>
                <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

        </div>
    </div>
</div>