<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Factura POS #<?= htmlspecialchars($factura['id']) ?></h2>
        <a href="<?= RUTA_URL ?>/facturaPOS" class="btn btn-secondary">Volver</a>
    </div>

    <!-- Datos del cliente -->
    <div class="card mb-4">
        <div class="card-header">
            <strong>Cliente</strong>
        </div>
        <div class="card-body">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($factura['cliente_nombre']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($factura['cliente_email'] ?? 'No registrado') ?></p>
            <p><strong>Tipo de venta:</strong> <?= htmlspecialchars($factura['tipo_venta']) ?></p>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($factura['fecha']) ?></p>
        </div>
    </div>

    <!-- Productos -->
    <div class="card mb-4">
        <div class="card-header">
            <strong>Productos</strong>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factura['productos'] as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td class="text-center"><?= (int)$producto['cantidad'] ?></td>
                            <td class="text-end"><?= number_format($producto['precio'], 2) ?></td>
                            <td class="text-end"><?= number_format($producto['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totales -->
    <div class="card mb-4">
        <div class="card-body text-end">
            <p><strong>Subtotal:</strong> <?= number_format($factura['subtotal'], 2) ?></p>
            <p><strong>Impuestos:</strong> <?= number_format($factura['impuestos'], 2) ?></p>
            <p><strong>Descuento:</strong> <?= number_format($factura['descuento'], 2) ?></p>
            <h4><strong>Total:</strong> <?= number_format($factura['total'], 2) ?></h4>
        </div>
    </div>

    <!-- Botones -->
    <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-primary" onclick="imprimirFactura(<?= $factura['id'] ?>)">
            Imprimir
        </button>
        <button class="btn btn-success" onclick="abrirModalCorreo('<?= $factura['cliente_email'] ?>', <?= $factura['id'] ?>)">
            Enviar por correo
        </button>
    </div>
</div>

<!-- Modal para pedir correo si no existe -->
<div class="modal fade" id="modalCorreo" tabindex="-1" aria-labelledby="modalCorreoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEnviarCorreo">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCorreoLabel">Enviar factura por correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="factura_id" id="facturaIdCorreo">
                    <div class="mb-3">
                        <label for="correoCliente" class="form-label">Correo del cliente</label>
                        <input type="email" class="form-control" id="correoCliente" name="correo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Enviar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function imprimirFactura(id) {
        window.open('<?= RUTA_URL ?>/facturaPOS/imprimir/' + id, '_blank');
    }

    function abrirModalCorreo(correo, facturaId) {
        if (correo && correo !== '') {
            // Si ya existe email, enviamos directo
            enviarCorreo(facturaId, correo);
        } else {
            // Si no hay email → pedimos uno
            document.getElementById('facturaIdCorreo').value = facturaId;
            new bootstrap.Modal(document.getElementById('modalCorreo')).show();
        }
    }

    document.getElementById('formEnviarCorreo').addEventListener('submit', function(e) {
        e.preventDefault();
        const correo = document.getElementById('correoCliente').value;
        const facturaId = document.getElementById('facturaIdCorreo').value;
        enviarCorreo(facturaId, correo);
    });

    function enviarCorreo(facturaId, correo) {
        fetch('<?= RUTA_URL ?>/facturaPOS/enviarCorreo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    factura_id: facturaId,
                    correo: correo
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Factura enviada al correo ' + correo);
                    bootstrap.Modal.getInstance(document.getElementById('modalCorreo')).hide();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => {
                alert('Error al enviar correo');
                console.error(err);
            });
    }
</script>