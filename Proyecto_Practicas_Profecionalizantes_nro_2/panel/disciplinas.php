<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/paginacion.php';
$pdo = conectarDB();

$busqueda = trim($_GET['busqueda'] ?? '');
$params = [];
$whereSql = '';

if ($busqueda !== '') {
    $whereSql = " WHERE (d.disciplina LIKE :b1 OR d.descripcion LIKE :b2) ";
    $term = '%' . $busqueda . '%';
    $params = [
        ':b1' => $term,
        ':b2' => $term,
    ];
}

// Conteo total para conocer la cantidad de coincidencias
$sqlCount = "SELECT COUNT(*) FROM disciplinas d $whereSql";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalRegistros = (int)$stmtCount->fetchColumn();

$paginacion = calcularPaginacion($totalRegistros, 15);

// Consulta de disciplinas paginada
$sqlDisciplinas = "
    SELECT d.id_disciplina, d.disciplina, d.descripcion, COUNT(ed.id_entrenador_disciplina) AS total_entrenadores
    FROM disciplinas d
    LEFT JOIN entrenadoresxdisciplinas ed ON d.id_disciplina = ed.id_disciplina
    $whereSql
    GROUP BY d.id_disciplina, d.disciplina, d.descripcion
    ORDER BY d.disciplina
    LIMIT {$paginacion['por_pagina']} OFFSET {$paginacion['offset']}
";
$stmtDisciplinas = $pdo->prepare($sqlDisciplinas);
$stmtDisciplinas->execute($params);
$disciplinas = $stmtDisciplinas->fetchAll();

$tituloPagina = 'Disciplinas';
$paginaActiva = 'disciplinas';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina">Disciplinas</h1>
        <div class="subt">
            <?php if ($busqueda !== ''): ?>
                <?= $totalRegistros ?> disciplina<?= $totalRegistros === 1 ? '' : 's' ?> encontrada<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php else: ?>
                <?= $totalRegistros ?> disciplina<?= $totalRegistros === 1 ? '' : 's' ?> registrada<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php endif; ?>
        </div>
    </div>
    <a href="disciplinas_form.php" class="btn btn-primary">
        <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Nueva disciplina
    </a>
</div>

<?php if (!empty($_GET['ok'])): ?>
    <div class="alerta ok">
        <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        <span><?= htmlspecialchars($_GET['ok']) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alerta">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        <span><?= htmlspecialchars($_GET['error']) ?></span>
    </div>
<?php endif; ?>

<div class="panel-bloque">
    <!-- Barra de búsqueda y Limitador -->
    <div class="barra-busqueda">
        <form action="disciplinas.php" method="GET" class="form-busqueda">
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="search" name="busqueda" class="form-control con-icono" 
                       placeholder="Buscar por disciplina o descripción..." 
                       value="<?= htmlspecialchars($busqueda) ?>"
                       onsearch="if(this.value=='') this.form.submit();">
            </div>
            <button type="submit" class="btn btn-primary" title="Buscar">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a href="disciplinas.php" class="btn btn-outline" title="Quitar filtro">Limpiar</a>
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
                    <th>Disciplina</th>
                    <th>Descripción</th>
                    <th>Entrenadores Asignados</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($disciplinas)): ?>
                    <tr>
                        <td colspan="4" class="celda-vacia">
                            <?= $busqueda !== '' ? 'No se encontraron disciplinas para "' . htmlspecialchars($busqueda) . '".' : 'No hay disciplinas cargadas todavía.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($disciplinas as $d): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($d['disciplina']) ?></strong></td>
                            <td><?= htmlspecialchars($d['descripcion'] ?? '-') ?></td>
                            <td>
                                <span class="badge <?= $d['total_entrenadores'] > 0 ? 'activo' : 'inactivo' ?>">
                                    <?= (int)$d['total_entrenadores'] ?> entrenador<?= $d['total_entrenadores'] == 1 ? '' : 'es' ?>
                                </span>
                            </td>
                            <td>
                                <div class="acciones">
                                    <a class="btn-accion btn-editar" href="disciplinas_form.php?id=<?= (int)$d['id_disciplina'] ?>">Editar</a>
                                    <form action="disciplinas_baja.php" method="POST" onsubmit="return confirm('¿Seguro que querés eliminar la disciplina <?= htmlspecialchars($d['disciplina'], ENT_QUOTES) ?>?');" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int)$d['id_disciplina'] ?>">
                                        <button type="submit" class="btn-accion btn-baja">Eliminar</button>
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