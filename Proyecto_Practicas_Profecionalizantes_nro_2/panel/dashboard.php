<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$totalClientes = $pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();

$clientesActivos = $pdo->query("
    SELECT COUNT(*) FROM clientes c
    JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
    WHERE e.Estado = 'Activo'
")->fetchColumn();

$totalEntrenadores = $pdo->query('SELECT COUNT(*) FROM entrenadores')->fetchColumn();
$totalDisciplinas = $pdo->query('SELECT COUNT(*) FROM disciplinas')->fetchColumn();

$recaudadoMes = $pdo->query("
    SELECT COALESCE(SUM(monto), 0) FROM historial_de_pagos
    WHERE MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())
")->fetchColumn();

$ultimosPagos = $pdo->query("
    SELECT hp.id_pago, hp.fecha_pago, hp.monto, c.nombre, c.apellido, t.nombre AS tarifa
    FROM historial_de_pagos hp
    JOIN clientes c ON hp.id_cliente = c.id_cliente
    JOIN tarifas t ON hp.id_tarifa = t.id_tarifa
    ORDER BY hp.fecha_pago DESC
    LIMIT 4
")->fetchAll();

// Clientes activos con cuota vencida, por vencer en los próximos 7 días o sin pagos registrados
$alertasVencimiento = $pdo->query("
    SELECT 
        c.id_cliente,
        c.DNI,
        c.nombre,
        c.apellido,
        c.telefono,
        (
            SELECT COALESCE(hp.fecha_vencimiento, DATE_ADD(DATE(hp.fecha_pago), INTERVAL 30 DAY))
            FROM historial_de_pagos hp
            WHERE hp.id_cliente = c.id_cliente
            ORDER BY hp.fecha_pago DESC, hp.id_pago DESC
            LIMIT 1
        ) AS ultimo_vencimiento
    FROM clientes c
    JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
    WHERE e.Estado = 'Activo'
    HAVING ultimo_vencimiento IS NULL OR ultimo_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY ultimo_vencimiento ASC
    LIMIT 8
")->fetchAll();
$hoy = date('Y-m-d');

// Recaudación de los últimos 6 meses (incluye el actual), con los meses sin pagos en $0
$mesesAbrev = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun',
               '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];

$recaudacionMensual = [];
for ($i = 5; $i >= 0; $i--) {
    $clave = date('Y-m', strtotime("-$i months"));
    $recaudacionMensual[$clave] = ['etiqueta' => $mesesAbrev[date('m', strtotime("-$i months"))], 'total' => 0.0];
}

$filasRecaudacion = $pdo->query("
    SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS ym, SUM(monto) AS total
    FROM historial_de_pagos
    WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY ym
")->fetchAll();

foreach ($filasRecaudacion as $fila) {
    if (isset($recaudacionMensual[$fila['ym']])) {
        $recaudacionMensual[$fila['ym']]['total'] = (float)$fila['total'];
    }
}

$maxRecaudacionMensual = max(1, ...array_column($recaudacionMensual, 'total'));

$tituloPagina = 'Dashboard';
$paginaActiva = 'dashboard';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <div class="eyebrow">
            <span class="punto"></span>
            Resumen en Tiempo Real
        </div>
        <h1 class="titulo-pagina">Dashboard</h1>
        <p class="subt">Visión general del estado financiero, accesos y miembros del gimnasio.</p>
    </div>
    <div class="acciones" style="flex-wrap: wrap;">
        <a href="acceso.php" class="btn btn-primary">
            <svg class="icon" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Fichar Acceso (DNI)
        </a>
        <a href="exportar_csv.php?tipo=clientes" class="btn btn-outline" title="Exportar padrón de clientes en CSV">
            <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Clientes CSV
        </a>
        <a href="exportar_csv.php?tipo=pagos" class="btn btn-outline" title="Exportar historial de pagos en CSV">
            <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Pagos CSV
        </a>
    </div>
</div>

<div class="cards-metricas">
    <div class="card-metrica azul">
        <div class="icono-metrica">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c.7-3.4 3-5.3 5.5-5.3s4.8 1.9 5.5 5.3"/><circle cx="17.5" cy="8.5" r="2.3"/><path d="M15.8 14.9c2 .2 3.6 1.9 4.1 4.3"/></svg>
        </div>
        <div class="info-metrica">
            <div class="valor"><?= (int)$totalClientes ?></div>
            <div class="etiqueta">Clientes totales</div>
        </div>
    </div>

    <div class="card-metrica verde">
        <div class="icono-metrica">
            <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div class="info-metrica">
            <div class="valor"><?= (int)$clientesActivos ?></div>
            <div class="etiqueta">Clientes activos</div>
        </div>
    </div>

    <div class="card-metrica naranja">
        <div class="icono-metrica">
            <svg class="icon" viewBox="0 0 24 24"><rect x="2.5" y="6" width="19" height="13" rx="2"/><path d="M2.5 10h19"/><path d="M6 15h4"/></svg>
        </div>
        <div class="info-metrica">
            <div class="valor">$<?= number_format((float)$recaudadoMes, 2, ',', '.') ?></div>
            <div class="etiqueta">Recaudado este mes</div>
        </div>
    </div>

    <div class="card-metrica gris">
        <div class="icono-metrica">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 2l2.4 6.9H21l-5.5 4.2 2.1 7-5.6-4.3L6.4 20l2.1-7L3 8.9h6.6z"/></svg>
        </div>
        <div class="info-metrica">
            <div class="valor"><?= (int)$totalEntrenadores ?></div>
            <div class="etiqueta">Entrenadores</div>
        </div>
    </div>

    <div class="card-metrica gris">
        <div class="icono-metrica">
            <svg class="icon" viewBox="0 0 24 24"><path d="M13 2 4.5 13h5L9 22l9.5-13h-5z"/></svg>
        </div>
        <div class="info-metrica">
            <div class="valor"><?= (int)$totalDisciplinas ?></div>
            <div class="etiqueta">Disciplinas</div>
        </div>
    </div>
</div>

<div class="fila-dos-columnas">
    <div class="panel-bloque">
        <div class="panel-bloque-cabecera">
            <h2 class="titulo-bloque">Recaudación de los últimos 6 meses</h2>
        </div>
        <div class="grafico-barras">
            <?php $claves = array_keys($recaudacionMensual); $claveActual = end($claves); ?>
            <?php foreach ($recaudacionMensual as $clave => $mes): ?>
                <?php $alturaPct = max(round(($mes['total'] / $maxRecaudacionMensual) * 100), $mes['total'] > 0 ? 4 : 1); ?>
                <?php $esActual = $clave === $claveActual; ?>
                <div class="gb-columna">
                    <div class="gb-barra-wrap">
                        <div class="gb-barra<?= $esActual ? ' gb-actual' : '' ?>" style="height: <?= $alturaPct ?>%"
                             data-valor="$<?= number_format($mes['total'], 0, ',', '.') ?>"></div>
                    </div>
                    <div class="gb-etiqueta<?= $esActual ? ' gb-etiqueta-actual' : '' ?>"><?= htmlspecialchars($mes['etiqueta']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel-bloque">
        <div class="panel-bloque-cabecera">
            <h2 class="titulo-bloque">Últimos pagos registrados</h2>
        </div>
        <div class="tabla-scroll">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Recibo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimosPagos)): ?>
                        <tr>
                            <td colspan="4" class="celda-vacia">Todavía no hay pagos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ultimosPagos as $p): ?>
                            <tr>
                                <td class="col-fecha"><?= htmlspecialchars(date('d/m/Y', strtotime($p['fecha_pago']))) ?></td>
                                <td class="col-cliente"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></td>
                                <td class="col-monto">$<?= number_format((float)$p['monto'], 2, ',', '.') ?></td>
                                <td>
                                    <a href="pago_recibo.php?id=<?= (int)$p['id_pago'] ?>" class="btn-accion btn-editar" title="Imprimir comprobante">Recibo</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bloque de Control de Vencimientos de Cuotas -->
<div class="panel-bloque" style="margin-top: 1.5rem;">
    <div class="panel-bloque-cabecera" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <h2 class="titulo-bloque">Control de Vencimientos de Cuotas (Socios Activos)</h2>
            <p class="subt" style="font-size: 0.85rem;">Socios con cuota vencida, sin pagos registrados o que vencen en los próximos 7 días.</p>
        </div>
        <a href="pagos_form.php" class="btn-accion btn-editar">+ Registrar Pago</a>
    </div>
    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Socio</th>
                    <th>Vencimiento</th>
                    <th>Estado de Cuota</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alertasVencimiento)): ?>
                    <tr>
                        <td colspan="5" class="celda-vacia">¡Excelente! Todos los clientes activos tienen su cuota al día por más de 7 días.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($alertasVencimiento as $av): ?>
                        <?php
                            $v = $av['ultimo_vencimiento'];
                            if (empty($v)) {
                                $cls = 'inactivo';
                                $txt = 'Sin pagos registrados';
                            } elseif ($v < $hoy) {
                                $cls = 'inactivo';
                                $txt = 'Vencida';
                            } else {
                                $cls = 'pausado';
                                $txt = 'Próxima a vencer';
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($av['DNI']) ?></td>
                            <td><strong><?= htmlspecialchars($av['nombre'] . ' ' . $av['apellido']) ?></strong></td>
                            <td class="col-fecha"><?= !empty($v) ? htmlspecialchars(date('d/m/Y', strtotime($v))) : '-' ?></td>
                            <td><span class="badge <?= $cls ?>"><?= $txt ?></span></td>
                            <td>
                                <div class="acciones">
                                    <a href="pagos_form.php?id_cliente=<?= (int)$av['id_cliente'] ?>" class="btn-accion btn-editar">Renovar Cuota</a>
                                    <a href="asistencias_cliente.php?id=<?= (int)$av['id_cliente'] ?>" class="btn-accion btn-editar">Asistencias</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>