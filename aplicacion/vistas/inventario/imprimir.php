<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);
?>

<script>
    window.onload = function() {
        window.print();
    }
</script>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<div class="container py-3">
    <div class="text-center mb-4">
        <h3>Informe de Inventario</h3>
        <p><?= date('Y-m-d H:i:s') ?></p>
    </div>

    <?php
    $valorCosto = 0;
    $valorVenta = 0;
    foreach ($productos as $producto) {
        $cantidad = $producto['cantidad_en_stock'] ?? 0;
        $precioCompra = $producto['precio_compra'] ?? 0;
        $precioVenta = $producto['precio_venta'] ?? 0;
        $valorCosto += $cantidad * $precioCompra;
        $valorVenta += $cantidad * $precioVenta;
    }
    $ganancia = $valorVenta - $valorCosto;
    ?>

    <div class="row text-center mb-4">
        <div class="col-4">
            <strong>Valor Total Costo:</strong><br>
            $<?= number_format($valorCosto, 0, ',', '.') ?>
        </div>
        <div class="col-4">
            <strong>Valor Total Venta:</strong><br>
            $<?= number_format($valorVenta, 0, ',', '.') ?>
        </div>
        <div class="col-4">
            <strong>Ganancia Estimada:</strong><br>
            $<?= number_format($ganancia, 0, ',', '.') ?>
        </div>
    </div>

    <?php if (empty($productos)) : ?>
        <div class="alert alert-info">No hay productos para mostrar.</div>
    <?php else : ?>
        <table class="table table-bordered table-sm align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Stock</th>
                    <th>Precio Compra</th>
                    <th>Precio Venta</th>
                    <th>% Ganancia</th>
                    <th>Proveedor</th>
                    <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                        <th>Cliente</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p) : ?>
                    <?php
                    $compra = $p['precio_compra'];
                    $venta = $p['precio_venta'];
                    $porcentaje = $p['porcentaje_ganancia'];;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= $p['cantidad_en_stock'] ?></td>
                        <td>$<?= number_format($compra, 0, ',', '.') ?></td>
                        <td>$<?= number_format($venta, 0, ',', '.') ?></td>
                        <td><?= $porcentaje ?>%</td>
                        <td><?= htmlspecialchars($p['nombre_proveedor'] ?? '-') ?></td>
                        <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                            <td><?= htmlspecialchars($p['cliente_nombre'] ?? '-') ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="no-print mt-3 text-end">
        <a href="<?= RUTA_URL ?>/inventario/index" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>