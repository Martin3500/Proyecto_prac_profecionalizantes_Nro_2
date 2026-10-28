<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/paginacion.php';
$pdo = conectarDB();

$busqueda = trim($_GET['busqueda'] ?? '');
$params = [];
$whereSql = '';

if ($busqueda !== '') {
    $whereSql = " WHERE (
        CONCAT(c.nombre, ' ', c.apellido) LIKE :b1 
        OR CONCAT(c.apellido, ' ', c.nombre) LIKE :b2 
        OR c.DNI LIKE :b3 
        OR c.email LIKE :b4 
        OR c.telefono LIKE :b5 
        OR e.Estado LIKE :b6
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
$stmtCount = $pdo->prepare("
    SELECT COUNT(*) 
    FROM clientes c 
    JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
    $whereSql
");
$stmtCount->execute($params);
$totalRegistros = (int)$stmtCount->fetchColumn();

$paginacion = calcularPaginacion($totalRegistros, 15);

// Consulta principal paginada
$stmt = $pdo->prepare("
    SELECT c.*, e.Estado,
        (
            SELECT COALESCE(hp.fecha_vencimiento, DATE_ADD(DATE(hp.fecha_pago), INTERVAL 30 DAY))
            FROM historial_de_pagos hp
            WHERE hp.id_cliente = c.id_cliente
            ORDER BY hp.fecha_pago DESC
            LIMIT 1
        ) AS ultimo_vencimiento
    FROM clientes c
    JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
    $whereSql
    ORDER BY c.apellido, c.nombre
    LIMIT {$paginacion['por_pagina']} OFFSET {$paginacion['offset']}
");
$stmt->execute($params);
$clientes = $stmt->fetchAll();

$hoy = date('Y-m-d');
$limitePorVencer = date('Y-m-d', strtotime('+5 days'));

function claseBadge(string $estado): string
{
    return match ($estado) {
        'Activo' => 'activo',
        'Inactivo' => 'inactivo',
        'Pausado' => 'pausado',
        'Suspensión' => 'suspension',
        default => '',
    };
}

$tituloPagina = 'Clientes';
$paginaActiva = 'clientes';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina">Clientes</h1>
        <div class="subt">
            <?php if ($busqueda !== ''): ?>
                <?= $totalRegistros ?> cliente<?= $totalRegistros === 1 ? '' : 's' ?> encontrado<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php else: ?>
                <?= $totalRegistros ?> cliente<?= $totalRegistros === 1 ? '' : 's' ?> registrado<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php endif; ?>
        </div>
    </div>
    <div class="acciones" style="flex-wrap: wrap;">
        <a href="exportar_csv.php?tipo=clientes<?= $busqueda !== '' ? '&busqueda=' . urlencode($busqueda) : '' ?>" class="btn btn-outline" title="Descargar listado en formato CSV para Excel">
            <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar CSV
        </a>
        <a href="clientes_form.php" class="btn btn-primary">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nuevo cliente
        </a>
    </div>
</div>

<?php if (!empty($_GET['ok'])): ?>
    <div class="alerta ok">
        <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        <span><?= htmlspecialchars($_GET['ok']) ?></span>
    </div>
<?php endif; ?>

<div class="panel-bloque">
    <!-- Barra de búsqueda y Limitador -->
    <div class="barra-busqueda">
        <form action="clientes.php" method="GET" class="form-busqueda">
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="search" name="busqueda" class="form-control con-icono" 
                       placeholder="Buscar por DNI, nombre, email o estado..." 
                       value="<?= htmlspecialchars($busqueda) ?>"
                       onsearch="if(this.value=='') this.form.submit();">
            </div>
            <button type="submit" class="btn btn-primary" title="Buscar">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a href="clientes.php" class="btn btn-outline" title="Quitar filtro">Limpiar</a>
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
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Cuota / Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clientes)): ?>
                    <tr>
                        <td colspan="6" class="celda-vacia">
                            <?= $busqueda !== '' ? 'No se encontraron clientes que coincidan con "' . htmlspecialchars($busqueda) . '".' : 'No hay clientes cargados todavía.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clientes as $c): ?>
                        <?php
                            $venc = $c['ultimo_vencimiento'];
                            if (empty($venc)) {
                                $badgeCuota = 'inactivo';
                                $textoCuota = 'Sin pagos';
                            } elseif ($venc < $hoy) {
                                $badgeCuota = 'inactivo';
                                $textoCuota = 'Vencida (' . date('d/m/Y', strtotime($venc)) . ')';
                            } elseif ($venc <= $limitePorVencer) {
                                $badgeCuota = 'pausado';
                                $textoCuota = 'Vence ' . date('d/m/Y', strtotime($venc));
                            } else {
                                $badgeCuota = 'activo';
                                $textoCuota = 'Al día (' . date('d/m/Y', strtotime($venc)) . ')';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($c['DNI']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></strong>
                                <?php if (!empty($c['email'])): ?>
                                    <div style="font-size: 0.78rem; color: var(--color-text-muted);"><?= htmlspecialchars($c['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($c['telefono'] ?? '-') ?></td>
                            <td><span class="badge <?= claseBadge($c['Estado']) ?>"><?= htmlspecialchars($c['Estado']) ?></span></td>
                            <td><span class="badge <?= $badgeCuota ?>"><?= htmlspecialchars($textoCuota) ?></span></td>
                            <td>
                                <div class="acciones">
                                    <a class="btn-accion btn-editar" href="pagos_form.php?id_cliente=<?= (int)$c['id_cliente'] ?>" title="Registrar pago de cuota">Cobrar</a>
                                    <a class="btn-accion btn-editar" href="asistencias_cliente.php?id=<?= (int)$c['id_cliente'] ?>">Asistencias</a>
                                    <a class="btn-accion btn-editar" href="clientes_form.php?id=<?= (int)$c['id_cliente'] ?>">Editar</a>
                                    <?php if ($c['Estado'] === 'Activo'): ?>
                                        <form action="clientes_baja.php" method="POST" onsubmit="return confirm('¿Dar de baja a este cliente?');" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$c['id_cliente'] ?>">
                                            <input type="hidden" name="accion" value="baja">
                                            <button type="submit" class="btn-accion btn-baja">Dar de baja</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="clientes_baja.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$c['id_cliente'] ?>">
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