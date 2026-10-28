<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: panel/dashboard.php');
    exit;
}

$error = '';
$maxIntentos = 3;
$tiempoBloqueoSegundos = 15 * 60; // 15 minutos

if (!isset($_SESSION['login_intentos'])) {
    $_SESSION['login_intentos'] = 0;
}

// Verificar si existe bloqueo activo
$esBloqueado = false;
if (!empty($_SESSION['login_bloqueado_hasta'])) {
    if (time() < $_SESSION['login_bloqueado_hasta']) {
        $esBloqueado = true;
        $segundosRestantes = $_SESSION['login_bloqueado_hasta'] - time();
        $minutosRestantes = ceil($segundosRestantes / 60);
        $error = "Has superado el límite de 3 intentos fallidos. Acceso bloqueado temporalmente (esperá {$minutosRestantes} minuto" . ($minutosRestantes > 1 ? 's' : '') . ").";
    } else {
        // El tiempo transcurrió, desbloquear
        unset($_SESSION['login_bloqueado_hasta']);
        $_SESSION['login_intentos'] = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$esBloqueado) {
        $usuario = trim($_POST['usuario'] ?? '');
        $clave = $_POST['password'] ?? '';

        if ($usuario === '' || $clave === '') {
            $error = 'Completá usuario y contraseña.';
        } else {
            $pdo = conectarDB();
            $stmt = $pdo->prepare('
                SELECT u.*, r.nombre AS rol 
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.usuario = ? AND u.activo = 1
            ');
            $stmt->execute([$usuario]);
            $fila = $stmt->fetch();

            if ($fila && password_verify($clave, $fila['password'])) {
                $_SESSION['login_intentos'] = 0;
                unset($_SESSION['login_bloqueado_hasta']);

                $_SESSION['usuario_id'] = $fila['id_usuario'];
                $_SESSION['usuario'] = $fila['usuario'];
                $_SESSION['usuario_nombre'] = $fila['nombre'];
                $_SESSION['usuario_rol'] = $fila['rol'];
                header('Location: panel/dashboard.php');
                exit;
            } else {
                $_SESSION['login_intentos']++;
                if ($_SESSION['login_intentos'] >= $maxIntentos) {
                    $_SESSION['login_bloqueado_hasta'] = time() + $tiempoBloqueoSegundos;
                    $esBloqueado = true;
                    $error = 'Has superado el límite de 3 intentos fallidos. Tu acceso ha sido bloqueado por 15 minutos.';
                } else {
                    $restantes = $maxIntentos - $_SESSION['login_intentos'];
                    $error = "Usuario o contraseña incorrectos. Te quedan {$restantes} intento" . ($restantes > 1 ? 's' : '') . '.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - <?= htmlspecialchars(GYM_NOMBRE) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
    <link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
</head>
<body class="login-body">
    <div class="login-wrap">
        <!-- Panel Izquierdo: Branding y Widget -->
        <div class="login-panel-marca">
            <div class="login-texto">
                <div class="marca">
                    <img src="assets/img/logo.svg" alt="" class="logo-img"> 
                    <span><?= htmlspecialchars(GYM_NOMBRE) ?></span>
                </div>
                <h2>Gestioná tu gimnasio desde un solo lugar</h2>
                <p>Clientes, pagos y actividad al día, todo centralizado en el panel administrativo.</p>
            </div>

            <div class="widget-clima">
                <div>
                    <div class="wc-hora" id="wcHora">--:--:--</div>
                    <div class="wc-fecha" id="wcFecha">--</div>
                </div>
                <div class="wc-separador"></div>
                <div class="wc-clima-fila">
                    <span class="wc-icono" id="wcIcono"></span>
                    <div>
                        <div class="wc-ciudad" id="wcCiudad">Ubicando...</div>
                        <div class="wc-desc" id="wcDesc">-- · --°C</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Formulario de Login -->
        <div class="login-panel-form">
            <div class="login-card">
                <a href="index.php" class="volver">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Volver al sitio
                </a>
                
                <h1>Iniciar sesión</h1>

                <?php if ($error): ?>
                    <div class="alerta" role="alert">
                        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="login.php" autocomplete="on">
                    <div class="campo">
                        <label for="usuario" class="sr-only">Usuario</label>
                        <div class="campo-icono">
                            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c1.1-4 3.8-6 7.5-6s6.4 2 7.5 6"/></svg>
                            <input type="text" id="usuario" name="usuario" placeholder="Usuario" required autofocus value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="campo">
                        <label for="password" class="sr-only">Contraseña</label>
                        <div class="campo-icono">
                            <svg class="icon" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2.2"/><path d="M8 11V7.5a4 4 0 0 1 8 0V11"/></svg>
                            <input type="password" id="password" name="password" placeholder="Contraseña" required>
                            <button type="button" class="alt-clave" id="altClave" aria-label="Mostrar contraseña">
                                <svg class="icon" viewBox="0 0 24 24" id="iconoOjo"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="campo-check">
                        <label>
                            <input type="checkbox" id="recordarUsuario" name="recordar_usuario">
                            Recordar usuario
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" <?= $esBloqueado ? 'disabled' : '' ?>>Ingresar</button>
                </form>
            </div>
        </div>
    </div>

<script>
(function () {
    var elHora = document.getElementById('wcHora');
    var elFecha = document.getElementById('wcFecha');
    var elIcono = document.getElementById('wcIcono');
    var elCiudad = document.getElementById('wcCiudad');
    var elDesc = document.getElementById('wcDesc');

    function capitalizar(texto) {
        return texto.replace(/\b\w/g, function (l) { return l.toUpperCase(); });
    }

    function actualizarReloj() {
        var ahora = new Date();
        elHora.textContent = ahora.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        elFecha.textContent = capitalizar(ahora.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
    }
    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    var ICONOS = {
        sol: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#fbbf24"><circle cx="12" cy="12" r="4.5"/><g stroke="#fbbf24" stroke-width="2" stroke-linecap="round"><path d="M12 2v2.5M12 19.5V22M4.2 4.2l1.8 1.8M18 18l1.8 1.8M2 12h2.5M19.5 12H22M4.2 19.8 6 18M18 6l1.8-1.8"/></g></svg>',
        nube: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#cbd5e1"><path d="M7 18a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 18H7z"/></svg>',
        lluvia: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#60a5fa"><path d="M7 14.5a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 14.5H7z"/><g stroke="#60a5fa" stroke-width="1.8" stroke-linecap="round"><path d="M9 17v3M13 17v3M17 17v2"/></g></svg>',
        nieve: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#e2e8f0"><path d="M7 14.5a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 14.5H7z"/><g fill="#e2e8f0"><circle cx="9" cy="19" r="1"/><circle cx="13" cy="20" r="1"/><circle cx="17" cy="19" r="1"/></g></svg>',
        tormenta: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#cbd5e1"><path d="M7 13.5a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 13.5H7z"/><path fill="#fbbf24" d="m13 13-3 5h2.3l-1.3 4 4-5.5h-2.4z"/></svg>',
        niebla: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none"><g stroke="#cbd5e1" stroke-width="1.8" stroke-linecap="round"><path d="M4 9h16M3 13h18M5 17h14"/></g></svg>'
    };

    var DESCRIPCIONES = {
        0: 'Despejado', 1: 'Mayormente despejado', 2: 'Parcialmente nublado', 3: 'Nublado',
        45: 'Niebla', 48: 'Niebla', 51: 'Llovizna', 53: 'Llovizna', 55: 'Llovizna',
        61: 'Lluvia', 63: 'Lluvia', 65: 'Lluvia fuerte', 71: 'Nieve', 73: 'Nieve', 75: 'Nieve fuerte',
        80: 'Chubascos', 81: 'Chubascos', 82: 'Chubascos fuertes', 95: 'Tormenta', 96: 'Tormenta', 99: 'Tormenta'
    };

    function iconoParaCodigo(codigo) {
        if (codigo <= 2) return ICONOS.sol;
        if (codigo === 3) return ICONOS.nube;
        if (codigo === 45 || codigo === 48) return ICONOS.niebla;
        if ((codigo >= 51 && codigo <= 67) || (codigo >= 80 && codigo <= 82)) return ICONOS.lluvia;
        if (codigo >= 71 && codigo <= 77) return ICONOS.nieve;
        if (codigo >= 95) return ICONOS.tormenta;
        return ICONOS.nube;
    }

    function mostrarClima(lat, lon) {
        fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current=temperature_2m,weather_code')
            .then(function (r) { return r.json(); })
            .then(function (datos) {
                if (!datos || !datos.current) throw new Error('sin datos');
                var codigo = datos.current.weather_code;
                var temp = Math.round(datos.current.temperature_2m);
                elDesc.textContent = (DESCRIPCIONES[codigo] || 'Templado') + ' · ' + temp + '°C';
                elIcono.innerHTML = iconoParaCodigo(codigo);
            })
            .catch(function () {
                elDesc.textContent = 'N/D';
                elIcono.innerHTML = ICONOS.nube;
            });
    }

    function mostrarCiudad(lat, lon, nombrePorDefecto) {
        fetch('https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + lat + '&longitude=' + lon + '&localityLanguage=es')
            .then(function (r) { return r.json(); })
            .then(function (datos) {
                elCiudad.textContent = (datos && (datos.city || datos.locality)) || nombrePorDefecto;
            })
            .catch(function () { elCiudad.textContent = nombrePorDefecto; });
    }

    var LAT_DEFECTO = -34.6037, LON_DEFECTO = -58.3816;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                mostrarClima(pos.coords.latitude, pos.coords.longitude);
                mostrarCiudad(pos.coords.latitude, pos.coords.longitude, 'Tu ubicación');
            },
            function () {
                mostrarClima(LAT_DEFECTO, LON_DEFECTO);
                mostrarCiudad(LAT_DEFECTO, LON_DEFECTO, 'Buenos Aires');
            },
            { timeout: 5000 }
        );
    } else {
        mostrarClima(LAT_DEFECTO, LON_DEFECTO);
        mostrarCiudad(LAT_DEFECTO, LON_DEFECTO, 'Buenos Aires');
    }

    var campoClave = document.getElementById('password');
    var botonOjo = document.getElementById('altClave');
    var iconoOjo = document.getElementById('iconoOjo');
    var OJO_ABIERTO = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>';
    var OJO_CERRADO = '<path d="M3 3l18 18"/><path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a15.6 15.6 0 0 1-3.4 4.2M6.6 6.6C4 8.3 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 4-.8"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>';
    botonOjo.addEventListener('click', function () {
        var mostrando = campoClave.type === 'text';
        campoClave.type = mostrando ? 'password' : 'text';
        iconoOjo.innerHTML = mostrando ? OJO_ABIERTO : OJO_CERRADO;
        botonOjo.setAttribute('aria-label', mostrando ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    var CLAVE_USUARIO_RECORDADO = 'gym_usuario_recordado';
    var campoUsuario = document.getElementById('usuario');
    var checkRecordar = document.getElementById('recordarUsuario');
    try {
        var usuarioGuardado = localStorage.getItem(CLAVE_USUARIO_RECORDADO);
        if (usuarioGuardado) {
            campoUsuario.value = usuarioGuardado;
            checkRecordar.checked = true;
            document.getElementById('password').focus();
        }
    } catch (e) { /* localStorage no disponible */ }

    document.querySelector('form').addEventListener('submit', function () {
        try {
            if (checkRecordar.checked) {
                localStorage.setItem(CLAVE_USUARIO_RECORDADO, campoUsuario.value);
            } else {
                localStorage.removeItem(CLAVE_USUARIO_RECORDADO);
            }
        } catch (e) { /* localStorage no disponible */ }
    });
})();
</script>
</body>
</html>