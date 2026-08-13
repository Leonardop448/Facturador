<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php';
// --- AGREGA ESTE BLOQUE AQUÍ ---
if (isset($mensaje) && $mensaje === 'sesion_cerrada'): ?>
  <script>
    alert('Has cerrado sesión correctamente.');
    // Opcional: limpiar el mensaje de la URL para que no vuelva a salir al recargar
    window.history.replaceState({}, document.title, window.location.pathname);
  </script>
<?php endif;
// --------------------------------
?>

<style>
  body {
    background: url('<?= RUTA_URL ?>/imagenes/fondoindex.jpeg') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    color: #fff;
  }

  .card {
    background-color: #ffffffff;
    color: #000000ff;
  }

  .text-shadow {
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
  }

  .hero {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    /* padding arriba/abajo, izquierda/derecha */
  }


  .hero-text {
    flex: 1 1 50%;
    max-width: 600px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.6);
    font-family: 'Comic Relief', system-ui;
  }

  .hero-text h1 {
    font-weight: bold;
    font-size: 2.5rem;
  }

  .hero-text p {
    font-size: 1.2rem;
    margin-top: 1rem;
  }

  .hero-image {
    flex: 1 1 40%;
    text-align: center;
  }

  .hero-image img {
    max-width: 90%;
    height: auto;
    border-radius: 50px;
    margin-left: 4rem;
  }

  .badges {
    margin-top: 2rem;
  }

  .badge-box {
    background-color: orange;
    color: white;
    padding: 0.7rem 1.2rem;
    font-weight: bold;
    border-radius: 5px;
    margin: 0.5rem 0;
    display: inline-block;
  }

  .footer-text {
    margin-top: 3rem;
    font-style: italic;
    font-weight: bold;
  }

  /* 👇 Corrección para móviles */
  @media (max-width: 768px) {
    .hero {
      flex-direction: column;
      text-align: center;
      padding: 3rem 1rem;
      /* Reduce en móvil */
    }

    .hero-image img {
      margin-left: 0;
    }
  }
</style>


