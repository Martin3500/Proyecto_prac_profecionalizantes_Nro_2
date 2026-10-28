<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$idEdit = (int)($_GET['id'] ?? $_GET['editar_id'] ?? 0);
$entrenadorEditar = null;
$disciplinasAsignadasIDs = [];

if ($idEdit > 0) {
    $stmtEdit = $pdo->prepare("SELECT * FROM entrenadores WHERE id_entrenador = ?");
    $stmtEdit->execute([$idEdit]);
    $entrenadorEditar = $stmtEdit->fetch();

    if ($entrenadorEditar) {
        $stmtExD = $pdo->prepare("SELECT id_disciplina FROM entrenadoresxdisciplinas WHERE id_entrenador = ?");
        $stmtExD->execute([$idEdit]);
        $disciplinasAsignadasIDs = $stmtExD->fetchAll(PDO::FETCH_COLUMN);
    } else {
        header('Location: entrenadores.php');
        exit;
    }
}

// Consulta del catálogo de disciplinas para el <select>
$todasDisciplinas = $pdo->query("SELECT id_disciplina, disciplina FROM disciplinas ORDER BY disciplina ASC")->fetchAll();

$tituloPagina = $entrenadorEditar ? 'Editar Entrenador' : 'Registrar Nuevo Entrenador';
$paginaActiva = 'entrenadores';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <div class="eyebrow">
            <span class="punto"></span>
            Módulo de Personal
        </div>
        <h1 class="titulo-pagina"><?= $entrenadorEditar ? 'Editar Entrenador #' . htmlspecialchars($entrenadorEditar['id_entrenador']) : 'Registrar Nuevo Entrenador' ?></h1>
        <p class="subt"><?= $entrenadorEditar ? 'Modificá los datos del entrenador y sus disciplinas asignadas.' : 'Alta y asignación de disciplinas.' ?></p>
    </div>
    <div>
        <a href="entrenadores.php" class="btn btn-outline">&larr; Volver a Entrenadores</a>
    </div>
</div>

<!-- Alertas de estado -->
<?php if (!empty($_GET['error'])): ?>
    <div class="alerta" style="margin-bottom: 1.5rem;">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
        <span><?= htmlspecialchars($_GET['error']) ?></span>
    </div>
<?php endif; ?>

<!-- FORMULARIO DE ALTA / MODIFICACIÓN -->
<div class="panel-bloque" style="margin-bottom: 2rem;">
    <div class="panel-bloque-cabecera">
        <h2 class="titulo-bloque">
            <?= $entrenadorEditar ? 'Editar Entrenador #' . htmlspecialchars($entrenadorEditar['id_entrenador']) : 'Registrar Nuevo Entrenador' ?>
        </h2>
    </div>

    <form action="entrenadores_guardar.php" method="POST">
        <input type="hidden" name="accion" value="<?= $entrenadorEditar ? 'editar' : 'crear' ?>">
        <?php if ($entrenadorEditar): ?>
            <input type="hidden" name="id_entrenador" value="<?= (int)$entrenadorEditar['id_entrenador'] ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="campo">
                <label for="dni">DNI (Obligatorio)</label>
                <input type="number" id="dni" name="dni" class="form-control" 
                       value="<?= htmlspecialchars($entrenadorEditar['DNI'] ?? '') ?>" required maxlength="8">
            </div>

            <div class="campo">
                <label for="nombre">Nombre (Obligatorio)</label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($entrenadorEditar['nombre'] ?? '') ?>" required>
            </div>

            <div class="campo">
                <label for="apellido">Apellido (Obligatorio)</label>
                <input type="text" id="apellido" name="apellido" class="form-control" 
                       value="<?= htmlspecialchars($entrenadorEditar['apellido'] ?? '') ?>" required>
            </div>

            <div class="campo">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($entrenadorEditar['email'] ?? '') ?>">
            </div>

            <div class="campo">
                <label for="telefono">Teléfono / Celular</label>
                <input type="text" id="telefono" name="telefono" class="form-control" 
                       value="<?= htmlspecialchars($entrenadorEditar['telefono'] ?? '') ?>">
            </div>

            <!-- SELECCIÓN DINÁMICA DE DISCIPLINAS CONSULTADAS A LA BD -->
            <div class="campo">
                <label for="disciplinas">Disciplinas a Cargo</label>
                <select id="disciplinas" name="disciplinas[]" class="form-control" multiple size="4" style="height: auto;">
                    <?php foreach ($todasDisciplinas as $disc): ?>
                        <?php 
                            $selected = in_array($disc['id_disciplina'], $disciplinasAsignadasIDs) ? 'selected' : '';
                        ?>
                        <option value="<?= (int)$disc['id_disciplina'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($disc['disciplina']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--color-text-muted); font-size: 0.78rem; display: block; margin-top: 0.3rem;">
                    Mantén presionada la tecla <kbd>Ctrl</kbd> (o <kbd>Cmd</kbd> en Mac) para elegir múltiples disciplinas.
                </small>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primary">
                <?= $entrenadorEditar ? 'Guardar Cambios' : 'Registrar Entrenador' ?>
            </button>
            <a href="entrenadores.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>
