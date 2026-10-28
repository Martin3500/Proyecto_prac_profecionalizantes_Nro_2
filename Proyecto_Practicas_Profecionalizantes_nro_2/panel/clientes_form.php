<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cliente = [
    'id_cliente' => '', 'DNI' => '', 'nombre' => '', 'apellido' => '',
    'email' => '', 'telefono' => '', 'id_estado' => '',
];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id_cliente = ?');
    $stmt->execute([$id]);
    $encontrado = $stmt->fetch();
    if (!$encontrado) {
        header('Location: clientes.php');
        exit;
    }
    $cliente = $encontrado;
}

$estados = $pdo->query('SELECT * FROM estado_cliente ORDER BY ID_est_clientes')->fetchAll();

$tituloPagina = $id > 0 ? 'Editar cliente' : 'Nuevo cliente';
$paginaActiva = 'clientes';
require __DIR__ . '/includes/layout_inicio.php';
?>

<div class="panel-header">
    <div>
        <h1 class="titulo-pagina"><?= $id > 0 ? 'Editar cliente' : 'Nuevo cliente' ?></h1>
        <div class="subt"><?= $id > 0 ? 'Actualizá los datos del cliente.' : 'Completá los datos para registrar un nuevo cliente.' ?></div>
    </div>
</div>

<div class="panel-bloque">
    <?php if (!empty($_GET['error'])): ?>
        <div class="alerta" style="margin-bottom: 1.5rem;">
            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            <span><?= htmlspecialchars($_GET['error']) ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="clientes_guardar.php">
        <input type="hidden" name="id_cliente" value="<?= (int)$cliente['id_cliente'] ?>">
        
        <div class="form-grid">
            <div class="campo">
                <label for="DNI">DNI</label>
                <input type="number" id="DNI" name="DNI" class="form-control" required value="<?= htmlspecialchars($cliente['DNI']) ?>">
            </div>
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required value="<?= htmlspecialchars($cliente['nombre']) ?>">
            </div>
            <div class="campo">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" class="form-control" required value="<?= htmlspecialchars($cliente['apellido']) ?>">
            </div>
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
            </div>
            <div class="campo">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>">
            </div>
            <div class="campo">
                <label for="id_estado">Estado</label>
                <select id="id_estado" name="id_estado" class="form-control" required>
                    <?php foreach ($estados as $e): ?>
                        <option value="<?= (int)$e['ID_est_clientes'] ?>"
                            <?= (string)$e['ID_est_clientes'] === (string)$cliente['id_estado'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['Estado']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-acciones">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="clientes.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/layout_fin.php'; ?>