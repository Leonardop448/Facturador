<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);
?>

<?php include_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-white" style="background-color: #0D6EFD;">
            <h4 class="mb-0"><i class="bi bi-person-fill-gear me-2"></i> Usuarios internos registrados</h4>
        </div>
        <div class="card-body">

            <!-- Buscador -->
            <form method="GET" action="<?= RUTA_URL ?>/usuarioscliente/buscar" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, correo o teléfono..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                </div>
            </form>

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Rol</th>
                        <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                            <th>Cliente</th>
                        <?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr data-id="<?= $usuario['id'] ?>">
                            <form method="POST" action="<?= RUTA_URL ?>/usuarioscliente/actualizar/<?= $usuario['id'] ?>" class="form-inline">
                                <td>
                                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" class="form-control form-control-sm" required>
                                </td>
                                <td>
                                    <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" class="form-control form-control-sm" required>
                                </td>
                                <td>
                                    <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" class="form-control form-control-sm" required>
                                </td>
                                <td>
                                    <input type="text" name="direccion" value="<?= htmlspecialchars($usuario['direccion']) ?>" class="form-control form-control-sm" required>
                                </td>
                                <td>
                                    <select name="rol" class="form-select form-select-sm" required>
                                        <option value="vendedor" <?= $usuario['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                                        <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                    </select>
                                </td>
                                <?php if ($_SESSION['usuario']['rol'] === 'SuperU'): ?>
                                    <td><?= htmlspecialchars($usuario['nombre_cliente'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i></button>
                                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalClave<?= $usuario['id'] ?>">
                                            <i class="bi bi-key-fill"></i>
                                        </button>
                                        <a href="<?= RUTA_URL ?>/usuarioscliente/eliminar/<?= $usuario['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Deseas eliminar este usuario?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </form>
                        </tr>

                        <!-- Modal para cambiar contraseña -->
                        <div class="modal fade" id="modalClave<?= $usuario['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $usuario['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="modalLabel<?= $usuario['id'] ?>">Cambiar contraseña de <?= htmlspecialchars($usuario['nombre']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="<?= RUTA_URL ?>/usuarioscliente/cambiarclave/<?= $usuario['id'] ?>">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nueva contraseña</label>
                                                <input type="password" name="clave" class="form-control" minlength="6" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Guardar</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="text-end">
                <a href="<?= RUTA_URL ?>/usuarioscliente/crear" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Agregar nuevo usuario
                </a>
                <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>