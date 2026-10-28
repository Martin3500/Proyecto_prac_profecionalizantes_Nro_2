<?php
// Requiere que $paginaActiva y $tituloPagina esten definidos antes del include
$paginaActiva = $paginaActiva ?? '';
$tituloPagina = $tituloPagina ?? 'Panel';

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$inicial = mb_strtoupper(mb_substr($nombreUsuario, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> - <?= htmlspecialchars(GYM_NOMBRE) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../assets/css/layout.css') . filemtime(__DIR__ . '/../../assets/css/components.css') ?>">
    <link rel="icon" href="../assets/img/logo.svg" type="image/svg+xml">
</head>
<body class="pagina-<?= htmlspecialchars($paginaActiva) ?>">
<div class="panel-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="marca">
                <img src="../assets/img/logo.svg" alt="Logo" class="logo-img"> 
                <span class="texto"><?= htmlspecialchars(GYM_NOMBRE) ?></span>
            </div>
        </div>

        <?php
        $bloqueInicial = 0;
        if (in_array($paginaActiva, ['pagos', 'entrenadores', 'disciplinas'], true)) {
            $bloqueInicial = 1;
        } elseif (in_array($paginaActiva, ['tarifas', 'usuarios'], true)) {
            $bloqueInicial = 2;
        }
        ?>
        <nav class="sidebar-nav" id="sidebarCarrusel" data-bloque-inicial="<?= $bloqueInicial ?>">
            <div class="nav-cabecera-carrusel">
                <span class="nav-etiqueta" id="navTituloBloque">Menú (1/3)</span>
                <div class="nav-controles-carrusel">
                    <button type="button" class="nav-flecha" id="navPrev" aria-label="Anterior bloque" title="Anterior">
                        <svg class="icon" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button type="button" class="nav-flecha" id="navNext" aria-label="Siguiente bloque" title="Siguiente">
                        <svg class="icon" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>

            <!-- Bloque 1 (3 enlaces) -->
            <div class="nav-grupo <?= $bloqueInicial === 0 ? 'activo' : '' ?>" data-titulo="Operaciones (1/3)">
                <a href="dashboard.php" class="nav-link <?= $paginaActiva === 'dashboard' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                    <span class="texto">Dashboard</span>
                </a>
                <a href="acceso.php" class="nav-link <?= $paginaActiva === 'acceso' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span class="texto">Control de Acceso</span>
                </a>
                <a href="clientes.php" class="nav-link <?= $paginaActiva === 'clientes' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c.7-3.4 3-5.3 5.5-5.3s4.8 1.9 5.5 5.3"/><circle cx="17.5" cy="8.5" r="2.3"/><path d="M15.8 14.9c2 .2 3.6 1.9 4.1 4.3"/></svg>
                    <span class="texto">Clientes</span>
                </a>
            </div>

            <!-- Bloque 2 (3 enlaces) -->
            <div class="nav-grupo <?= $bloqueInicial === 1 ? 'activo' : '' ?>" data-titulo="Gestión y Clases (2/3)">
                <a href="pagos.php" class="nav-link <?= $paginaActiva === 'pagos' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><rect x="2.5" y="6" width="19" height="13" rx="2"/><path d="M2.5 10h19"/><path d="M6 15h4"/></svg>
                    <span class="texto">Pagos</span>
                </a>
                <a href="entrenadores.php" class="nav-link <?= $paginaActiva === 'entrenadores' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="texto">Entrenadores</span>
                </a>
                <a href="disciplinas.php" class="nav-link <?= $paginaActiva === 'disciplinas' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/></svg>
                    <span class="texto">Disciplinas</span>
                </a>
            </div>

            <!-- Bloque 3 (hasta 3 enlaces) -->
            <div class="nav-grupo <?= $bloqueInicial === 2 ? 'activo' : '' ?>" data-titulo="Administración (3/3)">
                <a href="tarifas_entrenadores.php" class="nav-link <?= $paginaActiva === 'tarifas' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5"/></svg>
                    <span class="texto">Tarifas de Entrenadores</span>
                </a> 
                <?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
                <a href="usuarios.php" class="nav-link <?= $paginaActiva === 'usuarios' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1-3.8 3.6-5.8 7-5.8s6 2 7 5.8"/><path d="M19 8h3M20.5 6.5v3"/></svg>
                    <span class="texto">Usuarios</span>
                </a>
                <?php endif; ?>
            </div>

            <div class="nav-puntos" id="navPuntos">
                <button type="button" class="nav-punto <?= $bloqueInicial === 0 ? 'activo' : '' ?>" data-idx="0" aria-label="Bloque 1"></button>
                <button type="button" class="nav-punto <?= $bloqueInicial === 1 ? 'activo' : '' ?>" data-idx="1" aria-label="Bloque 2"></button>
                <button type="button" class="nav-punto <?= $bloqueInicial === 2 ? 'activo' : '' ?>" data-idx="2" aria-label="Bloque 3"></button>
            </div>
        </nav>

        <div class="pie-sidebar">
            <nav class="sidebar-nav">
                <a href="cambiar_password.php" class="nav-link <?= $paginaActiva === 'cambiar_password' ? 'activo' : '' ?>">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4"/></svg>
                    <span class="texto">Cambiar Contraseña</span>
                </a>
                <a href="logout.php" class="nav-link nav-link-logout">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    <span class="texto">Cerrar sesión</span>
                </a>
            </nav>
        </div>
    </aside>

    <div class="panel-main">
        <header class="topbar">
            <div class="topbar-reloj">
                <span class="tr-item">
                    <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    <span id="trFecha">--</span>
                </span>
                <span class="tr-sep">·</span>
                <span class="tr-item">
                    <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
                    <span id="trHora">--:--</span>
                </span>
                <span class="tr-sep">·</span>
                <span class="tr-item">
                    <span class="tr-icono-clima" id="trIconoClima"></span>
                    <span id="trTemp">--°C</span>
                </span>
            </div>

            <?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
                <a href="usuarios_form.php?id=<?= (int)($_SESSION['usuario_id'] ?? 0) ?>" class="usuario-chip" title="Editar mi perfil" style="text-decoration: none; color: inherit;">
                    <div class="usuario-texto">
                        <div class="usuario-saludo" id="saludoUsuario">Hola</div>
                        <div class="usuario-nombre"><?= htmlspecialchars($nombreUsuario) ?></div>
                    </div>
                    <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
                </a>
            <?php else: ?>
                <a href="cambiar_password.php" class="usuario-chip" title="Cambiar mi contraseña" style="text-decoration: none; color: inherit;">
                    <div class="usuario-texto">
                        <div class="usuario-saludo" id="saludoUsuario">Hola</div>
                        <div class="usuario-nombre"><?= htmlspecialchars($nombreUsuario) ?></div>
                    </div>
                    <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
                </a>
            <?php endif; ?>
        </header>

        <script>
            (function () {
                var h = new Date().getHours();
                var saludo = h < 6 ? 'Buenas noches' : h < 12 ? 'Buenos días' : h < 19 ? 'Buenas tardes' : 'Buenas noches';
                document.getElementById('saludoUsuario').textContent = saludo;

                function capitalizar(texto) {
                    return texto.replace(/\b\w/g, function (l) { return l.toUpperCase(); });
                }

                function actualizarReloj() {
                    var ahora = new Date();
                    document.getElementById('trHora').textContent = ahora.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', hour12: false });
                    document.getElementById('trFecha').textContent = capitalizar(ahora.toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'short' })).replace('.', '');
                }
                actualizarReloj();
                setInterval(actualizarReloj, 1000 * 30);

                var ICONOS_CLIMA = {
                    sol: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#f59e0b"><circle cx="12" cy="12" r="4.5"/><g stroke="#f59e0b" stroke-width="2" stroke-linecap="round"><path d="M12 2v2.5M12 19.5V22M4.2 4.2l1.8 1.8M18 18l1.8 1.8M2 12h2.5M19.5 12H22M4.2 19.8 6 18M18 6l1.8-1.8"/></g></svg>',
                    nube: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#94a3b8"><path d="M7 18a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 18H7z"/></svg>',
                    lluvia: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#3b82f6"><path d="M7 14.5a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 14.5H7z"/><g stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round"><path d="M9 17v3M13 17v3M17 17v2"/></g></svg>',
                    nieve: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#94a3b8"><path d="M7 14.5a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 14.5H7z"/><g fill="#94a3b8"><circle cx="9" cy="19" r="1"/><circle cx="13" cy="20" r="1"/><circle cx="17" cy="19" r="1"/></g></svg>',
                    tormenta: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none;fill:#94a3b8"><path d="M7 13.5a4.5 4.5 0 0 1-.5-8.97 5.5 5.5 0 0 1 10.6-1.9A4 4 0 0 1 17 13.5H7z"/><path fill="#f59e0b" d="m13 13-3 5h2.3l-1.3 4 4-5.5h-2.4z"/></svg>',
                    niebla: '<svg class="icon" viewBox="0 0 24 24" style="stroke:none"><g stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"><path d="M4 9h16M3 13h18M5 17h14"/></g></svg>'
                };

                function iconoParaCodigo(codigo) {
                    if (codigo <= 2) return ICONOS_CLIMA.sol;
                    if (codigo === 3) return ICONOS_CLIMA.nube;
                    if (codigo === 45 || codigo === 48) return ICONOS_CLIMA.niebla;
                    if ((codigo >= 51 && codigo <= 67) || (codigo >= 80 && codigo <= 82)) return ICONOS_CLIMA.lluvia;
                    if (codigo >= 71 && codigo <= 77) return ICONOS_CLIMA.nieve;
                    if (codigo >= 95) return ICONOS_CLIMA.tormenta;
                    return ICONOS_CLIMA.nube;
                }

                function mostrarClima(lat, lon) {
                    fetch('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current=temperature_2m,weather_code')
                        .then(function (r) { return r.json(); })
                        .then(function (datos) {
                            if (!datos || !datos.current) throw new Error('sin datos');
                            document.getElementById('trTemp').textContent = Math.round(datos.current.temperature_2m) + '°C';
                            document.getElementById('trIconoClima').innerHTML = iconoParaCodigo(datos.current.weather_code);
                        })
                        .catch(function () {
                            document.getElementById('trTemp').textContent = 'N/D';
                            document.getElementById('trIconoClima').innerHTML = ICONOS_CLIMA.nube;
                        });
                }

                var LAT_DEFECTO = -34.6037, LON_DEFECTO = -58.3816; // Buenos Aires
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function (pos) { mostrarClima(pos.coords.latitude, pos.coords.longitude); },
                        function () { mostrarClima(LAT_DEFECTO, LON_DEFECTO); },
                        { timeout: 5000 }
                    );
                } else {
                    mostrarClima(LAT_DEFECTO, LON_DEFECTO);
                }

                // Carrusel de 3 enlaces en el menú lateral
                var carrusel = document.getElementById('sidebarCarrusel');
                if (carrusel) {
                    var grupos = carrusel.querySelectorAll('.nav-grupo');
                    var puntos = carrusel.querySelectorAll('.nav-punto');
                    var tituloEl = document.getElementById('navTituloBloque');
                    var btnPrev = document.getElementById('navPrev');
                    var btnNext = document.getElementById('navNext');
                    var idxActual = parseInt(carrusel.getAttribute('data-bloque-inicial') || '0', 10);

                    function mostrarBloque(idx) {
                        if (idx < 0) idx = grupos.length - 1;
                        if (idx >= grupos.length) idx = 0;
                        idxActual = idx;

                        grupos.forEach(function (g, i) {
                            g.classList.toggle('activo', i === idxActual);
                        });
                        puntos.forEach(function (p, i) {
                            p.classList.toggle('activo', i === idxActual);
                        });
                        if (tituloEl && grupos[idxActual]) {
                            tituloEl.textContent = grupos[idxActual].getAttribute('data-titulo');
                        }
                    }

                    mostrarBloque(idxActual);

                    if (btnPrev) btnPrev.addEventListener('click', function () { mostrarBloque(idxActual - 1); });
                    if (btnNext) btnNext.addEventListener('click', function () { mostrarBloque(idxActual + 1); });
                    puntos.forEach(function (p) {
                        p.addEventListener('click', function () {
                            mostrarBloque(parseInt(p.getAttribute('data-idx'), 10));
                        });
                    });
                }
            })();
        </script>

        <div class="panel-contenido">