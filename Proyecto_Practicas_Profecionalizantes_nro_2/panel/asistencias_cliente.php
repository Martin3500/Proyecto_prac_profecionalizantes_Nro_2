<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/paginacion.php';
$pdo = conectarDB();

$idCliente = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idCliente <= 0) {
    header('Location: clientes.php');
    exit;
}

// 1. Obtener información del cliente
$stmtCliente = $pdo->prepare("
    SELECT c.*, e.Estado 
    FROM clientes c 
    JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes 
    WHERE c.id_cliente = ?
");
$stmtCliente->execute([$idCliente]);
$cliente = $stmtCliente->fetch();

if (!$cliente) {
    header('Location: clientes.php');
    exit;
}

$mensajeOk = $_GET['ok'] ?? '';
$mensajeError = '';

// 2. Procesamiento de acciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Eliminar registro de asistencia
    if ($accion === 'eliminar') {
        $idAsistencia = (int)($_POST['id_asistencia'] ?? 0);
        if ($idAsistencia > 0) {
            $stmtDel = $pdo->prepare("DELETE FROM historia_de_asistencia WHERE id_asistencia = ? AND id_cliente = ?");
            $stmtDel->execute([$idAsistencia, $idCliente]);

            header("Location: asistencias_cliente.php?id={$idCliente}&ok=" . urlencode("Asistencia eliminada correctamente."));
            exit;
        }
    }

    // Agregar nuevo registro de asistencia
    elseif ($accion === 'agregar') {
        $fechaHora = trim($_POST['fecha_hora'] ?? '');
        $asistio = isset($_POST['asistio']) ? (int)$_POST['asistio'] : 1;

        if (!empty($fechaHora)) {
            // Formatear datetime-local a DATETIME de MySQL
            $fechaFormateada = date('Y-m-d H:i:s', strtotime($fechaHora));

            $stmtIns = $pdo->prepare("
                INSERT INTO historia_de_asistencia (id_cliente, fecha_hora, asistio) 
                VALUES (?, ?, ?)
            ");
            $stmtIns->execute([$idCliente, $fechaFormateada, $asistio]);

            header("Location: asistencias_cliente.php?id={$idCliente}&ok=" . urlencode("Asistencia registrada correctamente."));
            exit;
        } else {
            $mensajeError = "Por favor, selecciona una fecha y hora válida.";
        }
    }
}

// 4. Conteo real de asistencias (solo Presentes) y total de registros para paginación
$stmtPresentes = $pdo->prepare("SELECT COUNT(*) FROM historia_de_asistencia WHERE id_cliente = ? AND asistio = 1");
$stmtPresentes->execute([$idCliente]);
$totalPresentes = (int)$stmtPresentes->fetchColumn();

$stmtPresentesMes = $pdo->prepare("
    SELECT COUNT(*) FROM historia_de_asistencia 
    WHERE id_cliente = ? AND asistio = 1 
      AND MONTH(fecha_hora) = MONTH(CURDATE()) AND YEAR(fecha_hora) = YEAR(CURDATE())
");
$stmtPresentesMes->execute([$idCliente]);
$presentesEsteMes = (int)$stmtPresentesMes->fetchColumn();

$stmtTotalReg = $pdo->prepare("SELECT COUNT(*) FROM historia_de_asistencia WHERE id_cliente = ?");
$stmtTotalReg->execute([$idCliente]);
$totalRegistros = (int)$stmtTotalReg->fetchColumn();

$paginacion = calcularPaginacion($totalRegistros, 15);

// Obtener el historial de asistencias paginado
$stmtAsistencias = $pdo->prepare("
    SELECT id_asistencia, fecha_hora, asistio 
    FROM historia_de_asistencia 
    WHERE id_cliente = ? 
    ORDER BY fecha_hora DESC
    LIMIT {$paginacion['por_pagina']} OFFSET {$paginacion['offset']}
");
$stmtAsistencias->execute([$idCliente]);
$asistencias = $stmtAsistencias->fetchAll();

$tituloPagina = 'Historial de Asistencias';
$paginaActiva = 'clientes';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina">Historial de Asistencias</h1>
        <div class="subt">
            Cliente: <strong><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></strong> 
            (DNI: <?= htmlspecialchars($cliente['DNI']) ?>)
        </div>
    </div>
    <div class="acciones" style="flex-wrap: wrap;">
        <a href="exportar_csv.php?tipo=asistencias&id_cliente=<?= (int)$idCliente ?>" class="btn btn-outline" title="Exportar historial de asistencias en CSV">
            <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar CSV
        </a>
        <a href="clientes.php" class="btn btn-outline">
            <svg class="icon" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
            Volver a Clientes
        </a>
    </div>
</div>

<?php if (!empty($mensajeOk)): ?>
    <div class="alerta ok" style="margin-bottom: 1.5rem;">
        <svg class="icon" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
        <span><?= htmlspecialchars($mensajeOk) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="alerta" style="margin-bottom: 1.5rem;">
        <svg class="icon" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
        <span><?= htmlspecialchars($mensajeError) ?></span>
    </div>
<?php endif; ?>

<!-- Bloque de Registro y Resumen -->
<div class="fila-dos-columnas" style="margin-bottom: 2rem;">
    <!-- Formulario Agregar Asistencia -->
    <div class="panel-bloque">
        <div class="panel-bloque-cabecera">
            <h2 class="titulo-bloque">Registrar Nueva Asistencia</h2>
        </div>
        <form action="asistencias_cliente.php?id=<?= $idCliente ?>" method="POST">
            <input type="hidden" name="accion" value="agregar">
            
            <div class="campo" style="margin-bottom: 1rem;">
                <label for="fecha_hora">Fecha y Hora</label>
                <input 
                    type="datetime-local" 
                    id="fecha_hora" 
                    name="fecha_hora" 
                    class="form-control" 
                    value="<?= date('Y-m-d\TH:i') ?>" 
                    required
                >
            </div>

            <div class="campo" style="margin-bottom: 1.25rem;">
                <label for="asistio">Estado de Asistencia</label>
                <select id="asistio" name="asistio" class="form-control">
                    <option value="1" selected>Presente</option>
                    <option value="0">Ausente</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Registrar Asistencia
            </button>
        </form>
    </div>

    <!-- Métrica Total -->
    <div class="panel-bloque" style="justify-content: center; gap: 1.25rem;">
        <div class="card-metrica verde" style="width: 100%; border: none; background: transparent; padding: 0;">
            <div class="icono-metrica" style="width: 64px; height: 64px;">
                <svg class="icon" viewBox="0 0 24 24" style="width: 32px; height: 32px;"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <div class="info-metrica">
                <div class="valor" style="font-size: 2.2rem;"><?= $totalPresentes ?></div>
                <div class="etiqueta" style="font-size: 0.95rem;">Total Histórico de Presentes</div>
            </div>
        </div>
        <div style="border-top: 1px solid var(--color-border); padding-top: 1rem; color: var(--color-text-muted); font-size: 0.9rem;">
            Asistencias en el mes actual: <strong style="color: var(--color-text-main);"><?= $presentesEsteMes ?></strong>
        </div>
    </div>
</div>

<!-- Tabla de Historial -->
<div class="panel-bloque">
    <div class="panel-bloque-cabecera" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h2 class="titulo-bloque">Historial de Registros</h2>
        <div class="badge-limite" title="Listado paginado de a <?= $paginacion['por_pagina'] ?> registros por página">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Página <?= $paginacion['pagina'] ?> de <?= $paginacion['total_paginas'] ?></span>
        </div>
    </div>

    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th># ID</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($asistencias)): ?>
                    <tr>
                        <td colspan="5" class="celda-vacia">Este cliente no registra asistencias en el sistema aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($asistencias as $a): ?>
                        <?php $fechaHora = new DateTime($a['fecha_hora']); ?>
                        <tr>
                            <td>#<?= (int)$a['id_asistencia'] ?></td>
                            <td class="col-fecha"><?= $fechaHora->format('d/m/Y') ?></td>
                            <td><?= $fechaHora->format('H:i:s') ?> hs</td>
                            <td>
                                <?php if ((int)$a['asistio'] === 1): ?>
                                    <span class="badge activo">Presente</span>
                                <?php else: ?>
                                    <span class="badge inactivo">Ausente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="acciones">
                                    <form action="asistencias_cliente.php?id=<?= $idCliente ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este registro de asistencia?');" style="display:inline;">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_asistencia" value="<?= (int)$a['id_asistencia'] ?>">
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