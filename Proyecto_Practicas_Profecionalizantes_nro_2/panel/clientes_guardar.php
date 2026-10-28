<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: clientes.php');
    exit;
}

$pdo = conectarDB();

$idCliente = (int)($_POST['id_cliente'] ?? 0);
$dni = trim($_POST['DNI'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$rawEmail = trim($_POST['email'] ?? '');
$email = $rawEmail !== '' ? $rawEmail : null;
$telefono = trim($_POST['telefono'] ?? '') ?: null;
$idEstado = (int)($_POST['id_estado'] ?? 0);

$redirectUrl = 'clientes_form.php' . ($idCliente > 0 ? '?id=' . $idCliente : '');
$sep = ($idCliente > 0 ? '&' : '?');

if ($dni === '' || !ctype_digit($dni) || (int)$dni <= 0) {
    header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('Ingresá un DNI válido (solo números positivos).'));
    exit;
}

if ($nombre === '' || $apellido === '' || $idEstado <= 0) {
    header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('Completá todos los campos obligatorios.'));
    exit;
}

if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('El correo electrónico no tiene un formato válido.'));
    exit;
}

// Verificar DNI duplicado
$stmt = $pdo->prepare('SELECT COUNT(*) FROM clientes WHERE DNI = ? AND id_cliente != ?');
$stmt->execute([$dni, $idCliente]);
if ($stmt->fetchColumn() > 0) {
    header('Location: ' . $redirectUrl . $sep . 'error=' . urlencode('El DNI ingresado ya pertenece a otro cliente.'));
    exit;
}

if ($idCliente > 0) {
    $stmt = $pdo->prepare('
        UPDATE clientes SET DNI = ?, nombre = ?, apellido = ?, email = ?, telefono = ?, id_estado = ?
        WHERE id_cliente = ?
    ');
    $stmt->execute([$dni, $nombre, $apellido, $email, $telefono, $idEstado, $idCliente]);
    $mensaje = 'Cliente actualizado correctamente.';
} else {
    $stmt = $pdo->prepare('
        INSERT INTO clientes (DNI, nombre, apellido, email, telefono, id_estado)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$dni, $nombre, $apellido, $email, $telefono, $idEstado]);
    $mensaje = 'Cliente creado correctamente.';
}

header('Location: clientes.php?ok=' . urlencode($mensaje));
exit;
