<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: disciplinas.php');
    exit;
}

$pdo = conectarDB();

$idDisciplina = (int)($_POST['id_disciplina'] ?? 0);
$nombreDisciplina = trim($_POST['disciplina'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '') ?: null;

if ($nombreDisciplina === '') {
    $paramId = $idDisciplina ? '?id=' . $idDisciplina . '&' : '?';
    header('Location: disciplinas_form.php' . $paramId . 'error=' . urlencode('El nombre de la disciplina es obligatorio.'));
    exit;
}

if ($idDisciplina > 0) {
    $stmt = $pdo->prepare('UPDATE disciplinas SET disciplina = ?, descripcion = ? WHERE id_disciplina = ?');
    $stmt->execute([$nombreDisciplina, $descripcion, $idDisciplina]);
    $mensaje = 'Disciplina actualizada correctamente.';
} else {
    $stmt = $pdo->prepare('INSERT INTO disciplinas (disciplina, descripcion) VALUES (?, ?)');
    $stmt->execute([$nombreDisciplina, $descripcion]);
    $mensaje = 'Disciplina creada correctamente.';
}

header('Location: disciplinas.php?ok=' . urlencode($mensaje));
exit;