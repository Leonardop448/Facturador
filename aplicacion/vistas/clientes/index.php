<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #198754;">
            <h4 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i> Clientes registrados</h4>
        </div>
        <div class="card-body">

            <!-- Buscador -->
            <form action="<?= RUTA_URL ?>/clientes/buscar" method="get" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre o documento..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>

            <?php if (!empty($clientes)) : ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                    <th>Usuario</th>
                                <?php endif; ?>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $index => $cliente) : ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                                    <td><?= htmlspecialchars($cliente['documento']) ?></td>
                                    <td><?= htmlspecialchars($cliente['correo']) ?></td>
                                    <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                                    <td><?= htmlspecialchars($cliente['direccion']) ?></td>
                                    <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                        <td><?= htmlspecialchars($cliente['cliente_dueño'] ?? 'N/A') ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= RUTA_URL ?>/clientes/editar/<?= $cliente['id'] ?>" class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['usuario']['rol'] !== 'vendedor') : ?>
                                                <a href="<?= RUTA_URL ?>/clientes/eliminar/<?= $cliente['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de eliminar este cliente?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="alert alert-warning text-center">
                    No se encontraron clientes registrados.
                </div>
            <?php endif; ?>

            <div class="text-end mt-4">
                <a href="<?= RUTA_URL ?>/clientes/crear" class="btn btn-outline-success">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </a>
                <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>