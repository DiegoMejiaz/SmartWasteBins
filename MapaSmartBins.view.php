<?php
/** @var array $smartbins  @var int $disponibles */
paginaInicio('Red de papeleras', ['atras' => 'CuentaPuntos.php']);
$etiquetas = ['ok' => 'Disponible', 'alerta' => 'Casi llena', 'llena' => 'Llena'];
?>
<main class="contenedor">
    <div class="encabezado">
        <h1>Papeleras inteligentes</h1>
        <p><?= count($smartbins) ?> papeleras en Cartagena · <?= $disponibles ?> disponibles</p>
    </div>

    <div class="mapa">
        <img src="mapa_cartagena.jpg" alt="Mapa del Centro Histórico de Cartagena con las papeleras">
        <?php foreach ($smartbins as $bin): if ($bin['x'] === null) continue; ?>
            <div class="marcador <?= $bin['estado'] ?>" style="left:<?= $bin['x'] ?>%;top:<?= $bin['y'] ?>%" tabindex="0"
                 aria-label="<?= e($bin['nombre']) ?>: <?= $bin['nivel'] ?>% lleno">
                <div class="globo">
                    <b><?= e($bin['nombre']) ?></b><br>
                    <span class="texto-sec"><?= formatoKg($bin['kg_actual']) ?> de <?= formatoKg($bin['kg_max']) ?> kg · <?= $bin['nivel'] ?>%</span>
                    <div class="progreso" style="margin-top:8px"><span style="width:<?= min(100, $bin['nivel']) ?>%;background:var(--<?= $bin['estado'] ?>)"></span></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="leyenda">
        <span><i style="background:var(--ok)"></i>Disponible</span>
        <span><i style="background:var(--alerta)"></i>Casi llena (75 %+)</span>
        <span><i style="background:var(--llena)"></i>Llena / por recolectar</span>
    </div>

    <h2 class="seccion-titulo">Estado de cada papelera</h2>
    <div class="acciones">
        <?php foreach ($smartbins as $bin): ?>
            <div class="tarjeta" style="padding:16px">
                <div style="display:flex;align-items:center;gap:14px">
                    <span class="circulo" style="width:44px;height:44px;color:var(--<?= $bin['estado'] ?>);background:color-mix(in srgb,var(--<?= $bin['estado'] ?>) 14%,transparent)"><?= icono('bin', 'icono-sm') ?></span>
                    <span class="lista-cuerpo">
                        <span class="lista-titulo"><?= e($bin['nombre']) ?></span><br>
                        <span class="lista-sub">Papelera #<?= $bin['id'] ?></span>
                    </span>
                    <span class="insignia <?= $bin['estado'] ?>"><?= $etiquetas[$bin['estado']] ?></span>
                </div>
                <div class="progreso" style="margin-top:16px"><span style="width:<?= min(100, $bin['nivel']) ?>%;background:var(--<?= $bin['estado'] ?>)"></span></div>
                <div style="display:flex;justify-content:space-between;margin-top:8px">
                    <span class="texto-sec"><?= formatoKg($bin['kg_actual']) ?> de <?= formatoKg($bin['kg_max']) ?> kg</span>
                    <b><?= round($bin['nivel']) ?>%</b>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php paginaFin(); ?>
