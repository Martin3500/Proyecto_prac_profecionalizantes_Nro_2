<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: entrenadores.php');
    exit;
}

$pdo = conectarDB();

$accion = $_POST['accion'] ?? '';
$idEntrenador = (int)($_POST['id_entrenador'] ?? 0);
$dni = trim($_POST['dni'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$disciplinas = $_POST['disciplinas'] ?? []; // Array de IDs de disciplinas

$redirectForm = 'entrenadores_form.php' . ($idEntrenador > 0 ? '?id=' . $idEntrenador : '');
$sep = ($idEntrenador > 0 ? '&' : '?');

// --- ACCIÓN: CREAR ENTRENADOR ---
if ($accion === 'crear' || ($accion === '' && $idEntrenador <= 0)) {
    if (empty($dni) || empty($nombre) || empty($apellido)) {
        $mensajeError = "Por favor complete los campos obligatorios (DNI, Nombre y Apellido).";
        header('Location: ' . $redirectForm . $sep . 'error=' . urlencode($mensajeError));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Insertar en tabla entrenadores
        $stmtInsert = $pdo->prepare("
            INSERT INTO entrenadores (DNI, nombre, apellido, email, telefono) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtInsert->execute([$dni, $nombre, $apellido, $email, $telefono]);
        $idEntrenadorNuevo = $pdo->lastInsertId();

        // 2. Vincular disciplinas elegidas en el <select> en entrenadoresxdisciplinas
        if (!empty($disciplinas) && is_array($disciplinas)) {
            $stmtExD = $pdo->prepare("
                INSERT INTO entrenadoresxdisciplinas (id_entrenador, id_disciplina) 
                VALUES (?, ?)
            ");
            foreach ($disciplinas as $idDisc) {
                $stmtExD->execute([$idEntrenadorNuevo, (int)$idDisc]);
            }
        }

        $pdo->commit();
        header('Location: entrenadores.php?ok=' . urlencode("Entrenador guardado correctamente."));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->getCode() == 23000) {
            $mensajeError = "El DNI ingresado ya pertenece a otro entrenador.";
        } else {
            $mensajeError = "Error al guardar el entrenador: " . $e->getMessage();
        }
        header('Location: ' . $redirectForm . $sep . 'error=' . urlencode($mensajeError));
        exit;
    }
}

// --- ACCIÓN: EDITAR ENTRENADOR ---
elseif ($accion === 'editar' || $idEntrenador > 0) {
    if ($idEntrenador <= 0 || empty($dni) || empty($nombre) || empty($apellido)) {
        $mensajeError = "Datos inválidos o faltan campos obligatorios.";
        header('Location: ' . $redirectForm . $sep . 'error=' . urlencode($mensajeError));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Actualizar datos base del entrenador
        $stmtUpdate = $pdo->prepare("
            UPDATE entrenadores 
            SET DNI = ?, nombre = ?, apellido = ?, email = ?, telefono = ? 
            WHERE id_entrenador = ?
        ");
        $stmtUpdate->execute([$dni, $nombre, $apellido, $email, $telefono, $idEntrenador]);

        // 2. Re-asignar disciplinas en entrenadoresxdisciplinas
        $stmtDelExD = $pdo->prepare("
            DELETE FROM entrenadoresxdisciplinas 
            WHERE id_entrenador = ?
        ");
        $stmtDelExD->execute([$idEntrenador]);

        $existentes = [];

        $stmtInsertExD = $pdo->prepare("
            INSERT INTO entrenadoresxdisciplinas (id_entrenador, id_disciplina) 
            VALUES (?, ?)
        ");
        foreach ($disciplinas as $idDisc) {
            if (!in_array($idDisc, $existentes)) {
                $stmtInsertExD->execute([$idEntrenador, (int)$idDisc]);
            }
        }

        $pdo->commit();
        header('Location: entrenadores.php?ok=' . urlencode("Datos del entrenador actualizados con éxito."));
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mensajeError = "Error al actualizar: " . $e->getMessage();
        header('Location: ' . $redirectForm . $sep . 'error=' . urlencode($mensajeError));
        exit;
    }
} else {
    header('Location: entrenadores.php');
    exit;
}
