<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: entrenadores.php');
    exit;
}

$pdo = conectarDB();

$idEliminar = (int)($_POST['id_entrenador'] ?? 0);

if ($idEliminar <= 0) {
    header('Location: entrenadores.php');
    exit;
}

// Verificar dependencias en tabla tarifas
$stmtTarifas = $pdo->prepare("SELECT COUNT(*) FROM tarifas WHERE id_entrenador = ?");
$stmtTarifas->execute([$idEliminar]);
$cantTarifas = $stmtTarifas->fetchColumn();

if ($cantTarifas > 0) {
    $mensajeError = "No se puede eliminar el entrenador porque tiene tarifas asociadas.";
    header('Location: entrenadores.php?error=' . urlencode($mensajeError));
    exit;
} else {
    try {
        $pdo->beginTransaction();

        // Eliminar primero las relaciones intermedias sin uso
        $stmtDelExD = $pdo->prepare("DELETE FROM entrenadoresxdisciplinas WHERE id_entrenador = ?");
        $stmtDelExD->execute([$idEliminar]);

        // Eliminar de la tabla principal
        $stmtDelEnt = $pdo->prepare("DELETE FROM entrenadores WHERE id_entrenador = ?");
        $stmtDelEnt->execute([$idEliminar]);

        $pdo->commit();
        header('Location: entrenadores.php?ok=' . urlencode("El entrenador ha sido eliminado correctamente."));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mensajeError = "No se pudo realizar la baja: " . $e->getMessage();
        header('Location: entrenadores.php?error=' . urlencode($mensajeError));
        exit;
    }
}