<div class="container py-5">

  <div class="hero">
    <div class="hero-text">
      <h1>¿Buscas un sistema de facturación ágil, económico y fácil de usar?</h1>
      <p>Con <strong>FacCil</strong> puedes gestionar tus ventas de forma segura, rápida y sin complicaciones. Nuestra herramienta te brinda el control que necesitas para hacer crecer tu negocio. <br><br>
        <strong>Pide tu mes de prueba gratis aqui!</strong><br>
        <!-- Botón de WhatsApp pequeño -->
        <a href="https://wa.me/573233022983" target="_blank" class="btn btn-outline-success btn-sm d-inline-flex align-items-center mt-1 offset-1">
          <i class="fab fa-whatsapp me-1"></i> WhatsApp
        </a>
      </p>


      <div class="badges">
        <div class="badge-box">+ de 1000 clientes satisfechos en Colombia</div><br>
        <div class="badge-box">Desarrollado 100% en Colombia con soporte garantizado</div>
      </div>

      <div class="footer-text">“Más de 2 años acompañando a los negocios como el tuyo…”</div>
    </div>

    <div class="hero-image">
      <img src="<?= RUTA_URL ?>/imagenes/logo.png" alt="Logo">
      <!-- Cambia 'logo.png' por tu logo -->
    </div>
  </div>
  <div class="d-flex justify-content-center align-items-center my-5">
    <div class="text-center mb-2 text-white">
      <div class="hero-text">
        <h1 class="mb-3 text-shadow">Bienvenido a FacCil</h1>
        <h4 class="text-shadow">
          Sistema integral para la gestión de productos, clientes, facturación, inventario y más.
        </h4>
      </div>
    </div>
  </div>

  <!-- Tarjetas centradas -->
  <div class="row justify-content-center row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">

    <?php
    $modulos = [
      ['icon' => 'person-fill-gear', 'color' => 'primary', 'titulo' => 'Usuarios', 'desc' => 'Gestión de accesos y permisos.'],
      ['icon' => 'person-lines-fill', 'color' => 'success', 'titulo' => 'Clientes', 'desc' => 'Gestión de clientes y ventas.'],
      ['icon' => 'box-seam', 'color' => 'primary', 'titulo' => 'Productos', 'desc' => 'Catálogo y control de productos.'],
      ['icon' => 'person-vcard', 'color' => 'danger', 'titulo' => 'Proveedores', 'desc' => 'Administración de proveedores.'],
      ['icon' => 'receipt', 'color' => 'warning', 'titulo' => 'Factura POS', 'desc' => 'Facturación rápida en tienda.'],
      ['icon' => 'file-earmark-check', 'color' => 'info', 'titulo' => 'Factura Electrónica', 'desc' => 'Emisión de facturas legales.'],
      ['icon' => 'clipboard-data', 'color' => 'secondary', 'titulo' => 'Inventario', 'desc' => 'Movimientos y existencias.'],
      ['icon' => 'cart-check', 'color' => 'danger', 'titulo' => 'Compras', 'desc' => 'Registro de compras a proveedores.'],
      ['icon' => 'cash-coin', 'color' => 'dark', 'titulo' => 'Gastos', 'desc' => 'Control de gastos operativos.'],
      ['icon' => 'graph-up-arrow', 'color' => 'primary', 'titulo' => 'Reportes', 'desc' => 'Informes y estadísticas.'],
      ['icon' => 'credit-card', 'color' => 'success', 'titulo' => 'Créditos', 'desc' => 'Ventas a crédito y seguimiento.'],
      ['icon' => 'truck', 'color' => 'warning', 'titulo' => 'Pedidos', 'desc' => 'Pedidos y logística de entrega.'],
      ['icon' => 'journal-text', 'color' => 'info', 'titulo' => 'Contabilidad', 'desc' => 'Control contable básico.'],
      ['icon' => 'gear', 'color' => 'secondary', 'titulo' => 'Configuración', 'desc' => 'Ajustes generales del sistema.'],
    ];

    foreach ($modulos as $modulo): ?>
      <div class="col">
        <div class="card h-100 shadow-sm rounded-4 text-center">
          <div class="card-body p-3 ">
            <i class="bi bi-<?= $modulo['icon'] ?> fs-2 text-<?= $modulo['color'] ?> mb-2"></i>
            <h6 class="card-title mb-1"><?= $modulo['titulo'] ?></h6>
            <p class="small"><?= $modulo['desc'] ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
  <br>
  <br>
  <div class="container">
    <div class="row align-items-center ">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <h2 class="fw-bold mb-3 text-shadow">¿Requieres <span class="text-primary">Asistencia Técnica</span>?</h2>
        <p class="mb-4 text-shadow">Nuestro equipo de atención está listo para ayudarte. Comunícate con nosotros a través de cualquiera de las siguientes líneas de contacto según tu necesidad.</p>
        <div class="row g-3 mb-4">
          <div class="col-6">
            <div class="bg-white rounded-4 shadow-sm p-3 text-center text-shadow">
              <strong class="text-primary">ASISTENCIA TÉCNICA</strong><br>
              <span class="text-muted">(+57) 323 302 29 83</span>
              <a href="https://wa.me/573233022983" target="_blank" class="btn btn-outline-success btn-sm d-inline-flex align-items-center mt-1">
                <i class="fab fa-whatsapp me-1"></i> WhatsApp
              </a>
            </div>
          </div>
          <div class="col-6">
            <div class="bg-white rounded-4 shadow-sm p-3 text-center text-shadow">
              <strong class="text-primary">ÁREA COMERCIAL</strong><br>
              <span class="text-muted">(+57) 323 302 29 83</span>
              <a href="https://wa.me/573233022983" target="_blank" class="btn btn-outline-success btn-sm d-inline-flex align-items-center mt-1">
                <i class="fab fa-whatsapp me-1"></i> WhatsApp
              </a>
            </div>
            <br>
          </div>
        </div>
        <p class="text-white text-shadow ">
          Nuestro horario de atención es de lunes a viernes de 8:00 a.m. a 5:00 p.m. y sábados de 8:00 a.m. a 12:00 p.m. Domingos y festivos no prestamos servicio.
        </p>
      </div>
      <div class="col-lg-6 text-center">
        <img src="<?= RUTA_URL ?>/imagenes/asistencia.png" alt="Soporte Técnico" class="img-fluid ms-4" style="max-width: 400px; border-radius: 50px;">
      </div>
    </div>
  </div>


</div>
</div>
</div>


<?php include BASE_PATH . '/aplicacion/vistas/plantillas/pie.php'; ?>