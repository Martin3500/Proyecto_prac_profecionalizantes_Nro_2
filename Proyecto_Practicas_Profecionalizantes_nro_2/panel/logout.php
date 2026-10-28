<?php
require_once __DIR__ . '/../config.php';

// 1. Vaciar todas las variables de sesión
$_SESSION = array();

// 2. Borrar la cookie de sesión en el navegador si está activa
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al formulario de ingreso
header('Location: ../login.php');
exit;