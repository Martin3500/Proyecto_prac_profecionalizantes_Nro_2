<?php
require_once __DIR__ . '/includes/auth_admin.php';
require_once __DIR__ . '/includes/paginacion.php';
$pdo = conectarDB();

$busqueda = trim($_GET['busqueda'] ?? '');
$params = [];
$whereSql = '';

if ($busqueda !== '') {
    $whereSql = " WHERE (u.usuario LIKE :b1 OR u.nombre LIKE :b2 OR r.nombre LIKE :b3) ";
    $term = '%' . $busqueda . '%';
    $params = [
        ':b1' => $term,
        ':b2' => $term,
        ':b3' => $term,
    ];
}

// Conteo total para conocer la cantidad de coincidencias
$sqlCount = "SELECT COUNT(*) FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol $whereSql";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalRegistros = (int)$stmtCount->fetchColumn();

$paginacion = calcularPaginacion($totalRegistros, 15);

// Consulta principal paginada
$sqlUsuarios = "SELECT u.*, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol $whereSql ORDER BY u.nombre LIMIT {$paginacion['por_pagina']} OFFSET {$paginacion['offset']}";
$stmtUsuarios = $pdo->prepare($sqlUsuarios);
$stmtUsuarios->execute($params);
$usuarios = $stmtUsuarios->fetchAll();

$tituloPagina = 'Usuarios';
$paginaActiva = 'usuarios';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina">Usuarios del sistema</h1>
        <div class="subt">
            <?php if ($busqueda !== ''): ?>
                <?= $totalRegistros ?> usuario<?= $totalRegistros === 1 ? '' : 's' ?> encontrado<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php else: ?>
                <?= $totalRegistros ?> usuario<?= $totalRegistros === 1 ? '' : 's' ?> registrado<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php endif; ?>
        </div>
    </div>
    <a href="usuarios_form.php" class="btn btn-primary">
        <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Registrar usuario
    </a>
</div>

<?php if (!empty($_GET['ok'])): ?>
    <div class="alerta ok">
        <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        <?= htmlspecialchars($_GET['ok']) ?>
    </div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
    <div class="alerta">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<div class="panel-bloque">
    <!-- Barra de búsqueda y Limitador -->
    <div class="barra-busqueda">
        <form action="usuarios.php" method="GET" class="form-busqueda">
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="search" name="busqueda" class="form-control con-icono" 
                       placeholder="Buscar por usuario, nombre o rol..." 
                       value="<?= htmlspecialchars($busqueda) ?>"
                       onsearch="if(this.value=='') this.form.submit();">
            </div>
            <button type="submit" class="btn btn-primary" title="Buscar">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a href="usuarios.php" class="btn btn-outline" title="Quitar filtro">Limpiar</a>
            <?php endif; ?>
        </form>
        <div class="badge-limite" title="Listado paginado de a <?= $paginacion['por_pagina'] ?> registros por página">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?></span>
        </div>
    </div>

    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="5" class="celda-vacia">
                            <?= $busqueda !== '' ? 'No se encontraron usuarios para "' . htmlspecialchars($busqueda) . '".' : 'No hay usuarios cargados todavía.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <?php $esMiCuenta = ((int)$u['id_usuario'] === (int)$_SESSION['usuario_id']); ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($u['usuario']) ?></strong>
                                <?php if ($esMiCuenta): ?>
                                    <span class="badge" style="font-size: 0.7rem; margin-left: 0.35rem; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">Tú</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['nombre'] ?? '-') ?></td>
                            <td><?= htmlspecialchars(ucfirst($u['rol'])) ?></td>
                            <td>
                                <span class="badge <?= $u['activo'] ? 'activo' : 'inactivo' ?>">
                                    <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="acciones">
                                    <a class="btn-accion btn-editar" href="usuarios_form.php?id=<?= (int)$u['id_usuario'] ?>"><?= $esMiCuenta ? 'Editar mi cuenta' : 'Editar' ?></a>
                                    <?php if ($esMiCuenta): ?>
                                        <span style="color: var(--color-text-muted); font-size: 0.82rem;">(Sesión actual)</span>
                                    <?php elseif ($u['activo']): ?>
                                        <form action="usuarios_baja.php" method="POST" onsubmit="return confirm('¿Desactivar a este usuario?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$u['id_usuario'] ?>">
                                            <input type="hidden" name="accion" value="baja">
                                            <button type="submit" class="btn-accion btn-baja">Desactivar</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="usuarios_baja.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$u['id_usuario'] ?>">
                                            <input type="hidden" name="accion" value="reactivar">
                                            <button type="submit" class="btn-accion btn-reactivar">Reactivar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php renderizarPaginacion($paginacion); ?>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>