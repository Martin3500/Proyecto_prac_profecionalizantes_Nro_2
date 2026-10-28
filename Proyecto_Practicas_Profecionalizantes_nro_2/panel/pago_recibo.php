<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$idPago = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idPago <= 0) {
    header('Location: pagos.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        hp.id_pago,
        hp.fecha_pago,
        hp.fecha_vencimiento,
        hp.monto,
        hp.metodo_pago,
        c.id_cliente,
        c.DNI AS cliente_dni,
        c.nombre AS cliente_nombre,
        c.apellido AS cliente_apellido,
        c.email AS cliente_email,
        c.telefono AS cliente_telefono,
        t.nombre AS tarifa_nombre,
        t.descripcion AS tarifa_descripcion,
        e.nombre AS entrenador_nombre,
        e.apellido AS entrenador_apellido
    FROM historial_de_pagos hp
    JOIN clientes c ON hp.id_cliente = c.id_cliente
    JOIN tarifas t ON hp.id_tarifa = t.id_tarifa
    JOIN entrenadores e ON t.id_entrenador = e.id_entrenador
    WHERE hp.id_pago = ?
");
$stmt->execute([$idPago]);
$pago = $stmt->fetch();

if (!$pago) {
    header('Location: pagos.php');
    exit;
}

$nroComprobante = str_pad((string)$pago['id_pago'], 6, '0', STR_PAD_LEFT);
$fechaEmision = date('d/m/Y H:i', strtotime($pago['fecha_pago']));
$fechaVenc = !empty($pago['fecha_vencimiento'])
    ? date('d/m/Y', strtotime($pago['fecha_vencimiento']))
    : date('d/m/Y', strtotime($pago['fecha_pago'] . ' +30 days'));

$telefonoLimpio = preg_replace('/\D+/', '', $pago['cliente_telefono'] ?? '');
$textoWhatsapp = "Hola {$pago['cliente_nombre']}! Te enviamos el comprobante de pago #{$nroComprobante} de " . GYM_NOMBRE .
    " por $" . number_format((float)$pago['monto'], 2, ',', '.') .
    " (Plan: {$pago['tarifa_nombre']}). Vigente hasta el {$fechaVenc}. ¡Gracias por entrenar con nosotros!";

$tituloPagina = 'Comprobante #' . $nroComprobante;
$paginaActiva = 'pagos';
require __DIR__ . '/includes/layout_inicio.php';
?>

<style>
.recibo-contenedor {
    max-width: 720px;
    margin: 0 auto;
}
.recibo-papel {
    background-color: var(--color-bg-card);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    box-shadow: var(--shadow-md);
}
.recibo-cabecera {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px dashed var(--color-border);
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
}
.recibo-marca {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.recibo-marca img {
    width: 44px;
    height: 44px;
}
.recibo-marca h2 {
    font-size: 1.4rem;
    font-weight: 800;
    margin: 0;
}
.recibo-marca p {
    margin: 0.15rem 0 0 0;
    color: var(--color-text-muted);
    font-size: 0.85rem;
}
.recibo-meta {
    text-align: right;
}
.recibo-meta .etiqueta-comp {
    display: inline-block;
    background-color: rgba(255, 51, 102, 0.15);
    color: var(--color-primary);
    border: 1px solid rgba(255, 51, 102, 0.3);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.4rem;
}
.recibo-meta .nro {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--color-text-main);
}
.recibo-meta .fecha {
    font-size: 0.85rem;
    color: var(--color-text-muted);
}
.recibo-grilla {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1.75rem;
}
.recibo-caja {
    background-color: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
}
.recibo-caja-titulo {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-text-muted);
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.recibo-caja-dato {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-text-main);
}
.recibo-caja-sub {
    font-size: 0.85rem;
    color: var(--color-text-muted);
    margin-top: 0.2rem;
}
.recibo-detalle {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.75rem;
}
.recibo-detalle th {
    text-align: left;
    padding: 0.75rem 1rem;
    font-size: 0.78rem;
    text-transform: uppercase;
    color: var(--color-text-muted);
    border-bottom: 1px solid var(--color-border);
}
.recibo-detalle td {
    padding: 1rem;
    border-bottom: 1px solid var(--color-border);
}
.recibo-total-fila {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: var(--radius-md);
    padding: 1.15rem 1.5rem;
    margin-bottom: 1.5rem;
}
.recibo-total-fila .lbl {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-text-main);
}
.recibo-total-fila .val {
    font-size: 1.65rem;
    font-weight: 800;
    color: #4ade80;
}
.recibo-pie {
    text-align: center;
    color: var(--color-text-muted);
    font-size: 0.82rem;
    padding-top: 1rem;
    border-top: 1px solid var(--color-border);
}

