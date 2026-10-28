<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = conectarDB();

$tipo = $_GET['tipo'] ?? 'clientes';
$busqueda = trim($_GET['busqueda'] ?? '');
$fechaHoy = date('Y-m-d');

header('Content-Type: text/csv; charset=utf-8');

$salida = fopen('php://output', 'w');
// BOM UTF-8 para que Microsoft Excel reconozca tildes y caracteres especiales automáticamente
fwrite($salida, "\xEF\xBB\xBF");

if ($tipo === 'pagos') {
    header('Content-Disposition: attachment; filename="reporte_pagos_' . $fechaHoy . '.csv"');
    fputcsv($salida, ['Nro Comprobante', 'Fecha de Pago', 'Vencimiento Cuota', 'DNI Cliente', 'Cliente', 'Tarifa / Plan', 'Entrenador', 'Metodo de Pago', 'Monto ($)'], ';');

    $params = [];
    $whereSql = '';
    if ($busqueda !== '') {
        $whereSql = " WHERE (
            CONCAT(c.nombre, ' ', c.apellido) LIKE :b1 
            OR CONCAT(c.apellido, ' ', c.nombre) LIKE :b2 
            OR c.DNI LIKE :b3 
            OR t.nombre LIKE :b4
            OR hp.metodo_pago LIKE :b5
        ) ";
        $term = '%' . $busqueda . '%';
        $params = [':b1' => $term, ':b2' => $term, ':b3' => $term, ':b4' => $term, ':b5' => $term];
    }

    $stmt = $pdo->prepare("
        SELECT 
            hp.id_pago,
            hp.fecha_pago,
            hp.fecha_vencimiento,
            hp.monto,
            hp.metodo_pago,
            c.DNI,
            c.nombre,
            c.apellido,
            t.nombre AS tarifa,
            CONCAT(e.nombre, ' ', e.apellido) AS entrenador
        FROM historial_de_pagos hp
        JOIN clientes c ON hp.id_cliente = c.id_cliente
        JOIN tarifas t ON hp.id_tarifa = t.id_tarifa
        JOIN entrenadores e ON t.id_entrenador = e.id_entrenador
        $whereSql
        ORDER BY hp.fecha_pago DESC
    ");
    $stmt->execute($params);

    while ($fila = $stmt->fetch()) {
        $venc = !empty($fila['fecha_vencimiento'])
            ? date('d/m/Y', strtotime($fila['fecha_vencimiento']))
            : date('d/m/Y', strtotime($fila['fecha_pago'] . ' +30 days'));

        fputcsv($salida, [
            str_pad((string)$fila['id_pago'], 6, '0', STR_PAD_LEFT),
            date('d/m/Y H:i', strtotime($fila['fecha_pago'])),
            $venc,
            $fila['DNI'],
            $fila['apellido'] . ', ' . $fila['nombre'],
            $fila['tarifa'],
            $fila['entrenador'],
            $fila['metodo_pago'] ?? 'Efectivo',
            number_format((float)$fila['monto'], 2, ',', ''),
        ], ';');
    }
} elseif ($tipo === 'asistencias') {
    $idCliente = isset($_GET['id_cliente']) ? (int)$_GET['id_cliente'] : 0;
    header('Content-Disposition: attachment; filename="reporte_asistencias_' . $fechaHoy . '.csv"');
    fputcsv($salida, ['ID Asistencia', 'Fecha', 'Hora', 'DNI', 'Cliente', 'Estado'], ';');

    $params = [];
    $whereSql = '';
    if ($idCliente > 0) {
        $whereSql = ' WHERE a.id_cliente = :id_cliente ';
        $params[':id_cliente'] = $idCliente;
    }

    $stmt = $pdo->prepare("
        SELECT a.id_asistencia, a.fecha_hora, a.asistio, c.DNI, c.nombre, c.apellido
        FROM historia_de_asistencia a
        JOIN clientes c ON a.id_cliente = c.id_cliente
        $whereSql
        ORDER BY a.fecha_hora DESC
    ");
    $stmt->execute($params);

    while ($fila = $stmt->fetch()) {
        $dt = new DateTime($fila['fecha_hora']);
        fputcsv($salida, [
            $fila['id_asistencia'],
            $dt->format('d/m/Y'),
            $dt->format('H:i:s'),
            $fila['DNI'],
            $fila['apellido'] . ', ' . $fila['nombre'],
            ((int)$fila['asistio'] === 1) ? 'Presente' : 'Ausente',
        ], ';');
    }
} else {
    // Por defecto: reporte de clientes con su estado de cuota
    header('Content-Disposition: attachment; filename="reporte_clientes_' . $fechaHoy . '.csv"');
    fputcsv($salida, ['ID Cliente', 'DNI', 'Apellido', 'Nombre', 'Email', 'Telefono', 'Estado Socio', 'Vencimiento Ultima Cuota', 'Situacion Cuota'], ';');

    $params = [];
    $whereSql = '';
    if ($busqueda !== '') {
        $whereSql = " WHERE (
            CONCAT(c.nombre, ' ', c.apellido) LIKE :b1 
            OR CONCAT(c.apellido, ' ', c.nombre) LIKE :b2 
            OR c.DNI LIKE :b3 
            OR c.email LIKE :b4 
            OR c.telefono LIKE :b5 
            OR e.Estado LIKE :b6
        ) ";
        $term = '%' . $busqueda . '%';
        $params = [
            ':b1' => $term, ':b2' => $term, ':b3' => $term,
            ':b4' => $term, ':b5' => $term, ':b6' => $term,
        ];
    }

    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            e.Estado,
            (
                SELECT COALESCE(hp.fecha_vencimiento, DATE_ADD(DATE(hp.fecha_pago), INTERVAL 30 DAY))
                FROM historial_de_pagos hp
                WHERE hp.id_cliente = c.id_cliente
                ORDER BY hp.fecha_pago DESC
                LIMIT 1
            ) AS ultimo_vencimiento
        FROM clientes c
        JOIN estado_cliente e ON c.id_estado = e.ID_est_clientes
        $whereSql
        ORDER BY c.apellido, c.nombre
    ");
    $stmt->execute($params);

    while ($fila = $stmt->fetch()) {
        $venc = $fila['ultimo_vencimiento'];
        if (empty($venc)) {
            $vencTexto = 'Sin pagos';
            $situacion = 'Sin cuota registrada';
        } else {
            $vencTexto = date('d/m/Y', strtotime($venc));
            $situacion = ($venc >= $fechaHoy) ? 'Al dia' : 'Vencida';
        }

        fputcsv($salida, [
            $fila['id_cliente'],
            $fila['DNI'],
            $fila['apellido'],
            $fila['nombre'],
            $fila['email'] ?? '',
            $fila['telefono'] ?? '',
            $fila['Estado'],
            $vencTexto,
            $situacion,
        ], ';');
    }
}

fclose($salida);
exit;

