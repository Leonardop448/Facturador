<?php require_once BASE_PATH . '/aplicacion/middleware/verificar_sesion.php'; ?>
<?php include BASE_PATH . '/aplicacion/vistas/plantillas/encabezado.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4">
                <div class="card-header text-white text-center" style="background-color: #060635;">
                    <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i> Mi Perfil</h4>
                </div>
                <div class="card-body">
                    <form id="formPerfil" method="post" action="<?= RUTA_URL ?>/usuarios/actualizarPerfil">

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre"
                                value="<?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Documento de Identidad</label>
                            <input type="text" class="form-control"
                                value="<?= $_SESSION['usuario']['documento'] ?? $_SESSION['usuario']['documento_identidad'] ?? 'No disponible' ?>"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control"
                                value="<?= $_SESSION['usuario']['correo'] ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono"
                                value="<?= htmlspecialchars($_SESSION['usuario']['telefono']) ?>"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion"
                                value="<?= htmlspecialchars($_SESSION['usuario']['direccion']) ?>"
                                class="form-control" required>
                        </div>

                        <hr class="my-4">
                        <h5 class="text-start"><i class="bi bi-lock me-2"></i> Cambiar contraseña</h5>

                        <div class="mb-3">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" name="clave_actual" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="clave_nueva" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" name="clave_confirmar" class="form-control">
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn text-white" style="background-color: #060635;">
                                <i class="bi bi-save me-1"></i> Guardar cambios
                            </button>
                            <a href="<?= RUTA_URL ?>/modulos/inicio" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Validaciones con JavaScript -->
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const nombre = document.querySelector('input[name="nombre"]').value.trim();
        const telefono = document.querySelector('input[name="telefono"]').value.trim();
        const direccion = document.querySelector('input[name="direccion"]').value.trim();

        // Validar nombre
        const nombreValido = /^[a-zA-ZÁÉÍÓÚáéíóúñÑ ]{3,}$/.test(nombre);
        if (!nombreValido) {
            alert("El nombre debe tener al menos 3 letras y solo puede contener letras y espacios.");
            e.preventDefault();
            return;
        }

        // Validar teléfono
        const telefonoValido = /^\d{7,15}$/.test(telefono);
        if (!telefonoValido) {
            alert("El teléfono debe tener entre 7 y 15 dígitos numéricos.");
            e.preventDefault();
            return;
        }

        // Validar dirección
        if (direccion.length < 5) {
            alert("La dirección debe tener al menos 5 caracteres.");
            e.preventDefault();
            return;
        }
    });

    document.getElementById('formPerfil').addEventListener('submit', function(e) {
        const claveActual = document.querySelector('input[name="clave_actual"]').value.trim();
        const claveNueva = document.querySelector('input[name="clave_nueva"]').value.trim();
        const claveConfirmar = document.querySelector('input[name="clave_confirmar"]').value.trim();

        if (claveActual !== '' || claveNueva !== '' || claveConfirmar !== '') {
            if (claveNueva === '' || claveConfirmar === '') {
                alert('Por favor completa todos los campos de cambio de contraseña.');
                e.preventDefault();
                return;
            }

            if (claveNueva !== claveConfirmar) {
                alert('La nueva contraseña y su confirmación no coinciden.');
                e.preventDefault();
                return;
            }

            if (claveNueva.length < 6) {
                alert('La nueva contraseña debe tener al menos 6 caracteres.');
                e.preventDefault();
                return;
            }
        }
    });
</script>