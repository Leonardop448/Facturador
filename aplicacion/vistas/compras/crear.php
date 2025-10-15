<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Registrar Compra</h2>
        <a href="<?= RUTA_URL ?>/compras" class="btn btn-secondary">Volver</a>
    </div>

    <form method="post" action="<?= RUTA_URL ?>/compras/guardar">
        <?php if ($rol === 'SuperU'): ?>
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente:</label>
                <select name="cliente_id" id="cliente_id" class="form-select" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id'] ?>" <?= isset($_GET['cliente_id']) && $_GET['cliente_id'] == $cliente['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <?php
            // Determinar cliente_id según tipo de sesión
            if (isset($_SESSION['usuario']['cliente_id'])) {
                // Caso usuarioscliente
                $clienteId = $_SESSION['usuario']['cliente_id'];
            } else {
                // Caso usuarios
                $clienteId = $_SESSION['usuario']['id'];
            }
            ?>
            <input type="hidden" name="cliente_id" id="cliente_id" value="<?= $clienteId ?>">
        <?php endif; ?>





        <div class="mb-3">
            <label for="proveedor_id" class="form-label"><strong>Proveedor:</strong></label>
            <select name="proveedor_id" id="proveedor_id" class="form-select">
                <option value="" disabled selected>Seleccione un proveedor</option>
                <?php foreach ($proveedores ?? [] as $proveedor): ?>
                    <option value="<?= $proveedor['id'] ?>"><?= htmlspecialchars($proveedor['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="form-label mt-3"><strong>Crear un nuevo proveedor</strong></label>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="nuevo_proveedor[nombre]" class="form-control" placeholder="Nombre *">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" name="nuevo_proveedor[documento]" class="form-control" placeholder="Documento *">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" name="nuevo_proveedor[telefono]" class="form-control" placeholder="Teléfono *">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="nuevo_proveedor[direccion]" class="form-control" placeholder="Dirección (opcional)">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" name="nuevo_proveedor[ciudad]" class="form-control" placeholder="Ciudad (opcional)">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="email" name="nuevo_proveedor[email]" class="form-control" placeholder="Email (opcional)">
                </div>
            </div>

            <!-- Creacion de nuevo producto -->
            <label class="form-label mt-4"><strong>Crear un nuevo producto:</strong></label>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="nuevo_producto[nombre]" class="form-control" placeholder="Nombre *">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="text" name="nuevo_producto[marca]" class="form-control" placeholder="Marca (opcional)">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="nuevo_producto[categoria]" class="form-control" placeholder="Categoría (opcional)">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="nuevo_producto[ubicacion_almacen]" class="form-control" placeholder="Ubicación (opcional)">
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="hidden" name="nuevo_producto[cantidad_en_stock]" value="0">
                    <input type="number" name="nuevo_producto[punto_recompra]" class="form-control" placeholder="Notificacion de Recompra">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="nuevo_producto[impuesto_aplicable]" class="form-select">
                        <option value="">Sin impuesto</option>
                        <option value="IVA 19%">IVA 19%</option>
                        <option value="IVA 5%">IVA 5%</option>
                        <option value="Exento">Exento</option>
                        <option value="Excluido">INC 8%</option>
                        <option value="Excluido">INC 4%</option>
                        <option value="Excluido">INC 16%</option>
                        <option value="Excluido">ICA 0.2%</option>
                        <option value="Excluido">ICA 0.5%</option>
                        <option value="Excluido">ICA 1.4%</option>
                    </select>
                </div>
                <div class="d-inline-block mt-2">
                    <button type="button" id="crear-nuevo-producto" class="btn btn-outline-primary btn-sm">
                        Crear producto
                    </button>
                </div>
            </div>


        </div>


        <hr>
        <h5>Productos</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center" id="tabla-productos">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Compra</th>
                        <th>% Ganancia</th>
                        <th>Precio Venta (estimado)</th>
                        <th>Impuesto</th>
                        <th>Fecha Venc.</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="detalle-compra">
                    <tr>
                        <td>
                            <select name="productos[0][producto_id]" class="form-select producto-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($productos ?? [] as $prod): ?>
                                    <option value="<?= $prod['id'] ?>"
                                        data-impuesto="<?= $prod['impuesto_aplicable'] ?>"
                                        data-porcentaje="<?= $porcentaje ?>"
                                        data-ganancia="<?= $prod['porcentaje_ganancia'] ?>"
                                        data-precioventa="<?= $prod['precio_venta'] ?>"
                                        data-preciocompra="<?= $prod['precio_compra'] ?>"
                                        data-proveedor="<?= htmlspecialchars($producto['nombre_proveedor']) ?>">
                                        <?= htmlspecialchars($prod['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="productos[0][nombre_proveedor]" class="nombre-proveedor" value="">

                        </td>
                        <td><input type="number" name="productos[0][cantidad]" class="form-control cantidad" required min="1" value="1"></td>
                        <td><input type="number" name="productos[0][precio_compra]" class="form-control precio" required min="0" value="0"></td>
                        <td><input type="number" name="productos[0][porcentaje_ganancia]" class="form-control ganancia" value="0"></td>
                        <td><input type="number" name="productos[0][precio_venta]" class="form-control precio_venta" value="0"></td>
                        <td><span class="text-impuesto">0%</span></td>
                        <td><input type="date" name="productos[0][fecha_vencimiento]" class="form-control"></td>
                        <td>
                            <span class="subtotal-text">0</span>
                            <input type="hidden" name="productos[0][subtotal]" class="input-subtotal" value="0">
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm eliminar-producto">X</button></td>
                    </tr>

                </tbody>
            </table>



            <!-- Totales -->
            <div class="text-end pe-2">
                <p><strong>Subtotal:</strong> $<span id="subtotal-general">0</span></p>
                <p><strong>Impuesto total:</strong> $<span id="impuesto-total">0</span></p>
                <p><strong>Total general:</strong> $<span id="total-general">0</span></p>
                <input type="hidden" name="total_general" id="input-total-general" value="0">
            </div>

            <!-- Botón para agregar productos -->
            <div class="text-start">
                <button type="button" class="btn btn-primary btn-sm" id="agregar-producto">+ Agregar producto</button>
                <div class="text-end mb-3">
                    <button type="submit" class="btn btn-success">Guardar Compra</button>
                </div>
            </div>


    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = document.getElementById('tabla-productos');
            const btnAgregar = document.getElementById('agregar-producto');
            const proveedorSeleccionado = document.getElementById('proveedor_id');
            proveedorSeleccionado.addEventListener('change', function() {
                const nombre = this.options[this.selectedIndex].textContent;
                document.querySelectorAll('.nombre-proveedor').forEach(input => {
                    input.value = nombre;
                    input.readOnly = true;
                });
            });

            let contador = 1;

            function actualizarFila(fila, origen = null) {
                const precioCompraInput = fila.querySelector('.precio');
                const gananciaInput = fila.querySelector('.ganancia');
                const precioVentaInput = fila.querySelector('.precio_venta');
                const impuestoSpan = fila.querySelector('.text-impuesto');
                const cantidadInput = fila.querySelector('.cantidad');
                const subtotalText = fila.querySelector('.subtotal-text');
                const subtotalInput = fila.querySelector('.input-subtotal');

                const precioCompra = parseFloat(precioCompraInput.value) || 0;
                const ganancia = parseFloat(gananciaInput.value) || 0;
                const precioVenta = parseFloat(precioVentaInput.value) || 0;
                const cantidad = parseInt(cantidadInput.value) || 1;

                const productoSelect = fila.querySelector('.producto-select');
                const selectedOption = productoSelect.options[productoSelect.selectedIndex];
                const impuestoTexto = selectedOption.dataset.impuesto || "Sin impuesto";

                let impuestoPorcentaje = 0;
                if (impuestoTexto.includes('19')) impuestoPorcentaje = 19;
                else if (impuestoTexto.includes('5')) impuestoPorcentaje = 5;

                const impuestoValor = precioCompra * (impuestoPorcentaje / 100);
                let precioVentaConImpuesto;
                let nuevoGanancia;

                if (origen === 'precio_venta') {
                    // calcular % ganancia según precio de venta manual
                    // impuesto siempre se mantiene sobre precio de compra
                    nuevoGanancia = ((precioVenta - precioCompra - impuestoValor) / precioCompra) * 100;
                    gananciaInput.value = Math.round(nuevoGanancia);
                } else {
                    // calcular precio de venta según % ganancia
                    const gananciaValor = precioCompra * (ganancia / 100);
                    precioVentaConImpuesto = Math.round(precioCompra + gananciaValor + impuestoValor);
                    precioVentaInput.value = precioVentaConImpuesto;
                }

                impuestoSpan.textContent = `${impuestoPorcentaje}%`;

                const subtotal = precioCompra * cantidad;
                subtotalText.textContent = subtotal;
                subtotalInput.value = subtotal;

                actualizarTotales();
            }




            function actualizarTotales() {
                let subtotalGeneral = 0;
                let impuestoTotal = 0;

                document.querySelectorAll('#detalle-compra tr').forEach(fila => {
                    const precioCompra = parseFloat(fila.querySelector('.precio').value) || 0;
                    const ganancia = parseFloat(fila.querySelector('.ganancia').value) || 0;
                    const cantidad = parseInt(fila.querySelector('.cantidad').value) || 1;

                    const productoSelect = fila.querySelector('.producto-select');
                    const selectedOption = productoSelect.options[productoSelect.selectedIndex];
                    const impuestoTexto = selectedOption.dataset.impuesto || "Sin impuesto";

                    let impuestoPorcentaje = 0;
                    if (impuestoTexto.includes('19')) impuestoPorcentaje = 19;
                    else if (impuestoTexto.includes('5')) impuestoPorcentaje = 5;

                    const gananciaValor = precioCompra * (ganancia / 100);
                    const impuestoValorUnitario = precioCompra * (impuestoPorcentaje / 100);
                    const precioVentaConImpuesto = Math.round(precioCompra + gananciaValor + impuestoValorUnitario);
                    const subtotal = precioCompra * cantidad;
                    const impuestoValor = Math.round(impuestoValorUnitario * cantidad);

                    subtotalGeneral += subtotal;
                    impuestoTotal += impuestoValor;
                });

                document.getElementById("subtotal-general").textContent = subtotalGeneral;
                document.getElementById("impuesto-total").textContent = impuestoTotal;
                document.getElementById("total-general").textContent = subtotalGeneral + impuestoTotal;
                document.getElementById("input-total-general").value = subtotalGeneral + impuestoTotal;
            }

            // Eventos para fila inicial
            document.querySelectorAll('#detalle-compra tr').forEach(fila => {
                fila.querySelector('.producto-select').addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    fila.querySelector('.precio').value = selectedOption.dataset.preciocompra || 0;
                    fila.querySelector('.ganancia').value = selectedOption.dataset.ganancia || 0;
                    fila.querySelector('.precio_venta').value = selectedOption.dataset.precioventa || 0;
                    actualizarFila(fila);
                });

                fila.querySelectorAll('.precio, .ganancia, .cantidad').forEach(input => {
                    input.addEventListener('input', () => actualizarFila(fila));
                });

                fila.querySelector('.precio_venta').addEventListener('input', () => {
                    actualizarFila(fila, 'precio_venta');
                });


                fila.querySelector('.eliminar-producto').addEventListener('click', function() {
                    fila.remove();
                    actualizarTotales();
                });
            });

            // Evento agregar nueva fila
            btnAgregar.addEventListener('click', function() {
                const nuevaFila = document.querySelector('#detalle-compra tr').cloneNode(true);
                nuevaFila.querySelectorAll('input').forEach(input => input.value = input.classList.contains('cantidad') ? 1 : 0);
                nuevaFila.querySelector('.text-impuesto').textContent = '0%';
                nuevaFila.querySelector('.subtotal-text').textContent = '0';
                nuevaFila.querySelector('.input-subtotal').value = '0';

                // 👇 Agrega nombre del proveedor seleccionado
                const nombreProveedor = proveedorSeleccionado.options[proveedorSeleccionado.selectedIndex].textContent;
                nuevaFila.querySelector('.nombre-proveedor').value = nombreProveedor;
                nuevaFila.querySelector('.nombre-proveedor').readOnly = true;

                const idx = contador++;
                nuevaFila.querySelectorAll('input, select').forEach(el => {
                    if (el.name) el.name = el.name.replace(/\[\d+\]/, `[${idx}]`);
                });

                document.getElementById('detalle-compra').appendChild(nuevaFila);


                // Volver a asignar eventos a nueva fila
                nuevaFila.querySelector('.producto-select').addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    nuevaFila.querySelector('.precio').value = selectedOption.dataset.preciocompra || 0;
                    nuevaFila.querySelector('.ganancia').value = selectedOption.dataset.ganancia || 0;
                    nuevaFila.querySelector('.precio_venta').value = selectedOption.dataset.precioventa || 0;
                    actualizarFila(nuevaFila);
                });

                nuevaFila.querySelectorAll('.precio, .ganancia, .cantidad').forEach(input => {
                    input.addEventListener('input', () => actualizarFila(nuevaFila));
                });

                nuevaFila.querySelector('.precio_venta').addEventListener('input', () => {
                    actualizarFila(nuevaFila, 'precio_venta');
                });

                nuevaFila.querySelector('.eliminar-producto').addEventListener('click', function() {
                    nuevaFila.remove();
                    actualizarTotales();
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const clienteSelect = document.getElementById('cliente_id');
            const proveedorSelect = document.getElementById('proveedor_id');
            const productoSelects = document.querySelectorAll('.producto-select');

            if (clienteSelect) {
                clienteSelect.addEventListener('change', function() {
                    const clienteId = this.value;


                    // Limpiar selects
                    proveedorSelect.innerHTML = '<option value="1">Proveedor Genérico</option>';
                    document.querySelectorAll('.producto-select').forEach(sel => {
                        sel.innerHTML = '<option value="">Seleccione</option>';
                    });

                    if (!clienteId) return;

                    // Cargar proveedores
                    fetch(`${location.origin}/facturador/publico/compras/proveedoresPorCliente/${clienteId}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(p => {
                                const opt = document.createElement('option');
                                opt.value = p.id;
                                opt.textContent = p.nombre;
                                proveedorSelect.appendChild(opt);
                            });
                        });

                    // Cargar productos
                    fetch(`${location.origin}/facturador/publico/compras/productosPorCliente/${clienteId}`)
                        .then(res => res.json())
                        .then(data => {
                            document.querySelectorAll('.producto-select').forEach(select => {
                                select.innerHTML = '<option value="">Seleccione</option>';
                                data.forEach(prod => {
                                    const opt = document.createElement('option');
                                    opt.value = prod.id;
                                    opt.textContent = prod.nombre;
                                    opt.dataset.impuesto = prod.impuesto_aplicable;
                                    opt.dataset.porcentaje = prod.porcentaje_impuesto;
                                    opt.dataset.ganancia = prod.porcentaje_ganancia;
                                    opt.dataset.precioventa = prod.precio_venta;
                                    opt.dataset.preciocompra = prod.precio_compra;
                                    select.appendChild(opt);
                                });
                            });
                        });
                });
            }
        });
    </script>
    <script>
        document.getElementById('crear-nuevo-producto').addEventListener('click', function() {
            const datos = {
                nombre: document.querySelector('[name="nuevo_producto[nombre]"]').value,
                marca: document.querySelector('[name="nuevo_producto[marca]"]').value,
                categoria: document.querySelector('[name="nuevo_producto[categoria]"]').value,
                ubicacion_almacen: document.querySelector('[name="nuevo_producto[ubicacion_almacen]"]').value,
                impuesto_aplicable: document.querySelector('[name="nuevo_producto[impuesto_aplicable]"]').value,
                punto_recompra: document.querySelector('[name="nuevo_producto[punto_recompra]"]').value || 0,
                cantidad_en_stock: 0,
                precio_compra: parseFloat(document.querySelector('[name="nuevo_producto[precio_compra]"]')?.value || 0),
                precio_venta: parseFloat(document.querySelector('[name="nuevo_producto[precio_venta]"]')?.value || 0),
                porcentaje_ganancia: parseFloat(document.querySelector('[name="nuevo_producto[porcentaje_ganancia]"]')?.value || 0),
                fecha_vencimiento: document.querySelector('[name="nuevo_producto[fecha_vencimiento]"]')?.value || null,
                nombre_proveedor: document.querySelector('[name="nuevo_producto[nombre_proveedor]"]')?.value || '',
                imagen_url: '',
                notas: document.querySelector('[name="nuevo_producto[notas]"]')?.value || ''
            };

            // Determinar cliente_id según el rol
            <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                datos.cliente_id = document.querySelector('[name="cliente_id"]')?.value || '';
            <?php else: ?>
                datos.cliente_id = "<?= (int)($_SESSION['usuario']['cliente_id'] ?? 0) ?>";
            <?php endif; ?>

            if (!datos.cliente_id || datos.cliente_id === "0") {
                alert("No se encontró cliente_id para crear el producto.");
                return;
            }

            fetch("<?= RUTA_URL ?>/productos/crearDesdeCompra", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                })
                .then(res => res.text())
                .then(texto => {
                    // Limpiamos espacios, saltos de línea y BOM
                    texto = texto.trim().replace(/^\uFEFF/, '');

                    let producto;
                    try {
                        producto = JSON.parse(texto);
                    } catch (e) {
                        console.warn("JSON inválido recibido (pero producto puede haberse creado):", texto);
                        return; // no dispara catch
                    }

                    if (producto.error) {
                        alert("Error al crear el producto: " + producto.error);
                        return;
                    }

                    // Producto creado correctamente
                    document.querySelectorAll('.producto-select').forEach(select => {
                        const opt = document.createElement('option');
                        opt.value = producto.id;
                        opt.textContent = producto.nombre;
                        opt.dataset.impuesto = producto.impuesto_aplicable || '';
                        opt.dataset.preciocompra = producto.precio_compra || 0;
                        opt.dataset.ganancia = producto.porcentaje_ganancia || 0;
                        opt.dataset.precioventa = producto.precio_venta || 0;
                        select.appendChild(opt);
                    });

                    alert("Producto creado correctamente.");

                })
                .catch(err => {
                    console.error("Error de fetch:", err);
                    alert("Fallo al crear producto por error de conexión o algo mas");
                });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="<?= RUTA_URL ?>/compras/guardar"]');
            const proveedorSelect = document.getElementById('proveedor_id');

            form.addEventListener('submit', function(e) {
                if (!proveedorSelect.value) {
                    e.preventDefault(); // detener envío
                    alert("Debe seleccionar un proveedor antes de guardar la compra.");
                    proveedorSelect.focus();
                    return false;
                }
            });
        });
    </script>