<?php
require_once __DIR__ . '/includes/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}

$pdo = conectarDB();

$id = (int)($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';

if ($id > 0 && $id !== (int)$_SESSION['usuario_id'] && in_array($accion, ['baja', 'reactivar'], true)) {
    $activo = $accion === 'reactivar' ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE usuarios SET activo = ? WHERE id_usuario = ?');
    $stmt->execute([$activo, $id]);
}

$mensaje = $accion === 'baja' ? 'Usuario desactivado.' : 'Usuario reactivado.';
header('Location: usuarios.php?ok=' . urlencode($mensaje));
exit;
