<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-X5CWZRDPMX"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'G-X5CWZRDPMX');
</script>
<?php

use App\modelos\UsuariosCliente;
use App\modelos\Empresa;

require_once BASE_PATH . '/configuracion/BaseDatos.php';
require_once BASE_PATH . '/aplicacion/modelos/Empresa.php';
require_once BASE_PATH . '/aplicacion/modelos/UsuariosCliente.php';

$rol = $_SESSION['usuario']['rol'] ?? '';
$nombreEmpresa = 'Empresa no asignada';

$conexion = \App\configuracion\BaseDatos::conectar(); // ← aquí está el cambio correcto

if ($rol === 'cliente') {
    $clienteId = $_SESSION['usuario']['id'];
    $empresaModelo = new Empresa($conexion);
    $empresa = $empresaModelo->obtenerPorCliente($clienteId);
    if ($empresa) {
        $nombreEmpresa = $empresa['nombre'];
    } else {
        $nombreEmpresa = '⚠ Empresa no registrada para cliente';
    }
} elseif (in_array($rol, ['admin', 'vendedor'])) {
    $usuarioActual = $_SESSION['usuario'];
    $usuarioClienteModelo = new UsuariosCliente($conexion);
    $datosUsuario = $usuarioClienteModelo->buscarPorId($usuarioActual['id']);

    if ($datosUsuario && isset($datosUsuario['cliente_id'])) {
        $empresaModelo = new Empresa($conexion);
        $empresa = $empresaModelo->obtenerPorCliente($datosUsuario['cliente_id']);


        if ($empresa) {
            $nombreEmpresa = $empresa['nombre'];
        } else {
            $nombreEmpresa = '⚠ Empresa no registrada para cliente_id = ' . $datosUsuario['cliente_id'];
        }
    } else {
        $nombreEmpresa = '⚠ No se pudo determinar el cliente del usuario';
    }
} elseif ($rol === 'SuperU') {
    $nombreEmpresa = 'Administrador General';
    $rol = 'SuperUsuario';
}
?>






<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Panel de Módulos</h3>
        <div class="alert alert-info mb-0 py-2 px-3 text-end" style="max-width: 350px;">
            <div><strong>Empresa:</strong> <?= htmlspecialchars($nombreEmpresa) ?></div>
            <div><strong>Rol:</strong> <?= htmlspecialchars(ucfirst($rol)) ?></div>
        </div>
    </div>





    <?php
    $rol = $_SESSION['usuario']['rol'] ?? 'usuario';

    $modulos = [
        [
            'icon' => 'person-fill-gear',
            'color' => 'primary',
            'titulo' => 'Usuarios',
            'desc' => 'Gestión de accesos y permisos.',
            'url' =>  RUTA_URL . '/usuarioscliente/listar',
            'roles' => ['SuperU', 'admin', 'cliente']
        ],
        ['icon' => 'person-lines-fill', 'color' => 'success', 'titulo' => 'Clientes', 'desc' => 'Gestión de clientes y ventas.', 'url' => RUTA_URL . '/clientes/index', 'roles' => ['SuperU', 'cliente', 'admin', 'vendedor']],
        ['icon' => 'box-seam', 'color' => 'primary', 'titulo' => 'Productos', 'desc' => 'Catálogo y control de productos.', 'url' => RUTA_URL . '/productos/index', 'roles' => ['SuperU', 'cliente', 'admin', 'vendedor']],
        ['icon' => 'person-vcard', 'color' => 'danger', 'titulo' => 'Proveedores', 'desc' => 'Administración de proveedores.', 'url' => RUTA_URL . '/proveedores/index', 'roles' => ['SuperU', 'cliente', 'admin']],
        ['icon' => 'receipt', 'color' => 'warning', 'titulo' => 'Factura POS', 'desc' => 'Facturación rápida en tienda.', 'url' => RUTA_URL . '/FacturaPOS/crear', 'roles' => ['SuperU', 'cliente', 'admin', 'vendedor']],
        ['icon' => 'file-earmark-check', 'color' => 'info', 'titulo' => 'Factura Electrónica', 'desc' => 'Emisión de facturas legales.', 'url' => RUTA_URL . '/factura/electronica', 'roles' => ['SuperU', 'cliente', 'admin', 'vendedor']],
        ['icon' => 'clipboard-data', 'color' => 'secondary', 'titulo' => 'Inventario', 'desc' => 'Movimientos y existencias.', 'url' => RUTA_URL . '/inventario/index', 'roles' => ['SuperU', 'cliente', 'admin']],
        ['icon' => 'cart-check', 'color' => 'danger', 'titulo' => 'Compras', 'desc' => 'Registro de compras a proveedores.', 'url' => RUTA_URL . '/compras/index', 'roles' => ['SuperU', 'cliente', 'admin']],
        ['icon' => 'cash-coin', 'color' => 'dark', 'titulo' => 'Gastos', 'desc' => 'Control de gastos operativos.', 'url' => RUTA_URL . '/gastos', 'roles' => ['SuperU', 'cliente', 'admin']],
        ['icon' => 'graph-up-arrow', 'color' => 'primary', 'titulo' => 'Reportes', 'desc' => 'Informes y estadísticas.', 'url' => RUTA_URL . '/reportes', 'roles' => ['SuperU', 'cliente', 'admin']],
        ['icon' => 'credit-card', 'color' => 'success', 'titulo' => 'Créditos', 'desc' => 'Ventas a crédito y seguimiento.', 'url' => RUTA_URL . '/creditos', 'roles' => ['SuperU', 'cliente', 'admin', 'vendedor']],
        ['icon' => 'truck', 'color' => 'warning', 'titulo' => 'Pedidos', 'desc' => 'Pedidos y logística de entrega.', 'url' => RUTA_URL . '/pedidos', 'roles' => ['SuperU', 'cliente', 'admin', 'vendedor']],
        ['icon' => 'journal-text', 'color' => 'info', 'titulo' => 'Contabilidad', 'desc' => 'Control contable básico.', 'url' => RUTA_URL . '/contabilidad', 'roles' => ['SuperU', 'cliente', 'admin']],
        ['icon' => 'gear', 'color' => 'success', 'titulo' => 'Configuración', 'desc' => 'Ajustes generales del sistema.', 'url' => RUTA_URL . '/usuarios/configuracion', 'roles' => ['SuperU', 'cliente', 'admin', 'vendedor', 'usuario']],
    ];
    ?>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($modulos as $modulo):
            $acceso = in_array($rol, $modulo['roles']);
        ?>
            <div class="col">
                <div class="card h-100 text-center shadow-sm rounded-4 <?= $acceso ? '' : 'border-secondary bg-light text-muted' ?>">
                    <div class="card-body d-flex flex-column justify-content-between p-3">
                        <div>
                            <i class="bi bi-<?= $modulo['icon'] ?> fs-2 mb-2 text-<?= $acceso ? $modulo['color'] : 'secondary' ?>"></i>
                            <h6 class="card-title"><?= $modulo['titulo'] ?></h6>
                            <p class="small mb-3"><?= $modulo['desc'] ?></p>
                        </div>
                        <?php if ($acceso): ?>
                            <a href="<?= $modulo['url'] ?>" class="btn btn-sm btn-outline-<?= $modulo['color'] ?>">Acceder</a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled>No disponible</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>