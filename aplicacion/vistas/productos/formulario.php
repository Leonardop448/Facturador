<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);
?>

<div class="container py-4">
    <h4 class="mb-4"><?= isset($producto) ? 'Editar Producto' : 'Registrar Producto' ?></h4>
    <p class="text-danger">Los campos con * son obligatorios</p>

    <form id="formProducto" action="<?= RUTA_URL ?>/productos/<?= isset($producto) ? 'actualizar' : 'guardar' ?>" method="post">
        <?php if (isset($producto)) : ?>
            <input type="hidden" name="id" value="<?= $producto['id'] ?>">
        <?php endif; ?>

        <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente *</label>
                <select name="cliente_id" id="cliente_id" class="form-select" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientesDisponibles as $cliente) : ?>
                        <option value="<?= $cliente['id'] ?>" <?= (isset($producto) && $producto['cliente_id'] == $cliente['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>


        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre *</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required value="<?= $producto['nombre'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input type="text" name="marca" id="marca" class="form-control" value="<?= $producto['marca'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="categoria" class="form-label">Categoría</label>
            <input type="text" name="categoria" id="categoria" class="form-control" value="<?= $producto['categoria'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="cantidad_en_stock" class="form-label">Cantidad en Stock *</label>
            <input type="number" name="cantidad_en_stock" id="cantidad_en_stock" class="form-control" required min="0" value="<?= $producto['cantidad_en_stock'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="ubicacion_almacen" class="form-label">Ubicación en Almacén</label>
            <input type="text" name="ubicacion_almacen" id="ubicacion_almacen" class="form-control" value="<?= $producto['ubicacion_almacen'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="precio_compra" class="form-label">Precio de Compra *</label>
            <input type="number" name="precio_compra" id="precio_compra" class="form-control" required min="0" value="<?= $producto['precio_compra'] ?? 0 ?>">
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="usarImpuesto">
            <label class="form-check-label" for="usarImpuesto">¿Impuesto aplicable?</label>
        </div>

        <div id="impuestoOpciones" style="display: none;">
            <div class="mb-3">
                <label for="impuesto_aplicable" class="form-label">Tipo de Impuesto *</label>
                <select name="impuesto_aplicable" id="impuesto_aplicable" class="form-select">
                    <option value="Sin impuesto">Sin impuesto</option>
                    <option value="IVA 19%">IVA 19%</option>
                    <option value="IVA 5%">IVA 5%</option>
                    <option value="Exento">Exento</option>
                    <option value="INC 8%">INC 8%</option>
                    <option value="INC 4%">INC 4%</option>
                    <option value="INC 16%">INC 16%</option>
                    <option value="ICA 0.2%">ICA 0.2%</option>
                    <option value="ICA 0.5%">ICA 0.5%</option>
                    <option value="ICA 1.4%">ICA 1.4%</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Precio de Compra con Impuesto:</label>
                <input type="text" class="form-control" id="precio_con_impuesto" readonly>
            </div>
        </div>

        <div class="mb-3">
            <label for="porcentaje_ganancia" class="form-label">Porcentaje de Ganancia (%)</label>
            <input type="number" name="porcentaje_ganancia" id="porcentaje_ganancia" class="form-control" value="<?= $producto['porcentaje_ganancia'] ?? 0 ?>" min="0">
        </div>

        <div class="mb-3">
            <label class="form-label">Precio de Venta (+ Impuesto si aplica)</label>
            <input type="number" id="precio_venta" name="precio_venta" class="form-control" value="<?= $producto['precio_venta'] ?? 0 ?>">
        </div>

        <div class="mb-3">
            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" value="<?= $producto['fecha_vencimiento'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="nombre_proveedor" class="form-label">Proveedor</label>
            <select name="nombre_proveedor" id="nombre_proveedor" class="form-select">
                <option value="Proveedor Generico" <?= (isset($producto) && $producto['nombre_proveedor'] === 'Proveedor Generico') ? 'selected' : '' ?>>
                    Proveedor Genérico
                </option>
                <?php if (isset($proveedoresDisponibles)) : ?>
                    <?php foreach ($proveedoresDisponibles as $prov) : ?>
                        <option value="<?= htmlspecialchars($prov['nombre']) ?>" <?= (isset($producto) && $producto['nombre_proveedor'] === $prov['nombre']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prov['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>



        <div class="mb-3">
            <label for="imagen_url" class="form-label">URL de Imagen</label>
            <input type="url" name="imagen_url" id="imagen_url" class="form-control" value="<?= $producto['imagen_url'] ?? '' ?>">
        </div>

        <div class="mb-3">
            <label for="notas" class="form-label">Notas</label>
            <textarea name="notas" id="notas" class="form-control" rows="3"><?= $producto['notas'] ?? '' ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-1"></i> Guardar
        </button>
        <a href="<?= RUTA_URL ?>/productos/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>




<script>
    const checkbox = document.getElementById('usarImpuesto');
    const impuestoDiv = document.getElementById('impuestoOpciones');
    const impuestoSelect = document.getElementById('impuesto_aplicable');
    const precioCompraInput = document.getElementById('precio_compra');
    const precioImpuesto = document.getElementById('precio_con_impuesto');
    const gananciaInput = document.getElementById('porcentaje_ganancia');
    const precioVentaInput = document.getElementById('precio_venta');

    const porcentajeImpuestos = {
        'IVA 19%': 19,
        'IVA 5%': 5,
        'Exento': 0,
        'Excluido': 0,
        'INC 8%': 8,
        'INC 4%': 4,
        'INC 16%': 16,
        'ICA 0.2%': 0.2,
        'ICA 0.5%': 0.5,
        'ICA 1.4%': 1.4,
        'Sin impuesto': 0
    };

    function getImpuesto() {
        return checkbox.checked ? (porcentajeImpuestos[impuestoSelect.value] || 0) : 0;
    }

    function calcularPrecioConImpuesto(precioCompra, impuesto) {
        return precioCompra + (precioCompra * impuesto / 100);
    }

    function calcularDesdePorcentajeGanancia() {
        const precioCompra = parseFloat(precioCompraInput.value) || 0;
        const impuesto = getImpuesto();
        const precioConImpuesto = calcularPrecioConImpuesto(precioCompra, impuesto);
        const ganancia = parseFloat(gananciaInput.value) || 0;
        const precioFinal = precioConImpuesto + (precioCompra * ganancia / 100);

        precioImpuesto.value = Math.round(precioConImpuesto);
        precioVentaInput.value = Math.round(precioFinal);
    }

    function calcularDesdePrecioVenta() {
        const precioCompra = parseFloat(precioCompraInput.value) || 0;
        const impuesto = getImpuesto();
        const precioConImpuesto = calcularPrecioConImpuesto(precioCompra, impuesto);
        const precioVenta = parseFloat(precioVentaInput.value) || 0;
        const ganancia = ((precioVenta - precioConImpuesto) / precioCompra) * 100;
        gananciaInput.value = Math.max(0, Math.round(ganancia));
        precioImpuesto.value = Math.round(precioConImpuesto);
    }

    checkbox.addEventListener('change', () => {
        impuestoDiv.style.display = checkbox.checked ? 'block' : 'none';
        calcularDesdePorcentajeGanancia();
    });

    impuestoSelect.addEventListener('change', calcularDesdePorcentajeGanancia);
    precioCompraInput.addEventListener('input', calcularDesdePorcentajeGanancia);
    gananciaInput.addEventListener('input', calcularDesdePorcentajeGanancia);
    precioVentaInput.addEventListener('input', calcularDesdePrecioVenta);

    <?php if (!empty($producto['impuesto_aplicable']) && $producto['impuesto_aplicable'] !== 'Sin impuesto') : ?>
        checkbox.checked = true;
        impuestoDiv.style.display = 'block';
        impuestoSelect.value = "<?= $producto['impuesto_aplicable'] ?>";
    <?php endif; ?>

    <?php if (!empty($producto['precio_venta']) && $producto['precio_venta'] > 0) : ?>
        calcularDesdePrecioVenta();
    <?php else : ?>
        calcularDesdePorcentajeGanancia();
    <?php endif; ?>

    <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
        document.getElementById('cliente_id').addEventListener('change', function() {
            const clienteId = this.value;

            const proveedorSelect = document.getElementById('nombre_proveedor');
            proveedorSelect.innerHTML = '<option value="Proveedor Genérico">Proveedor Genérico</option>';

            if (clienteId) {
                fetch(`<?= RUTA_URL ?>/productos/obtenerProveedoresPorCliente?cliente_id=${encodeURIComponent(clienteId)}`)

                    .then(response => response.json())
                    .then(data => {
                        data.forEach(prov => {
                            const option = document.createElement('option');
                            option.value = prov.nombre;
                            option.textContent = prov.nombre;
                            proveedorSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error al cargar proveedores:', error);
                    });
            }
        });
    <?php endif; ?>
</script>