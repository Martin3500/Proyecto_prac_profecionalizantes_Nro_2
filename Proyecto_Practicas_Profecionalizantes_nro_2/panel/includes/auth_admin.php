<?php
require_once __DIR__ . '/auth.php';

if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    header('Location: dashboard.php');
    exit;
}
