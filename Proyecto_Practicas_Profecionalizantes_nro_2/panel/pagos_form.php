<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$idClientePreseleccionado = isset($_GET['id_cliente']) ? (int)$_GET['id_cliente'] : 0;

$clientes = $pdo->query("
    SELECT c.id_cliente, c.DNI, c.nombre, c.apellido, e.Estado
    FROM clientes c
    JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
    ORDER BY (e.Estado = 'Activo') DESC, c.apellido, c.nombre
")->fetchAll();

$tarifas = $pdo->query('
    SELECT t.id_tarifa, t.nombre, t.monto, e.nombre AS entrenador
    FROM tarifas t
    JOIN entrenadores e ON t.id_entrenador = e.id_entrenador
    ORDER BY t.nombre
')->fetchAll();

$tituloPagina = 'Registrar pago';
$paginaActiva = 'pagos';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina">Registrar pago</h1>
        <div class="subt">Elegí el cliente y la tarifa para registrar el pago.</div>
    </div>
</div>

<div class="panel-bloque">
    <?php if (!empty($_GET['error'])): ?>
        <div class="alerta" style="margin-bottom: 1.5rem;">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            <span><?= htmlspecialchars($_GET['error'] === '1' ? 'Error al procesar el pago. Intentá nuevamente.' : $_GET['error']) ?></span>
        </div>
    <?php endif; ?>

    <?php if (empty($clientes)): ?>
        <div class="alerta info">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            <span>No hay clientes registrados para cargar un pago. Creá un cliente primero.</span>
        </div>
    <?php elseif (empty($tarifas)): ?>
        <div class="alerta info">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            <span>No hay tarifas cargadas todavía.</span>
        </div>
    <?php else: ?>
    <form method="post" action="pagos_guardar.php">
        <div class="form-grid">
            <div class="campo">
                <label for="id_cliente">Cliente</label>
                <div class="campo-icono">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <select id="id_cliente" name="id_cliente" class="form-control con-icono" required>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= (int)$c['id_cliente'] ?>" <?= $idClientePreseleccionado === (int)$c['id_cliente'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido'] . ' (DNI ' . $c['DNI'] . ')' . ($c['Estado'] !== 'Activo' ? ' [' . $c['Estado'] . ' - Se reactivará]' : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="campo">
                <label for="id_tarifa">Tarifa / Plan</label>
                <div class="campo-icono">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    <select id="id_tarifa" name="id_tarifa" class="form-control con-icono" required onchange="autocompletarMonto(this)">
                        <?php foreach ($tarifas as $t): ?>
                            <option value="<?= (int)$t['id_tarifa'] ?>" data-monto="<?= htmlspecialchars($t['monto']) ?>">
                                <?= htmlspecialchars($t['nombre'] . ' - $' . number_format((float)$t['monto'], 2, ',', '.') . ' (' . $t['entrenador'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="campo">
                <label for="monto">Monto ($)</label>
                <div class="campo-icono">
                    <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <input type="number" step="0.01" min="0" id="monto" name="monto" class="form-control con-icono" required
                           value="<?= htmlspecialchars($tarifas[0]['monto']) ?>">
                </div>
            </div>

            <div class="campo">
                <label for="metodo_pago">Método de Pago</label>
                <div class="campo-icono">
                    <svg class="icon" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    <select id="metodo_pago" name="metodo_pago" class="form-control con-icono" required>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia Bancaria</option>
                        <option value="MercadoPago">MercadoPago / QR</option>
                        <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                        <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                    </select>
                </div>
            </div>

            <div class="campo">
                <label for="fecha_vencimiento">Vencimiento de Cuota (Vigencia)</label>
                <div class="campo-icono">
                    <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-control con-icono" required
                           value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Registrar pago
            </button>
            <a href="pagos.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
    <script>
        function autocompletarMonto(select) {
            const opcion = select.options[select.selectedIndex];
            document.getElementById('monto').value = opcion.getAttribute('data-monto');
        }
    </script>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>