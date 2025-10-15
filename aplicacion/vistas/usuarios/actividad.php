<?php require_once BASE_PATH . '/aplicacion/middleware/verificar_sesion.php'; ?>
<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Actividad de inicio de sesión</h3>

    <?php if (!empty($sesiones)): ?>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Dirección IP</th>
                    <th>Navegador</th>
                    <th>Ubicación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sesiones as $sesion): ?>
                    <tr>
                        <td><?= htmlspecialchars($sesion['fecha']) ?></td>
                        <td><?= htmlspecialchars($sesion['ip']) ?></td>
                        <td><?= htmlspecialchars($sesion['navegador']) ?></td>
                        <td><?= htmlspecialchars($sesion['ubicacion'] ?? 'Desconocida') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Aún no hay actividad registrada.</div>
    <?php endif; ?>
</div>