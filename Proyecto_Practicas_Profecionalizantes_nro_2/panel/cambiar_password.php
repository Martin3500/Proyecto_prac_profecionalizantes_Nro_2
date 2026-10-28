<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout_inicio.php';
?>

<!-- Cabecera de la sección -->
<div class="panel-header">
    <div>
        <span class="eyebrow">
            <span class="punto"></span>
            Seguridad
        </span>
        <h1 class="titulo-pagina">Cambiar Contraseña</h1>
        <p class="subt">Actualiza tu clave de acceso para mantener tu cuenta resguardada.</p>
    </div>
</div>

<!-- Alerta de Éxito -->
<?php if (isset($_GET['mensaje'])): ?>
    <div class="alerta ok mb-4">
        <svg class="icon" viewBox="0 0 24 24">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>Contraseña actualizada correctamente.</span>
    </div>
<?php endif; ?>

<!-- Alerta de Error -->
<?php if (isset($_GET['error'])): ?>
    <div class="alerta mb-4">
        <svg class="icon" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>
            <?php 
                if ($_GET['error'] == 'pass_actual') echo "La contraseña actual es incorrecta.";
                elseif ($_GET['error'] == 'coincidencia') echo "Las nuevas contraseñas no coinciden.";
                elseif ($_GET['error'] == 'vacio') echo "Por favor, completa todos los campos.";
                else echo "Ocurrió un error al actualizar.";
            ?>
        </span>
    </div>
<?php endif; ?>

<!-- Bloque de Formulario -->
<div class="panel-bloque panel-formulario-password">
    <form action="cambiar_password_guardar.php" method="POST">
        
        <div class="campo">
            <label for="password_actual">Contraseña Actual</label>
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <input type="password" name="password_actual" id="password_actual" class="form-control con-icono" placeholder="Ingresa tu contraseña actual" required>
            </div>
        </div>

        <div class="campo">
            <label for="password_nueva">Nueva Contraseña</label>
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <input type="password" name="password_nueva" id="password_nueva" class="form-control con-icono" placeholder="Ingresa la nueva contraseña" required>
            </div>
        </div>

        <div class="campo">
            <label for="password_confirmar">Confirmar Nueva Contraseña</label>
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <input type="password" name="password_confirmar" id="password_confirmar" class="form-control con-icono" placeholder="Confirma la nueva contraseña" required>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Actualizar Contraseña
            </button>
        </div>

    </form>
</div>

<?php
require_once __DIR__ . '/includes/layout_fin.php';
?>