<?php
require_once __DIR__ . '/config.php';

$disciplinasDB = [];
try {
    $pdo = conectarDB();
    $disciplinasDB = $pdo->query('SELECT disciplina, descripcion FROM disciplinas ORDER BY id_disciplina ASC')->fetchAll();
} catch (Throwable $e) {
    $disciplinasDB = [];
}

function iconoDisciplinaLanding(string $nombre): string
{
    $n = mb_strtolower($nombre, 'UTF-8');
    if (str_contains($n, 'spin') || str_contains($n, 'cicl') || str_contains($n, 'bici') || str_contains($n, 'indoor')) {
        return '<svg class="icon" viewBox="0 0 24 24"><circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M6 17l4-9h4l-2 5h4l2 4M10 8h3"/></svg>';
    }
    if (str_contains($n, 'yoga') || str_contains($n, 'pilates') || str_contains($n, 'stretch') || str_contains($n, 'elong')) {
        return '<svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="5.2" r="2.1"/><path d="M12 7.3v3"/><path d="M9 9.3c1-.5 2-.7 3-.7s2 .2 3 .7"/><path d="M4.5 17.5c2-2.8 4.7-4.2 7.5-4.2s5.5 1.4 7.5 4.2"/><path d="M4.5 17.5c1.1 1.7 2.4 2.7 4 3.1M19.5 17.5c-1.1 1.7-2.4 2.7-4 3.1"/></svg>';
    }
    if (str_contains($n, 'cross') || str_contains($n, 'funcional') || str_contains($n, 'hiit') || str_contains($n, 'cardio')) {
        return '<svg class="icon" viewBox="0 0 24 24"><path d="M13 2 4.5 13h5L9 22l9.5-13h-5z"/></svg>';
    }
    return '<svg class="icon" viewBox="0 0 24 24"><path d="M6.5 6.5v11M17.5 6.5v11M3 9v6M21 9v6M6.5 12h11"/></svg>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars(GYM_NOMBRE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
<link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
</head>
<body>

<header class="navbar">
    <div class="marca"><img src="assets/img/logo.svg" alt="" class="logo-img"> <?= htmlspecialchars(GYM_NOMBRE) ?></div>
    <nav>
        <a href="#servicios" class="nav-link">Servicios</a>
        <a href="#contacto" class="nav-link">Contacto</a>
    </nav>
    <a href="login.php" class="btn-ingresar">Ingresar</a>
</header>

<section class="hero">
    <span class="eyebrow"><span class="punto"></span> Cupos abiertos para nuevos socios</span>
    <h1>Entrená con <span>disciplina</span>,<br>alcanzá tus objetivos</h1>
    <p>Musculación, clases grupales y entrenadores certificados en un mismo lugar. Sumate y empezá tu cambio hoy mismo.</p>
    <div class="cta-group">
        <a href="https://wa.me/<?= htmlspecialchars(GYM_WHATSAPP) ?>?text=<?= urlencode('Hola! Quiero información sobre ' . GYM_NOMBRE) ?>"
           target="_blank" rel="noopener" class="btn btn-whatsapp">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 2C6.6 2 2.1 6.5 2.1 11.9c0 1.8.5 3.5 1.3 5L2 22l5.3-1.4a9.9 9.9 0 0 0 4.7 1.2h.1c5.5 0 9.9-4.5 9.9-9.9 0-2.7-1-5.1-2.9-7A9.8 9.8 0 0 0 12 2zm5.8 14c-.2.7-1.4 1.3-1.9 1.4-.5.1-1.1.1-1.8-.1-.4-.2-1-.3-1.6-.6-2.9-1.3-4.8-4.2-4.9-4.4-.2-.2-1.2-1.5-1.2-3 0-1.4.7-2.1 1-2.4.3-.3.6-.4.8-.4h.5c.2 0 .4 0 .7.5.2.6.8 2 .9 2.1.1.2.1.3 0 .5l-.3.5c-.1.2-.3.4-.4.5-.1.1-.3.3-.1.6.2.3.7 1.2 1.5 1.9 1.1.9 1.9 1.2 2.2 1.4.3.1.4.1.6-.1.2-.2.7-.8.9-1.1.2-.3.4-.2.6-.1l2 .9c.3.2.5.2.5.3 0 .2 0 .7-.2 1.4z"/></svg>
            WhatsApp
        </a>
        <a href="login.php" class="btn btn-outline">Ingresar al sistema</a>
    </div>
    <ul class="confianza">
        <li>
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 2l2.4 6.9H21l-5.5 4.2 2.1 7-5.6-4.3L6.4 20l2.1-7L3 8.9h6.6z"/></svg>
            Entrenadores certificados
        </li>
        <li>
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
            Horarios flexibles
        </li>
        <li>
            <svg class="icon" viewBox="0 0 24 24"><path d="M6 4h9l3 3v13H6z"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>
            Planes a tu medida
        </li>
    </ul>
</section>

<section class="seccion-servicios" id="servicios">
    <div class="deco-circuito" aria-hidden="true"></div>
    <svg class="deco-hex" viewBox="0 0 200 200" aria-hidden="true">
        <polygon points="60,5 110,35 110,95 60,125 10,95 10,35" />
        <polygon points="150,60 180,78 180,112 150,130 120,112 120,78" />
    </svg>
    <div class="seccion">
        <div class="seccion-cabecera">
            <span class="eyebrow-claro">Disciplinas</span>
            <h2>Nuestros servicios</h2>
            <p>Elegí la disciplina que mejor se adapte a tu objetivo y sumate a nuestras clases.</p>
        </div>
        <div class="tarjetas">
            <?php if (!empty($disciplinasDB)): ?>
                <?php foreach ($disciplinasDB as $d): ?>
                    <div class="tarjeta">
                        <div class="icono">
                            <?= iconoDisciplinaLanding($d['disciplina']) ?>
                        </div>
                        <h3><?= htmlspecialchars($d['disciplina']) ?></h3>
                        <p><?= htmlspecialchars(!empty(trim($d['descripcion'] ?? '')) ? $d['descripcion'] : 'Entrenamiento guiado por profesionales certificados para todos los niveles.') ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="tarjeta">
                    <div class="icono">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M6.5 6.5v11M17.5 6.5v11M3 9v6M21 9v6M6.5 12h11"/></svg>
                    </div>
                    <h3>Musculación</h3>
                    <p>Sala equipada con planificación personalizada para cada objetivo.</p>
                </div>
                <div class="tarjeta">
                    <div class="icono">
                        <svg class="icon" viewBox="0 0 24 24"><circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/><path d="M6 17l4-9h4l-2 5h4l2 4M10 8h3"/></svg>
                    </div>
                    <h3>Spinning</h3>
                    <p>Clases grupales de ciclismo de interior con música e intensidad.</p>
                </div>
                <div class="tarjeta">
                    <div class="icono">
                        <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="5.2" r="2.1"/><path d="M12 7.3v3"/><path d="M9 9.3c1-.5 2-.7 3-.7s2 .2 3 .7"/><path d="M4.5 17.5c2-2.8 4.7-4.2 7.5-4.2s5.5 1.4 7.5 4.2"/><path d="M4.5 17.5c1.1 1.7 2.4 2.7 4 3.1M19.5 17.5c-1.1 1.7-2.4 2.7-4 3.1"/></svg>
                    </div>
                    <h3>Yoga</h3>
                    <p>Flexibilidad, respiración y relajación guiada por instructores certificados.</p>
                </div>
                <div class="tarjeta">
                    <div class="icono">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M13 2 4.5 13h5L9 22l9.5-13h-5z"/></svg>
                    </div>
                    <h3>CrossFit</h3>
                    <p>Entrenamiento funcional de alta intensidad en grupos reducidos.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="franja-contacto" id="contacto">
    <span class="eyebrow-claro">Contacto</span>
    <h2>¿Listo para empezar?</h2>
    <p>Escribinos por WhatsApp y te contamos los planes disponibles.</p>
    <a href="https://wa.me/<?= htmlspecialchars(GYM_WHATSAPP) ?>?text=<?= urlencode('Hola! Quiero información sobre ' . GYM_NOMBRE) ?>"
       target="_blank" rel="noopener" class="btn btn-whatsapp">
        <svg class="icon" viewBox="0 0 24 24"><path d="M12 2C6.6 2 2.1 6.5 2.1 11.9c0 1.8.5 3.5 1.3 5L2 22l5.3-1.4a9.9 9.9 0 0 0 4.7 1.2h.1c5.5 0 9.9-4.5 9.9-9.9 0-2.7-1-5.1-2.9-7A9.8 9.8 0 0 0 12 2zm5.8 14c-.2.7-1.4 1.3-1.9 1.4-.5.1-1.1.1-1.8-.1-.4-.2-1-.3-1.6-.6-2.9-1.3-4.8-4.2-4.9-4.4-.2-.2-1.2-1.5-1.2-3 0-1.4.7-2.1 1-2.4.3-.3.6-.4.8-.4h.5c.2 0 .4 0 .7.5.2.6.8 2 .9 2.1.1.2.1.3 0 .5l-.3.5c-.1.2-.3.4-.4.5-.1.1-.3.3-.1.6.2.3.7 1.2 1.5 1.9 1.1.9 1.9 1.2 2.2 1.4.3.1.4.1.6-.1.2-.2.7-.8.9-1.1.2-.3.4-.2.6-.1l2 .9c.3.2.5.2.5.3 0 .2 0 .7-.2 1.4z"/></svg>
        Escribir por WhatsApp
    </a>
</section>

<a class="whatsapp-flotante"
   href="https://wa.me/<?= htmlspecialchars(GYM_WHATSAPP) ?>?text=<?= urlencode('Hola! Quiero información sobre ' . GYM_NOMBRE) ?>"
   target="_blank" rel="noopener" title="Escribinos por WhatsApp">
    <svg class="icon" viewBox="0 0 24 24"><path d="M12 2C6.6 2 2.1 6.5 2.1 11.9c0 1.8.5 3.5 1.3 5L2 22l5.3-1.4a9.9 9.9 0 0 0 4.7 1.2h.1c5.5 0 9.9-4.5 9.9-9.9 0-2.7-1-5.1-2.9-7A9.8 9.8 0 0 0 12 2zm5.8 14c-.2.7-1.4 1.3-1.9 1.4-.5.1-1.1.1-1.8-.1-.4-.2-1-.3-1.6-.6-2.9-1.3-4.8-4.2-4.9-4.4-.2-.2-1.2-1.5-1.2-3 0-1.4.7-2.1 1-2.4.3-.3.6-.4.8-.4h.5c.2 0 .4 0 .7.5.2.6.8 2 .9 2.1.1.2.1.3 0 .5l-.3.5c-.1.2-.3.4-.4.5-.1.1-.3.3-.1.6.2.3.7 1.2 1.5 1.9 1.1.9 1.9 1.2 2.2 1.4.3.1.4.1.6-.1.2-.2.7-.8.9-1.1.2-.3.4-.2.6-.1l2 .9c.3.2.5.2.5.3 0 .2 0 .7-.2 1.4z"/></svg>
</a>

<footer class="landing-footer">
    &copy; <?= date('Y') ?> <?= htmlspecialchars(GYM_NOMBRE) ?>. Todos los derechos reservados.
</footer>

</body>
</html>