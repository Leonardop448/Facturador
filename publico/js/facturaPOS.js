document.addEventListener("DOMContentLoaded", function () {
  const selectUsuario = document.getElementById("usuario_id");
  const selectCliente = document.getElementById("cliente_id");
  const dataListProductos = document.getElementById("productos-list");

  // === CARGAR CLIENTES ===
  if (selectUsuario && selectCliente) {
    selectUsuario.addEventListener("change", function () {
      const usuarioId = this.value;
      selectCliente.innerHTML =
        '<option value="">Cargando clientes...</option>';

      if (!usuarioId) {
        selectCliente.innerHTML =
          '<option value="">Seleccione un cliente</option>';
        return;
      }

      fetch("/facturador/publico/FacturaPOS/obtenerClientesPorUsuario", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `usuario_id=${usuarioId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          selectCliente.innerHTML =
            '<option value="">Seleccione un cliente</option>';

          if (data.error) {
            alert(data.error);
            return;
          }

          if (data.length === 0) {
            selectCliente.innerHTML =
              '<option value="">No hay clientes disponibles</option>';
            return;
          }

          data.forEach((cliente) => {
            const option = document.createElement("option");
            option.value = cliente.id;
            option.textContent = cliente.nombre;
            selectCliente.appendChild(option);
          });
        })
        .catch((error) => console.error("Error al cargar clientes:", error));
    });
  }

  // === CARGAR PRODUCTOS ===
  if (selectUsuario && dataListProductos) {
    selectUsuario.addEventListener("change", function () {
      const usuarioId = this.value;
      dataListProductos.innerHTML = "";

      if (!usuarioId) {
        const option = document.createElement("option");
        option.value = "Seleccione un producto";
        dataListProductos.appendChild(option);
        return;
      }

      fetch("/facturador/publico/FacturaPOS/obtenerProductosPorUsuario", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${usuarioId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          dataListProductos.innerHTML = "";

          if (data.error) {
            alert(data.error);
            return;
          }

          if (data.length === 0) {
            const option = document.createElement("option");
            option.value = "No hay productos disponibles";
            dataListProductos.appendChild(option);
            return;
          }

          data.forEach((producto) => {
            const option = document.createElement("option");
            option.value = producto.nombre;
            option.dataset.id = producto.id;
            option.dataset.impuesto = producto.impuesto_aplicable;
            option.dataset.precioventa = producto.precio_venta;
            dataListProductos.appendChild(option);
          });
        })
        .catch((error) => console.error("Error al cargar productos:", error));
    });
  }

  // === PRODUCTOS POS ===
  const detalleFactura = document.getElementById("detalle-factura");
  const btnAgregarProducto = document.getElementById("agregar-producto-pos");
  const subtotalGeneralEl = document.getElementById("subtotal-general");
  const impuestoTotalEl = document.getElementById("impuesto-total");
  const totalGeneralEl = document.getElementById("total-general");
  const inputTotalGeneral = document.getElementById("input-total-general");
  const descuentoEl = document.getElementById("descuento");

  function calcularTotales() {
    let subtotal = 0;
    let impuestoTotal = 0;

    detalleFactura.querySelectorAll("tr").forEach((fila) => {
      const cantidad = parseFloat(fila.querySelector(".cantidad").value) || 0;
      const precio = parseFloat(fila.querySelector(".precio_venta").value) || 0;

      const impuestoTexto = (
        fila.querySelector(".text-impuesto")?.textContent || ""
      ).trim();
      const match = impuestoTexto.match(/\d+/);
      const impuesto = match ? parseFloat(match[0]) : 0;

      const subtotalFila = cantidad * precio;
      const impuestoFila = (subtotalFila * impuesto) / 100;

      const subtotalFilaRed = Math.ceil(subtotalFila);
      const impuestoFilaRed = Math.ceil(impuestoFila);

      const subTextEl = fila.querySelector(".subtotal-text");
      const subInputEl = fila.querySelector(".input-subtotal");
      if (subTextEl) subTextEl.textContent = subtotalFilaRed;
      if (subInputEl) subInputEl.value = subtotalFilaRed;

      subtotal += subtotalFila;
      impuestoTotal += impuestoFila;
    });

    let descuentoPct = parseInt(descuentoEl.value) || 0;
    if (descuentoPct < 0) descuentoPct = 0;
    if (descuentoPct > 100) descuentoPct = 100;

    const descuentoMonto = Math.ceil(subtotal * (descuentoPct / 100));
    const total = Math.max(0, Math.ceil(subtotal) - descuentoMonto);

    subtotalGeneralEl.textContent = Math.ceil(subtotal);
    impuestoTotalEl.textContent = Math.ceil(impuestoTotal);
    totalGeneralEl.textContent = total;
    inputTotalGeneral.value = total;
  }

  // Agregar nueva fila
  if (btnAgregarProducto) {
    btnAgregarProducto.addEventListener("click", () => {
      const index = detalleFactura.querySelectorAll("tr").length;
      const nuevaFila = document.createElement("tr");

      nuevaFila.innerHTML = `
        <td>
          <input list="productos-list" class="form-control producto-autocomplete" required>
          <input type="hidden" name="productos[${index}][producto_id]" class="input-producto-id">
        </td>
        <td><input type="number" name="productos[${index}][cantidad]" class="form-control cantidad" min="1" value="1"></td>
        <td><input type="number" name="productos[${index}][precio_venta]" class="form-control precio_venta" readonly></td>
        <td><span class="text-impuesto">0%</span><input type="hidden" name="productos[${index}][impuesto_valor]" class="input-impuesto" value="0"></td>
        <td><span class="subtotal-text">0</span><input type="hidden" name="productos[${index}][subtotal]" class="input-subtotal" value="0"></td>
        <td><button type="button" class="btn btn-danger btn-sm eliminar-producto">X</button></td>
      `;
      detalleFactura.appendChild(nuevaFila);
    });
  }

  // Delegación de eventos para cantidad, precio y eliminación
  detalleFactura.addEventListener("input", (e) => {
    if (
      e.target.classList.contains("cantidad") ||
      e.target.classList.contains("precio_venta") ||
      e.target.classList.contains("producto-autocomplete")
    ) {
      // Actualizar producto_id, precio e impuesto si cambió el producto
      if (e.target.classList.contains("producto-autocomplete")) {
        const input = e.target;
        const valor = input.value;
        const option = Array.from(dataListProductos.options).find(
          (opt) => opt.value === valor
        );

        if (option) {
          const fila = input.closest("tr");
          let inputId = fila.querySelector(".input-producto-id");
          if (!inputId) {
            inputId = document.createElement("input");
            inputId.type = "hidden";
            inputId.classList.add("input-producto-id");
            inputId.name = input.name;
            fila.appendChild(inputId);
          }
          inputId.value = option.dataset.id || 0;

          fila.querySelector(".precio_venta").value =
            option.dataset.precioventa || 0;
          fila.querySelector(".text-impuesto").textContent =
            option.dataset.impuesto || 0;
          fila.querySelector(".input-impuesto").value =
            option.dataset.impuesto || 0;
        }
      }

      calcularTotales();
    }
  });

  detalleFactura.addEventListener("click", (e) => {
    if (e.target.classList.contains("eliminar-producto")) {
      e.target.closest("tr").remove();
      calcularTotales();
    }
  });

  descuentoEl.addEventListener("input", calcularTotales);
});
