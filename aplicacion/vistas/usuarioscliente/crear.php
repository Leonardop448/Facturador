<?php
require_once BASE_PATH . '/aplicacion/middleware/verificar_rol.php';
verificarNivelAcceso(2);

// Si el usuario logueado es SuperU, necesitamos cargar la lista de clientes
$esSuperU = ($_SESSION['usuario']['rol'] === 'SuperU');

if ($esSuperU) {
    require_once BASE_PATH . '/configuracion/BaseDatos.php';
    $conexion = \App\configuracion\BaseDatos::conectar();
    $stmt = $conexion->query("SELECT id, nombre FROM usuarios WHERE rol = 'cliente'");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?php include_once BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-header text-white text-center" style="background-color: #0D6EFD;">
                    <h4 class="mb-0"><i class="bi bi-person-fill-gear me-2"></i> Crear Usuario Interno</h4>
                </div>
                <div class="card-body">
                    <form id="formUsuarioCliente" method="POST" action="<?= RUTA_URL ?>/usuarioscliente/guardar">

                        <?php if ($esSuperU): ?>
                            <div class="mb-3">
                                <label class="form-label">Cliente al que pertenece <span class="text-danger">*</span></label>
                                <select name="cliente_id" class="form-select" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Documento de Identidad <span class="text-danger">*</span></label>
                            <input type="text" name="documento" class="form-control" required pattern="\d+">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="clave" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" name="telefono" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Rol del usuario <span class="text-danger">*</span></label>
                            <select name="rol" class="form-select" required>
                                <option value="vendedor">Vendedor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn text-white" style="background-color: #0D6EFD;">
                                <i class="bi bi-check-circle me-1"></i> Guardar usuario
                            </button>
                            <a href="<?= RUTA_URL ?>/usuarioscliente/listar" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('formUsuarioCliente').addEventListener('submit', function(e) {
        const nombre = document.querySelector('input[name="nombre"]').value.trim();
        const correo = document.querySelector('input[name="correo"]').value.trim();
        const telefono = document.querySelector('input[name="telefono"]').value.trim();
        const direccion = document.querySelector('input[name="direccion"]').value.trim();

        const nombreValido = /^[a-zA-ZÁÉÍÓÚáéíóúñÑ ]{3,}$/.test(nombre);
        if (!nombreValido) {
            alert("El nombre debe tener al menos 3 letras y solo puede contener letras y espacios.");
            e.preventDefault();
            return;
        }

        if (!correo.includes('@') || correo.length < 6) {
            alert("Correo inválido.");
            e.preventDefault();
            return;
        }

        if (!/^\d{7,15}$/.test(telefono)) {
            alert("El teléfono debe tener entre 7 y 15 dígitos numéricos.");
            e.preventDefault();
            return;
        }

        if (direccion.length < 5) {
            alert("La dirección debe tener al menos 5 caracteres.");
            e.preventDefault();
            return;
        }
    });
</script>