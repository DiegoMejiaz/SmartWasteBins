<?php
/** @var array $top */
paginaInicio('Clasificación', ['atras' => '../paginas/CuentaPuntos.php']);
$medallas = [1 => '#C9A227', 2 => '#9AA5AE', 3 => '#B87333'];
?>
<main class="contenedor contenedor-angosto">
    <div class="encabezado">
        <h1>Top recicladores</h1>
        <p>Los 10 ciudadanos con más eco-puntos.</p>
    </div>

    <?php if ($top): ?>
    <div class="tarjeta" style="padding:4px 20px">
        <ol class="lista">
            <?php foreach ($top as $i => $row): $pos = $i + 1;
                $soy_yo = (string) $row['id_usuario'] === (string) ($_SESSION['user_id'] ?? ''); ?>
                <li <?= $soy_yo ? 'style="background:color-mix(in srgb,var(--primario) 8%,transparent);margin:0 -20px;padding-left:20px;padding-right:20px"' : '' ?>>
                    <span class="circulo" style="width:40px;height:40px;font-weight:700;<?= isset($medallas[$pos]) ? 'background:' . $medallas[$pos] . ';color:#fff' : '' ?>"><?= $pos ?></span>
                    <span class="lista-cuerpo">
                        <span class="lista-titulo"><?= e($row['nombre']) ?><?= $soy_yo ? ' (tú)' : '' ?></span><br>
                        <span class="lista-sub">@<?= e($row['usuario']) ?> · <?= e(obtenerRango($row['saldo_actual'])) ?></span>
                    </span>
                    <span class="lista-valor positivo"><?= formatoPuntos($row['saldo_actual']) ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <?php else: ?>
        <div class="tarjeta centrado texto-sec">Todavía no hay recicladores en la clasificación.</div>
    <?php endif; ?>

    <h2 class="seccion-titulo">Niveles de reconocimiento</h2>
    <div class="tarjeta" style="padding:4px 20px">
        <ul class="lista">
            <?php foreach (RANGOS as $i => [$minimo, $nombre]):
                $hasta = isset(RANGOS[$i + 1]) ? ' – ' . formatoPuntos(RANGOS[$i + 1][0] - 1) : '+'; ?>
                <li><span class="lista-cuerpo lista-titulo"><?= e($nombre) ?></span><span class="lista-sub"><?= formatoPuntos($minimo) . $hasta ?> pts</span></li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>
<?php paginaFin(); ?>
