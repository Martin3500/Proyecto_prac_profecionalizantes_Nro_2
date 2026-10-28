<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pagos.php');
    exit;
}

$pdo = conectarDB();

$idCliente = (int)($_POST['id_cliente'] ?? 0);
$idTarifa = (int)($_POST['id_tarifa'] ?? 0);
$monto = (float)($_POST['monto'] ?? 0);
$metodoPago = trim($_POST['metodo_pago'] ?? 'Efectivo');
$metodosPermitidos = ['Efectivo', 'Transferencia', 'MercadoPago', 'Tarjeta de Débito', 'Tarjeta de Crédito'];
if (!in_array($metodoPago, $metodosPermitidos, true)) {
    $metodoPago = 'Efectivo';
}
$fechaVencimiento = trim($_POST['fecha_vencimiento'] ?? '');

if ($fechaVencimiento === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaVencimiento)) {
    $fechaVencimiento = date('Y-m-d', strtotime('+30 days'));
}

if ($idCliente <= 0) {
    header('Location: pagos_form.php?error=' . urlencode('Seleccioná un cliente válido.'));
    exit;
}

if ($idTarifa <= 0) {
    header('Location: pagos_form.php?error=' . urlencode('Seleccioná una tarifa válida.'));
    exit;
}

if ($monto <= 0) {
    header('Location: pagos_form.php?error=' . urlencode('El monto del pago debe ser mayor a cero.'));
    exit;
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('
        INSERT INTO historial_de_pagos (id_cliente, id_tarifa, monto, metodo_pago, fecha_pago, fecha_vencimiento)
        VALUES (?, ?, ?, ?, NOW(), ?)
    ');
    $stmt->execute([$idCliente, $idTarifa, $monto, $metodoPago, $fechaVencimiento]);

    // Si la fecha de vencimiento está vigente, asegurar que el cliente quede en estado Activo
    if ($fechaVencimiento >= date('Y-m-d')) {
        $stmtEstado = $pdo->prepare("
            UPDATE clientes 
            SET id_estado = (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo' LIMIT 1)
            WHERE id_cliente = ?
        ");
        $stmtEstado->execute([$idCliente]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: pagos_form.php?error=1');
    exit;
}

header('Location: pagos.php?ok=' . urlencode('Pago registrado correctamente y membresía actualizada.'));
exit;
