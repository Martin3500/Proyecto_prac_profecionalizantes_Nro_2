<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/paginacion.php';
$pdo = conectarDB();

$mensajeOk = '';
$mensajeError = '';

// Entrenador seleccionado por URL para filtrar o preseleccionar
$idEntrenadorFiltro = isset($_GET['id_entrenador']) ? (int)$_GET['id_entrenador'] : 0;

// Tarifa a editar si se pasa ID por GET
$tarifaEditar = null;
if (isset($_GET['editar_id'])) {
    $idTarifaEdit = (int)$_GET['editar_id'];
    $stmtEdit = $pdo->prepare("SELECT * FROM tarifas WHERE id_tarifa = ?");
    $stmtEdit->execute([$idTarifaEdit]);
    $tarifaEditar = $stmtEdit->fetch();
    if ($tarifaEditar) {
        $idEntrenadorFiltro = $tarifaEditar['id_entrenador'];
    }
}

// --------------------------------------------------------------------------
// PROCESAMIENTO DE FORMULARIOS (POST)
// --------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // --- ACCIÓN: CREAR TARIFA ---
    if ($accion === 'crear') {
        $idEntrenador = (int)($_POST['id_entrenador'] ?? 0);
        $nombreTarifa = trim($_POST['nombre'] ?? '');
        $monto = (float)($_POST['monto'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($idEntrenador <= 0 || empty($nombreTarifa) || $monto <= 0) {
            $mensajeError = "Seleccione un entrenador, ingrese el nombre de la tarifa y un monto válido mayor a 0.";
        } else {
            try {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO tarifas (id_entrenador, nombre, monto, descripcion) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmtInsert->execute([$idEntrenador, $nombreTarifa, $monto, $descripcion]);

                $mensajeOk = "Tarifa registrada correctamente.";
                $idEntrenadorFiltro = $idEntrenador; // Mantener filtro activo
            } catch (PDOException $e) {
                $mensajeError = "Error al guardar la tarifa: " . $e->getMessage();
            }
        }
    }

    // --- ACCIÓN: EDITAR TARIFA ---
    elseif ($accion === 'editar') {
        $idTarifa = (int)($_POST['id_tarifa'] ?? 0);
        $idEntrenador = (int)($_POST['id_entrenador'] ?? 0);
        $nombreTarifa = trim($_POST['nombre'] ?? '');
        $monto = (float)($_POST['monto'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($idTarifa <= 0 || $idEntrenador <= 0 || empty($nombreTarifa) || $monto <= 0) {
            $mensajeError = "Datos inválidos para actualizar la tarifa.";
        } else {
            try {
                $stmtUpdate = $pdo->prepare("
                    UPDATE tarifas 
                    SET id_entrenador = ?, nombre = ?, monto = ?, descripcion = ? 
                    WHERE id_tarifa = ?
                ");
                $stmtUpdate->execute([$idEntrenador, $nombreTarifa, $monto, $descripcion, $idTarifa]);

                $mensajeOk = "Tarifa actualizada con éxito.";
                $tarifaEditar = null;
                $idEntrenadorFiltro = $idEntrenador;
            } catch (PDOException $e) {
                $mensajeError = "Error al actualizar la tarifa: " . $e->getMessage();
            }
        }
    }

    // --- ACCIÓN: ELIMINAR TARIFA ---
    elseif ($accion === 'eliminar') {
        $idEliminar = (int)($_POST['id_tarifa'] ?? 0);

        if ($idEliminar > 0) {
            // Verificar si la tarifa está vinculada a un pago guardado
            $stmtPagos = $pdo->prepare("SELECT COUNT(*) FROM historial_de_pagos WHERE id_tarifa = ?");
            $stmtPagos->execute([$idEliminar]);
            
            if ($stmtPagos->fetchColumn() > 0) {
                $mensajeError = "No se puede eliminar la tarifa porque ya registra pagos asociados en el historial.";
            } else {
                try {
                    $stmtDel = $pdo->prepare("DELETE FROM tarifas WHERE id_tarifa = ?");
                    $stmtDel->execute([$idEliminar]);
                    $mensajeOk = "La tarifa ha sido eliminada.";
                } catch (PDOException $e) {
                    $mensajeError = "No se pudo eliminar la tarifa: " . $e->getMessage();
                }
            }
        }
    }
}

// --------------------------------------------------------------------------
// CONSULTAS DE DATOS
// --------------------------------------------------------------------------

// 1. Obtener lista de entrenadores para el selector
$listaEntrenadores = $pdo->query("
    SELECT id_entrenador, nombre, apellido, DNI 
    FROM entrenadores 
    ORDER BY apellido ASC, nombre ASC
")->fetchAll();

// 2. Obtener lista de tarifas (filtrada si hay un entrenador seleccionado)
$sqlTarifas = "
    SELECT 
        t.id_tarifa,
        t.nombre AS tarifa_nombre,
        t.monto,
        t.descripcion,
        e.id_entrenador,
        e.nombre AS e_nombre,
        e.apellido AS e_apellido,
        e.DNI
    FROM tarifas t
    INNER JOIN entrenadores e ON t.id_entrenador = e.id_entrenador
";

$paramsTarifas = [];
$whereTarifas = '';
if ($idEntrenadorFiltro > 0) {
    $whereTarifas = " WHERE e.id_entrenador = ?";
    $paramsTarifas[] = $idEntrenadorFiltro;
}

$stmtCountTarifas = $pdo->prepare("SELECT COUNT(*) FROM tarifas t INNER JOIN entrenadores e ON t.id_entrenador = e.id_entrenador" . $whereTarifas);
$stmtCountTarifas->execute($paramsTarifas);
$totalTarifas = (int)$stmtCountTarifas->fetchColumn();

$paginacion = calcularPaginacion($totalTarifas, 15);

$sqlTarifas .= $whereTarifas . " ORDER BY t.id_tarifa DESC LIMIT {$paginacion['por_pagina']} OFFSET {$paginacion['offset']}";

$stmtTarifas = $pdo->prepare($sqlTarifas);
$stmtTarifas->execute($paramsTarifas);
$listaTarifas = $stmtTarifas->fetchAll();

$tituloPagina = 'Gestión de Tarifas de Entrenadores';
$paginaActiva = 'tarifas';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <div class="eyebrow">
            <span class="punto"></span>
            Módulo de Honorarios
        </div>
        <h1 class="titulo-pagina">Tarifas de Entrenadores</h1>
        <p class="subt">Asignación y control de tarifarios y montos por cada entrenador.</p>
    </div>
    <div>
        <a href="entrenadores.php" class="btn btn-outline">
            &larr; Volver a Entrenadores
        </a>
    </div>
</div>

<!-- Alertas -->
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

<!-- FORMULARIO DE REGISTRO / EDICIÓN -->
<div class="panel-bloque" style="margin-bottom: 2rem;">
    <div class="panel-bloque-cabecera">
        <h2 class="titulo-bloque">
            <?= $tarifaEditar ? 'Editar Tarifa #' . htmlspecialchars($tarifaEditar['id_tarifa']) : 'Asignar Nueva Tarifa' ?>
        </h2>
    </div>

    <form action="tarifas_entrenadores.php" method="POST">
        <input type="hidden" name="accion" value="<?= $tarifaEditar ? 'editar' : 'crear' ?>">
        <?php if ($tarifaEditar): ?>
            <input type="hidden" name="id_tarifa" value="<?= (int)$tarifaEditar['id_tarifa'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="campo">
                <label for="id_entrenador">Entrenador (Obligatorio)</label>
                <select id="id_entrenador" name="id_entrenador" class="form-control" required>
                    <option value="">-- Seleccionar Entrenador --</option>
                    <?php foreach ($listaEntrenadores as $ent): ?>
                        <?php 
                            $selectedId = $tarifaEditar ? $tarifaEditar['id_entrenador'] : $idEntrenadorFiltro;
                            $selected = ($ent['id_entrenador'] == $selectedId) ? 'selected' : '';
                        ?>
                        <option value="<?= (int)$ent['id_entrenador'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($ent['apellido'] . ', ' . $ent['nombre'] . ' (DNI: ' . $ent['DNI'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="nombre">Nombre de la Tarifa / Plan (Obligatorio)</label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($tarifaEditar['nombre'] ?? '') ?>" 
                       placeholder="Ej: Pase Libre, Clase Particular, Tarifa Estándar" required>
            </div>

            <div class="campo">
                <label for="monto">Monto / Valor ($ Obligatorio)</label>
                <input type="number" step="0.01" min="0" id="monto" name="monto" class="form-control" 
                       value="<?= htmlspecialchars($tarifaEditar['monto'] ?? '') ?>" placeholder="0.00" required>
            </div>

            <div class="campo" style="grid-column: span 3;">
                <label for="descripcion">Descripción Adicional (Opcional)</label>
                <input type="text" id="descripcion" name="descripcion" class="form-control" 
                       value="<?= htmlspecialchars($tarifaEditar['descripcion'] ?? '') ?>" 
                       placeholder="Detalles sobre qué incluye la tarifa o condiciones particulares">
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primary">
                <?= $tarifaEditar ? 'Guardar Cambios' : 'Registrar Tarifa' ?>
            </button>
            <?php if ($tarifaEditar || $idEntrenadorFiltro > 0): ?>
                <a href="tarifas_entrenadores.php" class="btn btn-outline">Ver Todas las Tarifas</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABLA DE TARIFAS -->
<div class="panel-bloque">
    <div class="panel-bloque-cabecera" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="titulo-bloque">
            <?= $idEntrenadorFiltro > 0 ? 'Tarifas del Entrenador Seleccionado' : 'Historial General de Tarifas' ?>
        </h2>
        <?php if ($idEntrenadorFiltro > 0): ?>
            <a href="tarifas_entrenadores.php" class="badge" style="text-decoration: none; padding: 0.4rem 0.8rem;">
                Quitar Filtro ✕
            </a>
        <?php endif; ?>
    </div>

    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Entrenador</th>
                    <th>Nombre de la Tarifa</th>
                    <th>Monto</th>
                    <th>Descripción</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listaTarifas)): ?>
                    <tr>
                        <td colspan="5" class="celda-vacia">No se encontraron tarifas registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listaTarifas as $t): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($t['e_apellido'] . ', ' . $t['e_nombre']) ?></strong>
                                <div style="font-size: 0.8rem; color: var(--color-text-muted);">DNI: <?= htmlspecialchars($t['DNI']) ?></div>
                            </td>
                            <td><strong><?= htmlspecialchars($t['tarifa_nombre']) ?></strong></td>
                            <td><strong style="color: var(--color-primary);">$<?= number_format((float)$t['monto'], 2, ',', '.') ?></strong></td>
                            <td><?= htmlspecialchars($t['descripcion'] ?: 'Sin descripción') ?></td>
                            <td style="text-align: right;">
                                <div class="acciones" style="justify-content: flex-end;">
                                    <a href="tarifas_entrenadores.php?editar_id=<?= (int)$t['id_tarifa'] ?>" class="btn-accion btn-editar">
                                        Editar
                                    </a>
                                    <form action="tarifas_entrenadores.php" method="POST" onsubmit="return confirm('¿Confirma eliminar esta tarifa?');" style="display:inline;">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_tarifa" value="<?= (int)$t['id_tarifa'] ?>">
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