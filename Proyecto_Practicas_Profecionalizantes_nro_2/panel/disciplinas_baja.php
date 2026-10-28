<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: disciplinas.php');
    exit;
}

$pdo = conectarDB();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: disciplinas.php');
    exit;
}

// 1. Verificar si existen entrenadores vinculados a esta disciplina en la tabla intermedia
$stmt = $pdo->prepare('SELECT COUNT(*) FROM entrenadoresxdisciplinas WHERE id_disciplina = ?');
$stmt->execute([$id]);
$vinculados = (int)$stmt->fetchColumn();

if ($vinculados > 0) {
    $mensajeError = "No se puede eliminar la disciplina porque tiene {$vinculados} entrenador(es) asignado(s). Desvinculá los entrenadores primero.";
    header('Location: disciplinas.php?error=' . urlencode($mensajeError));
    exit;
}

// 2. Si no tiene entrenadores vinculados, se procede con la eliminación
$stmt = $pdo->prepare('DELETE FROM disciplinas WHERE id_disciplina = ?');
$stmt->execute([$id]);

header('Location: disciplinas.php?ok=' . urlencode('Disciplina eliminada correctamente.'));
exit;