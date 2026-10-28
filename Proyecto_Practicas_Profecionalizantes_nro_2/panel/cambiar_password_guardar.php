<?php
require_once __DIR__ . '/includes/auth.php'; // Carga config.php y valida la sesión

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cambiar_password.php');
    exit;
}

$password_actual = trim($_POST['password_actual'] ?? '');
$password_nueva = trim($_POST['password_nueva'] ?? '');
$password_confirmar = trim($_POST['password_confirmar'] ?? '');
$usuario_id = $_SESSION['usuario_id']; // ID obtenido al autenticarse

if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
    header('Location: cambiar_password.php?error=vacio');
    exit;
}

if ($password_nueva !== $password_confirmar) {
    header('Location: cambiar_password.php?error=coincidencia');
    exit;
}

// Obtener la contraseña actual guardada en la base de datos
$pdo = conectarDB();
$stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($password_actual, $usuario['password'])) {
    header('Location: cambiar_password.php?error=pass_actual');
    exit;
}

// Generar el nuevo hash y actualizar en la base de datos
$nuevo_hash = password_hash($password_nueva, PASSWORD_BCRYPT);
$updateStmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");

if ($updateStmt->execute([$nuevo_hash, $usuario_id])) {
    header('Location: cambiar_password.php?mensaje=ok');
    exit;
} else {
    header('Location: cambiar_password.php?error=db');
    exit;
}