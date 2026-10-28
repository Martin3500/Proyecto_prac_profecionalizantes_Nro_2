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
        OR t.nombre LIKE :b4
        OR hp.metodo_pago LIKE :b5
    ) ";
    $term = '%' . $busqueda . '%';
    $params = [
        ':b1' => $term,
        ':b2' => $term,
        ':b3' => $term,
        ':b4' => $term,
        ':b5' => $term,
    ];
}

// Conteo total para conocer la cantidad de coincidencias
$stmtCount = $pdo->prepare("
    SELECT COUNT(*)
    FROM historial_de_pagos hp
    JOIN clientes c ON hp.id_cliente = c.id_cliente
    JOIN tarifas t ON hp.id_tarifa = t.id_tarifa
    $whereSql
");
$stmtCount->execute($params);
$totalRegistros = (int)$stmtCount->fetchColumn();

$paginacion = calcularPaginacion($totalRegistros, 15);

// Consulta principal paginada
$stmt = $pdo->prepare("
    SELECT 
        hp.id_pago, 
        hp.fecha_pago, 
        COALESCE(hp.fecha_vencimiento, DATE_ADD(DATE(hp.fecha_pago), INTERVAL 30 DAY)) AS fecha_vencimiento,
        hp.monto, 
        hp.metodo_pago,
        c.DNI, 
        c.nombre, 
        c.apellido, 
        t.nombre AS tarifa
    FROM historial_de_pagos hp
    JOIN clientes c ON hp.id_cliente = c.id_cliente
    JOIN tarifas t ON hp.id_tarifa = t.id_tarifa
    $whereSql
    ORDER BY hp.fecha_pago DESC
    LIMIT {$paginacion['por_pagina']} OFFSET {$paginacion['offset']}
");
$stmt->execute($params);
$pagos = $stmt->fetchAll();

$hoy = date('Y-m-d');
$limitePorVencer = date('Y-m-d', strtotime('+5 days'));

$tituloPagina = 'Pagos';
$paginaActiva = 'pagos';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina">Historial de pagos</h1>
        <div class="subt">
            <?php if ($busqueda !== ''): ?>
                <?= $totalRegistros ?> pago<?= $totalRegistros === 1 ? '' : 's' ?> encontrado<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php else: ?>
                <?= $totalRegistros ?> pago<?= $totalRegistros === 1 ? '' : 's' ?> registrado<?= $totalRegistros === 1 ? '' : 's' ?> (Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?>)
            <?php endif; ?>
        </div>
    </div>
    <div class="acciones" style="flex-wrap: wrap;">
        <a href="exportar_csv.php?tipo=pagos<?= $busqueda !== '' ? '&busqueda=' . urlencode($busqueda) : '' ?>" class="btn btn-outline" title="Descargar reporte en formato CSV para Excel">
            <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar CSV
        </a>
        <a href="pagos_form.php" class="btn btn-primary">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Registrar pago
        </a>
    </div>
</div>

<?php if (!empty($_GET['ok'])): ?>
    <div class="alerta ok">
        <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        <?= htmlspecialchars($_GET['ok']) ?>
    </div>
<?php endif; ?>

<div class="panel-bloque">
    <!-- Barra de búsqueda y Limitador -->
    <div class="barra-busqueda">
        <form action="pagos.php" method="GET" class="form-busqueda">
            <div class="campo-icono">
                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="search" name="busqueda" class="form-control con-icono" 
                       placeholder="Buscar por DNI, cliente, tarifa o método de pago..." 
                       value="<?= htmlspecialchars($busqueda) ?>"
                       onsearch="if(this.value=='') this.form.submit();">
            </div>
            <button type="submit" class="btn btn-primary" title="Buscar">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a href="pagos.php" class="btn btn-outline" title="Quitar filtro">Limpiar</a>
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
                    <th>Fecha</th>
                    <th>DNI</th>
                    <th>Cliente</th>
                    <th>Tarifa</th>
                    <th>Método</th>
                    <th>Vencimiento</th>
                    <th>Monto</th>
                    <th>Comprobante</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="8" class="celda-vacia">
                            <?= $busqueda !== '' ? 'No se encontraron pagos para "' . htmlspecialchars($busqueda) . '".' : 'No hay pagos registrados todavía.' ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagos as $p): ?>
                        <?php
                            $venc = $p['fecha_vencimiento'];
                            if ($venc < $hoy) {
                                $claseVenc = 'inactivo';
                                $textoVenc = 'Vencida';
                            } elseif ($venc <= $limitePorVencer) {
                                $claseVenc = 'pausado';
                                $textoVenc = 'Por vencer';
                            } else {
                                $claseVenc = 'activo';
                                $textoVenc = 'Vigente';
                            }
                        ?>
                        <tr>
                            <td class="col-fecha"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['fecha_pago']))) ?></td>
                            <td><?= htmlspecialchars($p['DNI']) ?></td>
                            <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></td>
                            <td><?= htmlspecialchars($p['tarifa']) ?></td>
                            <td><span class="badge" style="background: rgba(255,255,255,0.06); border: 1px solid var(--color-border);"><?= htmlspecialchars($p['metodo_pago'] ?? 'Efectivo') ?></span></td>
                            <td>
                                <div><?= htmlspecialchars(date('d/m/Y', strtotime($venc))) ?></div>
                                <span class="badge <?= $claseVenc ?>" style="margin-top: 0.2rem; font-size: 0.7rem; padding: 0.15rem 0.5rem;">
                                    <?= $textoVenc ?>
                                </span>
                            </td>
                            <td class="col-monto">$<?= number_format((float)$p['monto'], 2, ',', '.') ?></td>
                            <td>
                                <a href="pago_recibo.php?id=<?= (int)$p['id_pago'] ?>" class="btn-accion btn-editar" title="Ver e imprimir recibo">
                                    <svg class="icon" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 4px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    Recibo
                                </a>
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