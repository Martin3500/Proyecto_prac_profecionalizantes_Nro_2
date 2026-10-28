<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$resultadoCheckin = null;
$dniBuscado = '';
$hoy = date('Y-m-d');
$limitePorVencer = date('Y-m-d', strtotime('+5 days'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dniBuscado = trim($_POST['dni'] ?? '');
    $forzarIngreso = !empty($_POST['forzar_ingreso']);

    if ($dniBuscado === '' || !ctype_digit($dniBuscado)) {
        $resultadoCheckin = [
            'tipo' => 'error',
            'titulo' => 'DNI inválido',
            'mensaje' => 'Por favor, ingresá únicamente los números del DNI del socio.',
        ];
    } else {
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                e.Estado,
                up.id_pago,
                up.fecha_pago,
                COALESCE(up.fecha_vencimiento, DATE_ADD(DATE(up.fecha_pago), INTERVAL 30 DAY)) AS ultimo_vencimiento,
                t.nombre AS tarifa_nombre
            FROM clientes c
            JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
            LEFT JOIN historial_de_pagos up ON up.id_pago = (
                SELECT hp2.id_pago
                FROM historial_de_pagos hp2
                WHERE hp2.id_cliente = c.id_cliente
                ORDER BY hp2.fecha_pago DESC, hp2.id_pago DESC
                LIMIT 1
            )
            LEFT JOIN tarifas t ON up.id_tarifa = t.id_tarifa
            WHERE c.DNI = ?
            LIMIT 1
        ");
        $stmt->execute([$dniBuscado]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            $resultadoCheckin = [
                'tipo' => 'no_encontrado',
                'titulo' => 'Socio no encontrado',
                'mensaje' => "No existe ningún cliente registrado con el DNI {$dniBuscado}.",
                'dni' => $dniBuscado,
            ];
        } else {
            $idCliente = (int)$cliente['id_cliente'];
            $venc = $cliente['ultimo_vencimiento'];
            $esActivo = ($cliente['Estado'] === 'Activo');
            $cuotaAlDia = (!empty($venc) && $venc >= $hoy);

            if (($esActivo && $cuotaAlDia) || $forzarIngreso) {
                // Evitar doble fichaje accidental en los últimos 5 minutos
                $stmtReciente = $pdo->prepare("
                    SELECT COUNT(*) FROM historia_de_asistencia
                    WHERE id_cliente = ? AND asistio = 1 AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                ");
                $stmtReciente->execute([$idCliente]);
                $yaFichadoReciente = ((int)$stmtReciente->fetchColumn() > 0);

                if (!$yaFichadoReciente) {
                    $stmtIns = $pdo->prepare("
                        INSERT INTO historia_de_asistencia (id_cliente, fecha_hora, asistio)
                        VALUES (?, NOW(), 1)
                    ");
                    $stmtIns->execute([$idCliente]);
                }

                // Contar asistencias del mes actual
                $stmtMes = $pdo->prepare("
                    SELECT COUNT(*) FROM historia_de_asistencia
                    WHERE id_cliente = ? AND asistio = 1
                      AND MONTH(fecha_hora) = MONTH(CURDATE())
                      AND YEAR(fecha_hora) = YEAR(CURDATE())
                ");
                $stmtMes->execute([$idCliente]);
                $asistenciasMes = (int)$stmtMes->fetchColumn();

                $porVencer = (!empty($venc) && $venc >= $hoy && $venc <= $limitePorVencer);
                $diasRestantes = !empty($venc) ? (int)round((strtotime($venc) - strtotime($hoy)) / 86400) : 0;

                $resultadoCheckin = [
                    'tipo' => ($forzarIngreso && (!$esActivo || !$cuotaAlDia)) ? 'advertencia' : ($porVencer ? 'advertencia' : 'exito'),
                    'titulo' => $yaFichadoReciente ? 'INGRESO YA REGISTRADO RECIENTEMENTE' : '¡ACCESO HABILITADO! PRESENTE REGISTRADO',
                    'cliente' => $cliente,
                    'asistencias_mes' => $asistenciasMes,
                    'dias_restantes' => $diasRestantes,
                    'forzado' => $forzarIngreso,
                ];
                $dniBuscado = ''; // Limpiar para el próximo socio
            } else {
                $motivo = [];
                if (!$esActivo) {
                    $motivo[] = "Estado actual del socio: {$cliente['Estado']}";
                }
                if (empty($venc)) {
                    $motivo[] = 'No registra pagos de cuota en el sistema';
                } elseif ($venc < $hoy) {
                    $motivo[] = 'Cuota vencida el ' . date('d/m/Y', strtotime($venc));
                }

                $resultadoCheckin = [
                    'tipo' => 'denegado',
                    'titulo' => 'ACCESO RESTRINGIDO — REGULARIZAR SITUACIÓN',
                    'motivo' => implode(' · ', $motivo),
                    'cliente' => $cliente,
                ];
            }
        }
    }
}

// Obtener los ingresos de hoy
$ingresosHoy = $pdo->query("
    SELECT 
        a.id_asistencia,
        a.fecha_hora,
        c.id_cliente,
        c.DNI,
        c.nombre,
        c.apellido,
        e.Estado
    FROM historia_de_asistencia a
    JOIN clientes c ON a.id_cliente = c.id_cliente
    JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
    WHERE DATE(a.fecha_hora) = CURDATE() AND a.asistio = 1
    ORDER BY a.fecha_hora DESC
    LIMIT 50
")->fetchAll();

$totalHoy = (int)$pdo->query("
    SELECT COUNT(*) FROM historia_de_asistencia
    WHERE DATE(fecha_hora) = CURDATE() AND asistio = 1
")->fetchColumn();

$tituloPagina = 'Control de Acceso';
$paginaActiva = 'acceso';
require __DIR__ . '/includes/layout_inicio.php';
?>

<style>
.checkin-card {
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    margin-bottom: 2rem;
    border: 2px solid var(--color-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.checkin-card.exito {
    background-color: rgba(34, 197, 94, 0.12);
    border-color: rgba(34, 197, 94, 0.45);
}
.checkin-card.advertencia {
    background-color: rgba(249, 115, 22, 0.12);
    border-color: rgba(249, 115, 22, 0.45);
}
.checkin-card.denegado {
    background-color: rgba(239, 68, 68, 0.12);
    border-color: rgba(239, 68, 68, 0.45);
}
.checkin-estado-tag {
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 0.35rem;
}
.checkin-card.exito .checkin-estado-tag { color: #4ade80; }
.checkin-card.advertencia .checkin-estado-tag { color: #fb923c; }
.checkin-card.denegado .checkin-estado-tag { color: #f87171; }
.checkin-socio-nombre {
    font-size: 1.65rem;
    font-weight: 800;
    color: var(--color-text-main);
    margin-bottom: 0.3rem;
}
.checkin-meta {
    color: var(--color-text-muted);
    font-size: 0.92rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
</style>

<div class="panel-header">
    <div>
        <div class="eyebrow">
            <span class="punto"></span>
            Recepción y Molinete
        </div>
        <h1 class="titulo-pagina">Control de Acceso Rápido</h1>
        <p class="subt">Ingresá el DNI del socio para validar su cuota y registrar su asistencia al instante.</p>
    </div>
    <div class="acciones">
        <a href="exportar_csv.php?tipo=asistencias" class="btn btn-outline" title="Descargar reporte de asistencias en CSV">
            <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar Asistencias CSV
        </a>
    </div>
</div>

<!-- Buscador / Fichador por DNI -->
<div class="fila-dos-columnas" style="margin-bottom: 2rem;">
    <div class="panel-bloque">
        <div class="panel-bloque-cabecera">
            <h2 class="titulo-bloque">Fichar Ingreso por DNI</h2>
        </div>
        <form method="post" action="acceso.php" autocomplete="off">
            <div class="campo" style="margin-bottom: 1rem;">
                <label for="dni">Número de DNI del Socio</label>
                <div class="campo-icono">
                    <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M15 8h2M15 12h2M7 16h10"/></svg>
                    <input type="text" id="dni" name="dni" class="form-control con-icono"
                           placeholder="Ej: 40123456 (Presioná Enter)"
                           value="<?= htmlspecialchars($dniBuscado) ?>"
                           inputmode="numeric" pattern="[0-9]+" required autofocus
                           style="font-size: 1.15rem; font-weight: 600;">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                Validar y Registrar Presente
            </button>
        </form>
    </div>

    <div class="panel-bloque" style="justify-content: center;">
        <div class="card-metrica verde" style="width: 100%; border: none; background: transparent; padding: 0;">
            <div class="icono-metrica" style="width: 64px; height: 64px;">
                <svg class="icon" viewBox="0 0 24 24" style="width: 32px; height: 32px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
            </div>
            <div class="info-metrica">
                <div class="valor" style="font-size: 2.4rem;"><?= $totalHoy ?></div>
                <div class="etiqueta" style="font-size: 0.95rem;">Socios que asistieron hoy (<?= date('d/m/Y') ?>)</div>
            </div>
        </div>
    </div>
</div>

<!-- Resultado de la validación en tiempo real -->
<?php if ($resultadoCheckin): ?>
    <?php if ($resultadoCheckin['tipo'] === 'error' || $resultadoCheckin['tipo'] === 'no_encontrado'): ?>
        <div class="checkin-card denegado">
            <div>
                <div class="checkin-estado-tag"><?= htmlspecialchars($resultadoCheckin['titulo']) ?></div>
                <div style="font-size: 1.05rem; color: var(--color-text-main);"><?= htmlspecialchars($resultadoCheckin['mensaje']) ?></div>
            </div>
            <?php if ($resultadoCheckin['tipo'] === 'no_encontrado'): ?>
                <a href="clientes_form.php" class="btn btn-primary">Dar de Alta Socio</a>
            <?php endif; ?>
        </div>
    <?php elseif ($resultadoCheckin['tipo'] === 'exito' || $resultadoCheckin['tipo'] === 'advertencia'): ?>
        <?php $c = $resultadoCheckin['cliente']; ?>
        <div class="checkin-card <?= $resultadoCheckin['tipo'] ?>">
            <div>
                <div class="checkin-estado-tag"><?= htmlspecialchars($resultadoCheckin['titulo']) ?></div>
                <div class="checkin-socio-nombre"><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?></div>
                <div class="checkin-meta">
                    <span><strong>DNI:</strong> <?= htmlspecialchars($c['DNI']) ?></span>
                    <span><strong>Plan:</strong> <?= htmlspecialchars($c['tarifa_nombre'] ?? 'Sin plan') ?></span>
                    <span>
                        <strong>Vencimiento:</strong>
                        <?= !empty($c['ultimo_vencimiento']) ? htmlspecialchars(date('d/m/Y', strtotime($c['ultimo_vencimiento']))) . ' (' . $resultadoCheckin['dias_restantes'] . ' días)' : 'Sin pagos' ?>
                    </span>
                    <span><strong>Asistencias este mes:</strong> <?= (int)$resultadoCheckin['asistencias_mes'] ?></span>
                </div>
            </div>
            <div class="acciones">
                <a href="asistencias_cliente.php?id=<?= (int)$c['id_cliente'] ?>" class="btn btn-outline">Ver Historial</a>
                <?php if ($resultadoCheckin['tipo'] === 'advertencia'): ?>
                    <a href="pagos_form.php?id_cliente=<?= (int)$c['id_cliente'] ?>" class="btn btn-primary">Renovar Cuota</a>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($resultadoCheckin['tipo'] === 'denegado'): ?>
        <?php $c = $resultadoCheckin['cliente']; ?>
        <div class="checkin-card denegado">
            <div>
                <div class="checkin-estado-tag"><?= htmlspecialchars($resultadoCheckin['titulo']) ?></div>
                <div class="checkin-socio-nombre"><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?> (DNI <?= htmlspecialchars($c['DNI']) ?>)</div>
                <div style="color: #fca5a5; font-weight: 600; margin-top: 0.25rem;">
                    Motivo: <?= htmlspecialchars($resultadoCheckin['motivo']) ?>
                </div>
            </div>
            <div class="acciones" style="flex-wrap: wrap;">
                <a href="pagos_form.php?id_cliente=<?= (int)$c['id_cliente'] ?>" class="btn btn-primary">
                    Cobrar / Renovar Cuota
                </a>
                <form method="post" action="acceso.php" style="display:inline;">
                    <input type="hidden" name="dni" value="<?= htmlspecialchars($c['DNI']) ?>">
                    <input type="hidden" name="forzar_ingreso" value="1">
                    <button type="submit" class="btn btn-outline" onclick="return confirm('¿Permitir el ingreso excepcional aunque tenga cuota vencida o inactiva?');">
                        Autorizar Ingreso Excepcional
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Tabla de Ingresos del Día -->
<div class="panel-bloque">
    <div class="panel-bloque-cabecera">
        <h2 class="titulo-bloque">Ingresos Registrados Hoy</h2>
    </div>
    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>DNI</th>
                    <th>Socio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ingresosHoy)): ?>
                    <tr>
                        <td colspan="5" class="celda-vacia">Todavía no se registraron asistencias en el día de hoy.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ingresosHoy as $ing): ?>
                        <tr>
                            <td class="col-fecha"><strong><?= htmlspecialchars(date('H:i:s', strtotime($ing['fecha_hora']))) ?> hs</strong></td>
                            <td><?= htmlspecialchars($ing['DNI']) ?></td>
                            <td><strong><?= htmlspecialchars($ing['nombre'] . ' ' . $ing['apellido']) ?></strong></td>
                            <td><span class="badge activo"><?= htmlspecialchars($ing['Estado']) ?></span></td>
                            <td>
                                <a href="asistencias_cliente.php?id=<?= (int)$ing['id_cliente'] ?>" class="btn-accion btn-editar">Ver Ficha</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>

