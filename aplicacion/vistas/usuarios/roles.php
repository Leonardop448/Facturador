<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(1);
?>

<?php require_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container mt-4">
    <h3 class="text-primary mb-4">Gestión de Roles de Usuarios</h3>

    <!-- Filtro de búsqueda -->
    <form method="GET" action="<?= RUTA_URL ?>/usuarios/roles" class="mb-3 d-flex ">
        <input type="text" name="filtro" class="form-control me-2" placeholder="Buscar por nombre o correo" value="<?= htmlspecialchars($_GET['filtro'] ?? '') ?>">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol actual</th>
                <th>Cliente hasta</th>
                <th>Actualizar rol / fecha</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                    <td><span class="badge bg-info text-dark"><?= $usuario['rol'] ?></span></td>
                    <td>
                        <?php if (!empty($usuario['cliente_hasta'])): ?>
                            <?= date('Y-m-d', strtotime($usuario['cliente_hasta'])) ?>
                        <?php else: ?>
                            <span class="text-muted">No definido</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="<?= RUTA_URL ?>/usuarios/cambiarRol" method="POST" class="d-flex flex-column flex-md-row gap-2">
                            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                            <select name="rol" class="form-select">
                                <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                                <option value="cliente" <?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                            </select>
                            <input type="date" name="cliente_hasta" class="form-control"
                                value="<?= !empty($usuario['cliente_hasta']) ? date('Y-m-d', strtotime($usuario['cliente_hasta'])) : '' ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                        </form>
                    </td>
                    <td>
                        <form action="<?= RUTA_URL ?>/usuarios/eliminar" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= RUTA_URL ?>/usuarios/roles?pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>