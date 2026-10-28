<?php
require_once __DIR__ . '/../../config.php';

// Cabeceras para evitar que el navegador guarde en caché la página
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

if (empty($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}