<?php
/** @var array $movimientos  @var int $saldo */
paginaInicio('Movimientos', ['atras' => '../paginas/CuentaPuntos.php', 'puntos' => $saldo]);
?>
<main class="contenedor contenedor-angosto">
    <div class="encabezado">
        <h1>Historial de movimientos</h1>
        <p>Puntos ganados por reciclar y canjes realizados.</p>
    </div>

    <?php if ($movimientos): ?>
        <div class="tarjeta" style="padding:4px 20px">
            <ul class="lista">
                <?php foreach ($movimientos as $m):
                    [$texto, $ic, $positivo] = describirMovimiento($m['tipo']); ?>
                    <li>
                        <span class="circulo <?= $positivo ? '' : 'secundario' ?>" style="width:40px;height:40px"><?= icono($ic, 'icono-sm') ?></span>
                        <span class="lista-cuerpo">
                            <span class="lista-titulo"><?= e($texto) ?></span><br>
                            <span class="lista-sub">
                                <?= e(date('d/m/Y h:i a', strtotime($m['fecha']))) ?>
                                <?php if (!empty($m['referencia'])): ?>
                                    · <a href="../paginas/ComprobanteCanje.php?ref=<?= e(urlencode($m['referencia'])) ?>">Ver comprobante</a>
                                <?php endif; ?>
                            </span>
                        </span>
                        <span class="lista-valor <?= $positivo ? 'positivo' : 'negativo' ?>">
                            <?= $positivo ? '+' : '−' ?><?= formatoPuntos($m['cantidad']) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="tarjeta centrado">
            <span class="circulo"><?= icono('history') ?></span>
            <p class="texto-sec">Aún no tienes movimientos. ¡Empieza a reciclar para ganar puntos!</p>
            <a class="boton" href="../paginas/RegistrarResiduos.php">Depositar residuos</a>
        </div>
    <?php endif; ?>
</main>
<?php paginaFin(); ?>
