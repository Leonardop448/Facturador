<?php require_once BASE_PATH . '/aplicacion/middleware/verificar_sesion.php'; ?>
<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container mt-4">
    <h3 class="text-primary">Notificaciones</h3>

    <?php

    use App\configuracion\BaseDatos;

    $rol = $_SESSION['usuario']['rol'];
    $clienteHasta = $_SESSION['usuario']['cliente_hasta'] ?? null;
    $clienteId = $_SESSION['usuario']['cliente_id'] ?? null;
    ?>

    <?php if ($rol === 'SuperU'): ?>
        <h5 class="mt-3">Clientes con suscripción vencida</h5>
        <table class="table table-bordered table-hover mt-2">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Fecha vencimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($notificaciones) > 0): ?>
                    <?php foreach ($notificaciones as $cliente): ?>
                        <tr>
                            <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                            <td><?= htmlspecialchars($cliente['correo']) ?></td>
                            <td><?= $cliente['cliente_hasta'] ?? 'No definida' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No hay clientes vencidos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif (in_array($rol, ['cliente', 'admin', 'usuario'])): ?>
        <?php
        // Si es admin, buscamos la fecha del cliente al que pertenece
        if ($rol === 'admin' && $clienteId) {
            $conexion = BaseDatos::conectar();
            $stmt = $conexion->prepare("SELECT cliente_hasta FROM usuarios WHERE id = :id AND rol = 'cliente'");
            $stmt->bindParam(':id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            $clienteHasta = $cliente['cliente_hasta'] ?? null;
        }
        ?>

        <div class="alert alert-info mt-3">
            <?php if (!empty($clienteHasta)): ?>
                <?php if ($clienteHasta >= date('Y-m-d')): ?>
                    <strong>Tu licencia de cliente está activa hasta:</strong><br>
                <?php else: ?>
                    <strong>Tu licencia de cliente venció el:</strong><br>
                <?php endif; ?>
                <?= $clienteHasta ?>
            <?php else: ?>
                <strong>No tienes una licencia activa.</strong>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>