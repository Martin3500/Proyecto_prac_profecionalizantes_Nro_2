<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: clientes.php');
    exit;
}

$pdo = conectarDB();

$id = (int)($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';

if ($id > 0 && in_array($accion, ['baja', 'reactivar'], true)) {
    $estadoDestino = $accion === 'baja' ? 'Inactivo' : 'Activo';

    $stmt = $pdo->prepare('SELECT ID_est_clientes FROM estado_cliente WHERE Estado = ?');
    $stmt->execute([$estadoDestino]);
    $idEstado = $stmt->fetchColumn();

    if ($idEstado) {
        $stmt = $pdo->prepare('UPDATE clientes SET id_estado = ? WHERE id_cliente = ?');
        $stmt->execute([$idEstado, $id]);
    }
}

$mensaje = $accion === 'baja' ? 'Cliente dado de baja.' : 'Cliente reactivado.';
header('Location: clientes.php?ok=' . urlencode($mensaje));
exit;
