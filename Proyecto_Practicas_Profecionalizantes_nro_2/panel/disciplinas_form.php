<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$disciplina = [
    'id_disciplina' => '',
    'disciplina' => '',
    'descripcion' => '',
];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM disciplinas WHERE id_disciplina = ?');
    $stmt->execute([$id]);
    $encontrado = $stmt->fetch();
    if (!$encontrado) {
        header('Location: disciplinas.php');
        exit;
    }
    $disciplina = $encontrado;
}

$tituloPagina = $id > 0 ? 'Editar disciplina' : 'Nueva disciplina';
$paginaActiva = 'disciplinas';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina"><?= $id > 0 ? 'Editar disciplina' : 'Nueva disciplina' ?></h1>
        <div class="subt"><?= $id > 0 ? 'Actualizá el nombre o la descripción de la disciplina.' : 'Completá los datos para registrar una nueva disciplina.' ?></div>
    </div>
</div>

<?php if (!empty($_GET['error'])): ?>
    <div class="alerta">
        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        <span><?= htmlspecialchars($_GET['error']) ?></span>
    </div>
<?php endif; ?>

<div class="panel-bloque">
    <form method="post" action="disciplinas_guardar.php">
        <input type="hidden" name="id_disciplina" value="<?= (int)$disciplina['id_disciplina'] ?>">
        
        <div class="form-grid">
            <div class="campo">
                <label for="disciplina">Nombre de la disciplina</label>
                <input type="text" id="disciplina" name="disciplina" class="form-control" required
                       placeholder="Ej: CrossFit, Yoga, Spinning" value="<?= htmlspecialchars($disciplina['disciplina']) ?>">
            </div>

            <div class="campo campo-full" style="grid-column: 1 / -1;">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                          placeholder="Breve detalle de la disciplina..."><?= htmlspecialchars($disciplina['descripcion'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="disciplinas.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>