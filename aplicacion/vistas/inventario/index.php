<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);
?>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #6C757D;">
            <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i> Inventario General</h4>
        </div>
        <div class="card-body">

            <!-- Totales -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3 shadow">
                        <div class="card-body">
                            <h5 class="card-title">Valor total (Costo):</h5>
                            <p class="card-text fs-4">
                                $
                                <?php
                                $valorCosto = 0;
                                foreach ($productos as $producto) {
                                    $cantidad = $producto['cantidad_en_stock'] ?? 0;
                                    $precioCompra = $producto['precio_compra'] ?? 0;
                                    $valorCosto += $cantidad * $precioCompra;
                                }
                                echo number_format($valorCosto, 0, ',', '.');
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3 shadow">
                        <div class="card-body">
                            <h5 class="card-title">Valor total (Venta):</h5>
                            <p class="card-text fs-4">
                                $
                                <?php
                                $valorVenta = 0;
                                foreach ($productos as $producto) {
                                    $cantidad = $producto['cantidad_en_stock'] ?? 0;
                                    $precioVenta = $producto['precio_venta'] ?? 0;
                                    $valorVenta += $cantidad * $precioVenta;
                                }
                                echo number_format($valorVenta, 0, ',', '.');
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-dark bg-warning mb-3 shadow">
                        <div class="card-body">
                            <h5 class="card-title">Ganancia estimada:</h5>
                            <p class="card-text fs-4">
                                $
                                <?php
                                $gananciaPotencial = $valorVenta - $valorCosto;
                                echo number_format($gananciaPotencial, 0, ',', '.');
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Botones de exportación -->
            <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                <form method="get" class="d-flex align-items-center mb-3" id="filtro-form">
                    <label for="cliente_id" class="form-label me-2 mb-0">Filtrar por cliente:</label>
                    <select name="cliente_id" id="cliente_id" class="form-select me-2" onchange="document.getElementById('filtro-form').submit();">
                        <option value="">Todos</option>
                        <?php foreach ($clientes as $cliente) : ?>
                            <option value="<?= $cliente['id'] ?>" <?= ($clienteSeleccionado == $cliente['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Botones con el filtro aplicado -->
                    <a href="<?= RUTA_URL ?>/inventario/imprimir?cliente_id=<?= urlencode($clienteSeleccionado ?? '') ?>" class="btn btn-secondary me-2" target="_blank">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                    <a href="<?= RUTA_URL ?>/inventario/exportarPdf?cliente_id=<?= urlencode($clienteSeleccionado ?? '') ?>" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i>PDF
                    </a>
                </form>
            <?php endif; ?>




            <!-- Tabla de productos -->
            <?php if (empty($productos)) : ?>
                <div class="alert alert-info">No se encontraron productos en el inventario.</div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>P. Compra</th>
                                <th>P. Venta</th>
                                <th>% Ganancia</th>
                                <th>Proveedor</th>
                                <th>Vencimiento</th>
                                <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                    <th>Cliente</th>
                                <?php endif; ?>

                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                    <td><?= htmlspecialchars($producto['marca'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($producto['categoria'] ?? '-') ?></td>
                                    <td><?= $producto['cantidad_en_stock'] ?></td>
                                    <td>$<?= number_format($producto['precio_compra'], 0, ',', '.') ?></td>
                                    <td>$<?= number_format($producto['precio_venta'], 0, ',', '.') ?></td>
                                    <td><?= (int)$producto['porcentaje_ganancia'] ?>%</td> <!-- Valor cargado -->
                                    <td><?= htmlspecialchars($producto['nombre_proveedor'] ?? '-') ?></td>
                                    <td><?= $producto['fecha_vencimiento'] ?? '-' ?></td>
                                    <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                        <td><?= htmlspecialchars($producto['cliente_nombre'] ?? '-') ?></td>
                                    <?php endif; ?>
                                    <td><?= $producto['fecha_ultima_actualizacion'] ?? '-' ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= RUTA_URL ?>/productos/editar/<?= $producto['id'] ?>" class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= RUTA_URL ?>/productos/eliminar/<?= $producto['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar producto?')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            <?php endif; ?>

            <!-- Botones finales -->
            <div class="text-end mt-4">
                <a href="<?= RUTA_URL ?>/productos/crear" class="btn btn-outline-secondary">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </a>
                <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

        </div>
    </div>
</div>