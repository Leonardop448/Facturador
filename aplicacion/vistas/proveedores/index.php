<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>
<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);
?>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #DC3545;">
            <h4 class="mb-0"><i class="bi bi-person-vcard me-2"></i> Proveedores registrados</h4>
        </div>
        <div class="card-body">

            <!-- Buscador -->
            <form method="get" action="<?= RUTA_URL ?>/proveedores/buscar" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, documento, ciudad..." value="<?= $_GET['q'] ?? '' ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>

            <?php if (empty($proveedores)) : ?>
                <div class="alert alert-info">No se encontraron proveedores.</div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>NIT</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                                <th>Ciudad</th>
                                <th>Fecha de Registro</th>
                                <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                                    <th>Cliente</th>
                                <?php endif; ?>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $prov) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($prov['nombre']) ?></td>
                                    <td><?= htmlspecialchars($prov['documento']) ?></td>
                                    <td><?= htmlspecialchars($prov['telefono']) ?></td>
                                    <td><?= htmlspecialchars($prov['email']) ?></td>
                                    <td><?= htmlspecialchars($prov['direccion']) ?></td>
                                    <td><?= htmlspecialchars($prov['ciudad']) ?></td>
                                    <td><?= $prov['fecha_creacion'] ?></td>
                                    <?php if ($_SESSION['usuario']['rol'] === 'SuperU') : ?>
                                        <td><?= htmlspecialchars($prov['cliente_nombre'] ?? '-') ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= RUTA_URL ?>/proveedores/editar/<?= $prov['id'] ?>" class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= RUTA_URL ?>/proveedores/eliminar/<?= $prov['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar proveedor?')" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="text-end mt-4">
                <a href="<?= RUTA_URL ?>/proveedores/crear" class="btn btn-outline-danger">
                    <i class="fas fa-plus"></i> Nuevo Proveedor
                </a>
                <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

        </div>
    </div>
</div>