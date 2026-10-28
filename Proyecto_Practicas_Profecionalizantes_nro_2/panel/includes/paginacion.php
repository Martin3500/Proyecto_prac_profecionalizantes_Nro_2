<?php
/**
 * Funciones auxiliares para paginación de listados en el panel administrativo.
 */

function calcularPaginacion(int $totalRegistros, int $porPagina = 15): array
{
    $paginaSolicitada = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($paginaSolicitada < 1) {
        $paginaSolicitada = 1;
    }

    $totalPaginas = max(1, (int)ceil($totalRegistros / $porPagina));
    $paginaActual = min($paginaSolicitada, $totalPaginas);
    $offset = ($paginaActual - 1) * $porPagina;

    $desde = $totalRegistros > 0 ? $offset + 1 : 0;
    $hasta = min($offset + $porPagina, $totalRegistros);

    return [
        'pagina'        => $paginaActual,
        'por_pagina'    => $porPagina,
        'total_paginas' => $totalPaginas,
        'offset'        => $offset,
        'total'         => $totalRegistros,
        'desde'         => $desde,
        'hasta'         => $hasta,
    ];
}

function urlPagina(int $numPagina): string
{
    $queryParams = $_GET;
    unset($queryParams['ok'], $queryParams['error']);
    $queryParams['pagina'] = $numPagina;
    return '?' . http_build_query($queryParams);
}

function renderizarPaginacion(array $pag): void
{
    if ($pag['total'] <= 0) {
        return;
    }
    ?>
    <div class="paginacion-barra">
        <div class="paginacion-info">
            Mostrando <strong><?= (int)$pag['desde'] ?>–<?= (int)$pag['hasta'] ?></strong> de <strong><?= (int)$pag['total'] ?></strong> registro<?= $pag['total'] === 1 ? '' : 's' ?>
            <span>(Página <?= (int)$pag['pagina'] ?> de <?= (int)$pag['total_paginas'] ?>)</span>
        </div>

        <?php if ($pag['total_paginas'] > 1): ?>
            <div class="paginacion-controles">
                <?php if ($pag['pagina'] > 1): ?>
                    <a href="<?= htmlspecialchars(urlPagina($pag['pagina'] - 1)) ?>" class="pag-btn">
                        &laquo; Anterior
                    </a>
                <?php else: ?>
                    <span class="pag-btn deshabilitado">&laquo; Anterior</span>
                <?php endif; ?>

                <?php
                $inicio = max(1, $pag['pagina'] - 2);
                $fin = min($pag['total_paginas'], $pag['pagina'] + 2);

                if ($inicio > 1) {
                    echo '<a href="' . htmlspecialchars(urlPagina(1)) . '" class="pag-btn">1</a>';
                    if ($inicio > 2) {
                        echo '<span class="pag-puntos">...</span>';
                    }
                }

                for ($i = $inicio; $i <= $fin; $i++):
                    if ($i === $pag['pagina']): ?>
                        <span class="pag-btn activo"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(urlPagina($i)) ?>" class="pag-btn"><?= $i ?></a>
                    <?php endif;
                endfor;

                if ($fin < $pag['total_paginas']) {
                    if ($fin < $pag['total_paginas'] - 1) {
                        echo '<span class="pag-puntos">...</span>';
                    }
                    echo '<a href="' . htmlspecialchars(urlPagina($pag['total_paginas'])) . '" class="pag-btn">' . (int)$pag['total_paginas'] . '</a>';
                }
                ?>

                <?php if ($pag['pagina'] < $pag['total_paginas']): ?>
                    <a href="<?= htmlspecialchars(urlPagina($pag['pagina'] + 1)) ?>" class="pag-btn">
                        Siguiente &raquo;
                    </a>
                <?php else: ?>
                    <span class="pag-btn deshabilitado">Siguiente &raquo;</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

