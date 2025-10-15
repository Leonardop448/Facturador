<style>
    body {
        font-family: sans-serif;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 6px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }
</style>

<h2>Inventario General</h2>
<p><strong>Fecha:</strong> <?= date('Y-m-d H:i:s') ?></p>

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Cantidad</th>
            <th>Precio Costo</th>
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
            <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= (int)$p['cantidad_en_stock'] ?></td>
                <td>$<?= number_format($p['precio_compra'], 0, ',', '.') ?></td>
                <td>$<?= number_format($p['precio_venta'], 0, ',', '.') ?></td>
                <td><?= isset($p['porcentaje_ganancia']) ? htmlspecialchars($p['porcentaje_ganancia']) . '%' : '-' ?></td>
                <td><?= htmlspecialchars($p['nombre_proveedor'] ?? '-') ?></td>
                <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                    <td><?= htmlspecialchars($p['cliente_nombre'] ?? '-') ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p><strong>Valor Total Costo:</strong> $<?= number_format($valorCosto, 0, ',', '.') ?></p>
<p><strong>Valor Total Venta:</strong> $<?= number_format($valorVenta, 0, ',', '.') ?></p>
<p><strong>Ganancia Estimada:</strong> $<?= number_format($ganancia, 0, ',', '.') ?></p>