@media print {
    .sidebar, .topbar, .no-print {
        display: none !important;
    }
    body, .panel-layout, .panel-main, .panel-contenido {
        background: #ffffff !important;
        color: #111827 !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }
    .recibo-contenedor {
        max-width: 100% !important;
    }
    .recibo-papel {
        background: #ffffff !important;
        color: #111827 !important;
        border: 1px solid #d1d5db !important;
        box-shadow: none !important;
        padding: 1.5rem !important;
    }
    .recibo-marca h2, .recibo-meta .nro, .recibo-caja-dato, .recibo-total-fila .lbl, .recibo-detalle td {
        color: #111827 !important;
    }
    .recibo-marca p, .recibo-meta .fecha, .recibo-caja-titulo, .recibo-caja-sub, .recibo-detalle th, .recibo-pie {
        color: #4b5563 !important;
    }
    .recibo-caja {
        background: #f9fafb !important;
        border-color: #e5e7eb !important;
    }
    .recibo-total-fila {
        background: #f0fdf4 !important;
        border-color: #86efac !important;
    }
    .recibo-total-fila .val {
        color: #15803d !important;
    }
}
</style>

<div class="panel-header no-print">
    <div>
        <h1 class="titulo-pagina">Comprobante de Pago</h1>
        <div class="subt">Recibo oficial #<?= htmlspecialchars($nroComprobante) ?> listo para imprimir o descargar en PDF.</div>
    </div>
    <div class="acciones" style="flex-wrap: wrap;">
        <button type="button" onclick="window.print()" class="btn btn-primary">
            <svg class="icon" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Imprimir / Guardar PDF
        </button>
        <?php if ($telefonoLimpio !== ''): ?>
            <a href="https://wa.me/<?= htmlspecialchars($telefonoLimpio) ?>?text=<?= urlencode($textoWhatsapp) ?>"
               target="_blank" rel="noopener" class="btn btn-whatsapp">
                <svg class="icon" viewBox="0 0 24 24"><path d="M12 2C6.6 2 2.1 6.5 2.1 11.9c0 1.8.5 3.5 1.3 5L2 22l5.3-1.4a9.9 9.9 0 0 0 4.7 1.2h.1c5.5 0 9.9-4.5 9.9-9.9 0-2.7-1-5.1-2.9-7A9.8 9.8 0 0 0 12 2zm5.8 14c-.2.7-1.4 1.3-1.9 1.4-.5.1-1.1.1-1.8-.1-.4-.2-1-.3-1.6-.6-2.9-1.3-4.8-4.2-4.9-4.4-.2-.2-1.2-1.5-1.2-3 0-1.4.7-2.1 1-2.4.3-.3.6-.4.8-.4h.5c.2 0 .4 0 .7.5.2.6.8 2 .9 2.1.1.2.1.3 0 .5l-.3.5c-.1.2-.3.4-.4.5-.1.1-.3.3-.1.6.2.3.7 1.2 1.5 1.9 1.1.9 1.9 1.2 2.2 1.4.3.1.4.1.6-.1.2-.2.7-.8.9-1.1.2-.3.4-.2.6-.1l2 .9c.3.2.5.2.5.3 0 .2 0 .7-.2 1.4z"/></svg>
                Enviar por WhatsApp
            </a>
        <?php endif; ?>
        <a href="pagos.php" class="btn btn-outline">Volver a Pagos</a>
    </div>
</div>

<div class="recibo-contenedor">
    <div class="recibo-papel">
        <div class="recibo-cabecera">
            <div class="recibo-marca">
                <img src="../assets/img/logo.svg" alt="Logo">
                <div>
                    <h2><?= htmlspecialchars(GYM_NOMBRE) ?></h2>
                    <p>Comprobante de Membresía y Servicios</p>
                </div>
            </div>
            <div class="recibo-meta">
                <span class="etiqueta-comp">Comprobante de Pago</span>
                <div class="nro">N° <?= htmlspecialchars($nroComprobante) ?></div>
                <div class="fecha">Fecha: <?= htmlspecialchars($fechaEmision) ?> hs</div>
            </div>
        </div>

        <div class="recibo-grilla">
            <div class="recibo-caja">
                <div class="recibo-caja-titulo">Datos del Socio / Cliente</div>
                <div class="recibo-caja-dato"><?= htmlspecialchars($pago['cliente_nombre'] . ' ' . $pago['cliente_apellido']) ?></div>
                <div class="recibo-caja-sub">DNI: <?= htmlspecialchars($pago['cliente_dni']) ?></div>
                <?php if (!empty($pago['cliente_telefono'])): ?>
                    <div class="recibo-caja-sub">Tel: <?= htmlspecialchars($pago['cliente_telefono']) ?></div>
                <?php endif; ?>
            </div>

            <div class="recibo-caja">
                <div class="recibo-caja-titulo">Vigencia y Medio de Pago</div>
                <div class="recibo-caja-dato">Hasta el <?= htmlspecialchars($fechaVenc) ?></div>
                <div class="recibo-caja-sub">Medio de pago: <strong><?= htmlspecialchars($pago['metodo_pago'] ?? 'Efectivo') ?></strong></div>
                <div class="recibo-caja-sub">Entrenador: <?= htmlspecialchars($pago['entrenador_nombre'] . ' ' . $pago['entrenador_apellido']) ?></div>
            </div>
        </div>

        <table class="recibo-detalle">
            <thead>
                <tr>
                    <th>Concepto / Plan Contratado</th>
                    <th>Período Cubierto</th>
                    <th style="text-align: right;">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($pago['tarifa_nombre']) ?></strong>
                        <?php if (!empty($pago['tarifa_descripcion'])): ?>
                            <div style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 0.2rem;">
                                <?= htmlspecialchars($pago['tarifa_descripcion']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($pago['fecha_pago']))) ?> al <?= htmlspecialchars($fechaVenc) ?></td>
                    <td style="text-align: right; font-weight: 700;">$<?= number_format((float)$pago['monto'], 2, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <div class="recibo-total-fila">
            <span class="lbl">TOTAL ABONADO</span>
            <span class="val">$<?= number_format((float)$pago['monto'], 2, ',', '.') ?></span>
        </div>

        <div class="recibo-pie">
            Comprobante emitido por el sistema de gestión de <strong><?= htmlspecialchars(GYM_NOMBRE) ?></strong>.<br>
            Conservá este recibo como constancia de pago y vigencia de tu cuota.
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>

