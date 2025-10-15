<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #0D6EFD;">
            <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Crear Factura POS</h4>
        </div>
        <div class="card-body">
            <form id="form-factura-pos" method="post" action="<?= RUTA_URL ?>/FacturaPOS/guardar">
                <!-- Selección de usuario (solo SuperU) -->
                <?php if ($rol === 'SuperU'): ?>
                    <div class="mb-3">
                        <label for="usuario_id" class="form-label">Usuario</label>
                        <select name="usuario_id" id="usuario_id" class="form-control" required>
                            <option value="">Seleccione un usuario</option>
                            <?php foreach ($usuarios ?? [] as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['correo']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Selección de cliente -->
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente:</label>
                    <div class="input-group">
                        <select id="cliente_id" name="cliente_id" class="form-select" required>
                            <option value="">Seleccione un cliente</option>
                            <?php foreach ($clientes ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                            Nuevo Cliente
                        </button>
                    </div>
                </div>

                <!-- Tipo de venta -->
                <div class="mb-3">
                    <label for="tipo_venta" class="form-label">Tipo de venta:</label>
                    <select id="tipo_venta" name="tipo_venta" class="form-select" required>
                        <option value="contado">Contado</option>
                        <option value="credito">Crédito</option>
                        <option value="mixto">Mixto</option>
                    </select>
                </div>

                <!-- Tabla de productos -->
                <h5>Productos</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center" id="tabla-productos-pos">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Venta</th>
                                <th>Impuesto</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="detalle-factura">
                            <tr>
                                <td>
                                    <input list="productos-list" class="form-control producto-autocomplete" required>
                                    <input type="hidden" name="productos[0][producto_id]" class="input-producto-id">
                                    <datalist id="productos-list">
                                        <?php foreach ($productos ?? [] as $prod): ?>
                                            <option
                                                value="<?= htmlspecialchars($prod['nombre']) ?>"
                                                data-id="<?= $prod['id'] ?>"
                                                data-precioventa="<?= $prod['precio_venta'] ?>"
                                                data-impuesto="<?= $prod['impuesto_aplicable'] ?>">
                                            </option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </td>

                                <td>
                                    <input type="number" name="productos[0][cantidad]" class="form-control cantidad" min="1" value="1">
                                </td>
                                <td>
                                    <input type="number" name="productos[0][precio_venta]" class="form-control precio_venta" readonly>
                                </td>
                                <td>
                                    <span class="text-impuesto">0%</span>
                                    <!-- 🔹 Input oculto para guardar el valor del impuesto de la fila -->
                                    <input type="hidden" name="productos[0][impuesto_valor]" class="input-impuesto" value="0">
                                </td>
                                <td>
                                    <span class="subtotal-text">0</span>
                                    <input type="hidden" name="productos[0][subtotal]" class="input-subtotal" value="0">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm eliminar-producto">X</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary btn-sm" id="agregar-producto-pos">+ Agregar producto</button>
                </div>

                <!-- Totales -->
                <div class="mt-3 text-end">
                    <p><strong>Subtotal:</strong> $<span id="subtotal-general">0</span></p>
                    <p><strong>Impuestos:</strong> $<span id="impuesto-total">0</span></p>
                    <p>
                        <strong>Descuento %:</strong>
                        <input type="number" id="descuento" name="descuento" value="0" min="0" max="100">
                    </p>
                    <p><strong>Total:</strong> $<span id="total-general">0</span></p>
                    <input type="hidden" name="total" id="input-total-general" value="0">
                </div>



                <!-- Botones finales -->
                <div class="mb-3 text-end">
                    <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-success" id="btn-guardar">Guardar Venta</button>
                    <button type="button" class="btn btn-primary" id="btn-imprimir">Imprimir Factura</button>
                    <button type="button" class="btn btn-info" id="btn-enviar-correo">Enviar por Correo</button>
                </div>
            </form>
        </div>

    </div>


    <!-- Modal Nuevo Cliente -->
    <div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="form-nuevo-cliente">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre:</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email:</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento:</label>
                            <input type="text" class="form-control" name="documento">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Incluir JS externo -->
    <script src="<?= RUTA_URL ?>/js/facturaPOS.js"></script>