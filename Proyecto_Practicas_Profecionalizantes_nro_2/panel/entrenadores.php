<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/paginacion.php';
$pdo = conectarDB();

// Redirección si se llama con editar_id para mantener compatibilidad
if (isset($_GET['editar_id'])) {
    header('Location: entrenadores_form.php?id=' . (int)$_GET['editar_id']);
    exit;
}

$mensajeOk = $_GET['ok'] ?? '';
$mensajeError = $_GET['error'] ?? '';

// --------------------------------------------------------------------------
// --------------------------------------------------------------------------
// CONSULTAS PARA MOSTRAR DATOS EN PANTALLA
// --------------------------------------------------------------------------

$busqueda = trim($_GET['busqueda'] ?? '');
$params = [];
$whereSql = '';

if ($busqueda !== '') {
    $whereSql = " WHERE (
        CONCAT(e.nombre, ' ', e.apellido) LIKE :b1 
        OR CONCAT(e.apellido, ' ', e.nombre) LIKE :b2 
        OR e.DNI LIKE :b3 
        OR e.email LIKE :b4 
        OR e.telefono LIKE :b5 
        OR d.disciplina LIKE :b6
    ) ";
    $term = '%' . $busqueda . '%';
    $params = [
        ':b1' => $term,
        ':b2' => $term,
        ':b3' => $term,
        ':b4' => $term,
        ':b5' => $term,
        ':b6' => $term,
    ];
}

// Conteo total para conocer la cantidad de coincidencias
$sqlCount = "
    SELECT COUNT(DISTINCT e.id_entrenador)
    FROM entrenadores e
    LEFT JOIN entrenadoresxdisciplinas exd ON e.id_entrenador = exd.id_entrenador
    LEFT JOIN disciplinas d ON exd.id_disciplina = d.id_disciplina
    $whereSql
";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalRegistros = (int)$stmtCount->fetchColumn();

$paginacion = calcularPaginacion($totalRegistros, 15);

// Consulta de entrenadores y sus disciplinas paginada
$sqlEntrenadores = "
    SELECT 
        e.id_entrenador,
        e.DNI,
        e.nombre,
        e.apellido,
        e.email,
        e.telefono,
        GROUP_CONCAT(d.disciplina ORDER BY d.disciplina SEPARATOR ', ') AS disciplinas_nombres
    FROM entrenadores e
    LEFT JOIN entrenadoresxdisciplinas exd ON e.id_entrenador = exd.id_entrenador
    LEFT JOIN disciplinas d ON exd.id_disciplina = d.id_disciplina
    $whereSql
    GROUP BY e.id_entrenador
    ORDER BY e.apellido ASC, e.nombre ASC
    LIMIT {$paginacion['por_pagina']} OFFSET {$paginacion['offset']}
";
$stmtEntrenadores = $pdo->prepare($sqlEntrenadores);
$stmtEntrenadores->execute($params);
$listaEntrenadores = $stmtEntrenadores->fetchAll();

$tituloPagina = 'Gestión de Entrenadores';
$paginaActiva = 'entrenadores';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <div class="eyebrow">
            <span class="punto"></span>
            Módulo de Personal
        </div>
        <h1 class="titulo-pagina">Gestión de Entrenadores</h1>
        <p class="subt">
            <?php if ($busqueda !== ''): ?>
                <?= $totalRegistros ?> entrenador<?= $totalRegistros === 1 ? '' : 'es' ?> encontrado<?= $totalRegistros === 1 ? '' : 'es' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php else: ?>
                <?= $totalRegistros ?> entrenador<?= $totalRegistros === 1 ? '' : 'es' ?> registrado<?= $totalRegistros === 1 ? '' : 'es' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php endif; ?>
        </p>
    </div>
    <div>
        <a href="entrenadores_form.php" class="btn btn-primary">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo Entrenador
        </a>
    </div>
</div>

<!-- Alertas de estado -->
<?php if (!empty($mensajeOk)): ?>
    <div class="alerta ok" style="margin-bottom: 1.5rem;">
        <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        <span><?= htmlspecialchars($mensajeOk) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alerta" style="margin-bottom: 1.5rem;">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
        <span><?= htmlspecialchars($mensajeError) ?></span>
    </div>
<?php endif; ?>

<!-- LISTADO DE ENTRENADORES -->
<div class="panel-bloque">
    <div class="panel-bloque-cabecera">
        <h2 class="titulo-bloque">Lista de Entrenadores Creados</h2>
    </div>

    <!-- Barra de búsqueda y Limitador -->
    <div class="barra-busqueda">
        <form action="entrenadores.php" method="GET" class="form-busqueda">
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="search" name="busqueda" class="form-control con-icono" 
                       placeholder="Buscar por DNI, nombre, email o disciplina..." 
                       value="<?= htmlspecialchars($busqueda) ?>"
                       onsearch="if(this.value=='') this.form.submit();">
            </div>
            <button type="submit" class="btn btn-primary" title="Buscar">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a href="entrenadores.php" class="btn btn-outline" title="Quitar filtro">Limpiar</a>
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
                    <th>DNI</th>
                    <th>Nombre y Apellido</th>
                    <th>Email / Teléfono</th>
                    <th>Disciplinas</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listaEntrenadores)): ?>
                    <tr>
                        <td colspan="5" class="celda-vacia">
                            <?= $busqueda !== '' ? 'No se encontraron entrenadores para "' . htmlspecialchars($busqueda) . '".' : 'No hay entrenadores registrados.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listaEntrenadores as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['DNI']) ?></td>
                            <td><strong><?= htmlspecialchars($e['apellido'] . ', ' . $e['nombre']) ?></strong></td>
                            <td>
                                <div><?= htmlspecialchars($e['email'] ?: '-') ?></div>
                                <div style="font-size: 0.8rem; color: var(--color-text-muted);"><?= htmlspecialchars($e['telefono'] ?: '-') ?></div>
                            </td>
                            <td>
                                <?php if (!empty($e['disciplinas_nombres'])): ?>
                                    <span class="badge activo"><?= htmlspecialchars($e['disciplinas_nombres']) ?></span>
                                <?php else: ?>
                                    <span style="color: var(--color-text-muted); font-style: italic;">Sin disciplina</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="acciones" style="justify-content: flex-end;">
                                    <a href="tarifas_entrenadores.php?id_entrenador=<?= (int)$e['id_entrenador'] ?>" 
                                       class="btn-accion" 
                                       style="background-color: #0284c7; color: white;" 
                                       title="Gestionar Tarifas">
                                        Tarifas
                                    </a>
                                    <a href="entrenadores_form.php?id=<?= (int)$e['id_entrenador'] ?>" class="btn-accion btn-editar">
                                        Editar
                                    </a>
                                    
                                    <form action="entrenadores_baja.php" method="POST" onsubmit="return confirm('¿Confirma que desea eliminar a este entrenador?');" style="display:inline;">
                                        <input type="hidden" name="id_entrenador" value="<?= (int)$e['id_entrenador'] ?>">
                                        <button type="submit" class="btn-accion btn-baja">Baja</button>
                                    </form>
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