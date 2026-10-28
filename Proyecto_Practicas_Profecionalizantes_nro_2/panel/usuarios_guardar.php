<?php
require_once __DIR__ . '/includes/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}

$pdo = conectarDB();

$idUsuario = (int)($_POST['id_usuario'] ?? 0);
$usuario = trim($_POST['usuario'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$idRol = (int)($_POST['id_rol'] ?? 0);
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

$redirectUrl = 'usuarios_form.php' . ($idUsuario > 0 ? '?id=' . $idUsuario : '');
$sep = ($idUsuario > 0 ? '&' : '?');

$esPropioUsuario = ($idUsuario > 0 && $idUsuario === (int)($_SESSION['usuario_id'] ?? 0));

// Si el usuario edita su propia cuenta, su rol NO puede ser modificado por él mismo.
// Se mantiene obligatoriamente el rol que ya tiene registrado en la base de datos (seguridad server-side).
if ($esPropioUsuario) {
    $stmtActual = $pdo->prepare('SELECT id_rol FROM usuarios WHERE id_usuario = ?');
    $stmtActual->execute([$idUsuario]);
    $rolActualBD = $stmtActual->fetchColumn();
    if ($rolActualBD !== false) {
        $idRol = (int)$rolActualBD;
    }
}

// Validar que el rol exista en la base de datos
$stmtRol = $pdo->prepare('SELECT COUNT(*) FROM roles WHERE id_rol = ?');
$stmtRol->execute([$idRol]);
$rolExiste = (int)$stmtRol->fetchColumn() > 0;

if ($usuario === '' || $nombre === '' || !$rolExiste) {
    header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('Completá todos los campos obligatorios.'));
    exit;
}

// Verificar nombre de usuario duplicado (excluyendo el usuario actual si es edición)
$stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = ? AND id_usuario != ?');
$stmt->execute([$usuario, $idUsuario]);
if ($stmt->fetchColumn() > 0) {
    header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('Ese nombre de usuario ya existe.'));
    exit;
}

if ($idUsuario > 0) {
    // Edición de usuario existente
    if ($password !== '') {
        if (strlen($password) < 6) {
            header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('La contraseña debe tener al menos 6 caracteres.'));
            exit;
        }
        if ($password !== $passwordConfirm) {
            header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('Las contraseñas no coinciden.'));
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET usuario = ?, nombre = ?, id_rol = ?, password = ? WHERE id_usuario = ?');
        $stmt->execute([$usuario, $nombre, $idRol, $hash, $idUsuario]);
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET usuario = ?, nombre = ?, id_rol = ? WHERE id_usuario = ?');
        $stmt->execute([$usuario, $nombre, $idRol, $idUsuario]);
    }

    // Si el usuario actualizó sus propios datos, sincronizar de inmediato la sesión activa
    if ($esPropioUsuario) {
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario'] = $usuario;
    }

    $mensaje = 'Usuario actualizado correctamente.';
} else {
    // Alta de nuevo usuario
    if (strlen($password) < 6) {
        header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('La contraseña debe tener al menos 6 caracteres.'));
        exit;
    }
    if ($password !== $passwordConfirm) {
        header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('Las contraseñas no coinciden.'));
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('
        INSERT INTO usuarios (usuario, password, nombre, id_rol, activo)
        VALUES (?, ?, ?, ?, 1)
    ');
    $stmt->execute([$usuario, $hash, $nombre, $idRol]);
    $mensaje = 'Usuario registrado correctamente.';
}

header('Location: usuarios.php?ok=' . urlencode($mensaje));
exit;
