<?php
require_once __DIR__ . '/includes/auth_admin.php';
$pdo = conectarDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuarioItem = [
    'id_usuario' => 0,
    'usuario' => '',
    'nombre' => '',
    'id_rol' => 0,
];

// Obtener roles disponibles desde la base de datos
$roles = $pdo->query('SELECT id_rol, nombre, descripcion FROM roles ORDER BY id_rol ASC')->fetchAll();

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = ?');
    $stmt->execute([$id]);
    $encontrado = $stmt->fetch();
    if (!$encontrado) {
        header('Location: usuarios.php');
        exit;
    }
    $usuarioItem = $encontrado;
} else {
    // Rol 'staff' por defecto si existe, o el primer rol disponible
    foreach ($roles as $r) {
        if (strtolower($r['nombre']) === 'staff') {
            $usuarioItem['id_rol'] = (int)$r['id_rol'];
            break;
        }
    }
    if ($usuarioItem['id_rol'] === 0 && !empty($roles)) {
        $usuarioItem['id_rol'] = (int)$roles[0]['id_rol'];
    }
}

$esPropioUsuario = ($id > 0 && $id === (int)($_SESSION['usuario_id'] ?? 0));

$tituloPagina = $id > 0 ? ($esPropioUsuario ? 'Editar mi usuario' : 'Editar usuario') : 'Nuevo usuario';
$paginaActiva = 'usuarios';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina">
            <?= $id > 0 ? ($esPropioUsuario ? 'Editar mi usuario' : 'Editar usuario') : 'Registrar usuario' ?>
            <?php if ($esPropioUsuario): ?>
                <span class="badge" style="font-size: 0.75rem; vertical-align: middle; margin-left: 0.5rem; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">Tu cuenta</span>
            <?php endif; ?>
        </h1>
        <div class="subt">
            <?php if ($id > 0): ?>
                <?= $esPropioUsuario ? 'Actualizá tu nombre de usuario, nombre y apellido o contraseña. Por seguridad, no podés modificar tu propio rol.' : 'Actualizá los datos del usuario.' ?>
            <?php else: ?>
                Completá los datos para dar de alta un nuevo usuario en el sistema.
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($_GET['error'])): ?>
    <div class="alerta" style="margin-bottom: 1.5rem;">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
        <span><?= htmlspecialchars($_GET['error']) ?></span>
    </div>
<?php endif; ?>

<div class="panel-bloque">
    <form method="post" action="usuarios_guardar.php" autocomplete="off">
        <input type="hidden" name="id_usuario" value="<?= (int)$usuarioItem['id_usuario'] ?>">

        <div class="form-grid">
            
            <!-- Campo Nombre de Usuario -->
            <div class="campo">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Ej: jperez" required autocomplete="off" value="<?= htmlspecialchars($usuarioItem['usuario']) ?>">
            </div>

            <!-- Campo Nombre Completo -->
            <div class="campo">
                <label for="nombre">Nombre y Apellido</label>
                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required autocomplete="name" value="<?= htmlspecialchars($usuarioItem['nombre'] ?? '') ?>">
            </div>

            <!-- Campo Rol de Usuario -->
            <?php if ($esPropioUsuario): ?>
                <div class="campo">
                    <label for="id_rol">
                        Rol de usuario 
                        <span style="font-weight: normal; color: var(--color-text-muted); font-size: 0.8rem;">(Bloqueado para tu propia cuenta)</span>
                    </label>
                    <select id="id_rol" class="form-control" disabled style="opacity: 0.75; cursor: not-allowed;">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int)$r['id_rol'] ?>" <?= ((int)$usuarioItem['id_rol'] === (int)$r['id_rol']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($r['nombre'])) ?><?= !empty($r['descripcion']) ? ' (' . htmlspecialchars($r['descripcion']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Campo oculto para asegurar el envío del rol actual -->
                    <input type="hidden" name="id_rol" value="<?= (int)$usuarioItem['id_rol'] ?>">
                    <small style="color: var(--color-text-muted); font-size: 0.8rem; margin-top: 0.35rem; display: block;">
                        Por seguridad no podés modificar tu propio rol. Solo otro administrador puede cambiar tu nivel de acceso.
                    </small>
                </div>
            <?php else: ?>
                <div class="campo">
                    <label for="id_rol">Rol de usuario</label>
                    <select id="id_rol" name="id_rol" class="form-control" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int)$r['id_rol'] ?>" <?= ((int)$usuarioItem['id_rol'] === (int)$r['id_rol']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($r['nombre'])) ?><?= !empty($r['descripcion']) ? ' (' . htmlspecialchars($r['descripcion']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Campo Contraseña -->
            <div class="campo">
                <label for="password">
                    Contraseña 
                    <?php if ($id > 0): ?>
                        <span style="font-weight: normal; color: var(--color-text-muted); font-size: 0.8rem;">(dejar en blanco para no cambiarla)</span>
                    <?php endif; ?>
                </label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" autocomplete="new-password" <?= $id > 0 ? '' : 'required' ?>>
            </div>

            <!-- Campo Confirmar Contraseña -->
            <div class="campo">
                <label for="password_confirm">Confirmar contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Repetí la contraseña" autocomplete="new-password" <?= $id > 0 ? '' : 'required' ?>>
            </div>

        </div>

        <!-- Botones de Acción -->
        <div class="form-acciones">
            <button type="submit" class="btn btn-primary">
                <?= $id > 0 ? 'Guardar cambios' : 'Registrar usuario' ?>
            </button>
            <a href="usuarios.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>