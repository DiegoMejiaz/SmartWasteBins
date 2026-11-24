<?php
/** @var string $nombre_usuario, $nombre_completo, $rango, $rol_usuario
 *  @var int $puntos, $id_usuario  @var float $total_peso  @var array|null $siguiente */
paginaInicio('Mi cuenta', ['puntos' => $puntos]);
$primer_nombre = explode(' ', trim($nombre_completo))[0];
?>
<main class="contenedor">
    <div class="encabezado">
        <h1>Hola, <?= e($primer_nombre) ?></h1>
        <p>@<?= e($nombre_usuario) ?> · ID <?= e($id_usuario) ?></p>
    </div>

    <!-- Saldo -->
    <section class="hero">
        <div class="hero-fila">
            <span class="hero-etiqueta">Saldo de eco-puntos</span>
            <span class="hero-insignia"><?= e($rango) ?></span>
        </div>
        <div class="hero-valor"><?= formatoPuntos($puntos) ?> pts</div>
        <div class="hero-sub"><?= formatoKg($total_peso) ?> kg reciclados · equivalen a <?= formatoCop($puntos * CANJE_COP_POR_PUNTO) ?> COP</div>
        <div class="progreso" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $siguiente ? $siguiente['progreso'] : 100 ?>">
            <span style="width: <?= $siguiente ? $siguiente['progreso'] : 100 ?>%"></span>
        </div>
        <div class="hero-nota">
            <?= $siguiente
                ? 'Te faltan ' . formatoPuntos($siguiente['faltan']) . ' pts para ' . e($siguiente['nombre'])
                : '¡Alcanzaste el rango máximo!' ?>
        </div>
    </section>

    <h2 class="seccion-titulo">¿Qué quieres hacer?</h2>
    <div class="acciones">
        <a class="accion" href="RegistrarResiduos.php">
            <span class="circulo"><?= icono('add') ?></span>
            <span class="accion-cuerpo"><span class="accion-titulo">Depositar residuos</span><br><span class="accion-sub">Registra tu reciclaje y gana puntos</span></span>
            <?= icono('chevron') ?>
        </a>
        <a class="accion" href="RetirarPuntos.php">
            <span class="circulo"><?= icono('wallet') ?></span>
            <span class="accion-cuerpo"><span class="accion-titulo">Canjear puntos</span><br><span class="accion-sub">Pásalos a Nequi o Daviplata</span></span>
            <?= icono('chevron') ?>
        </a>
        <a class="accion" href="MapaSmartBins.php">
            <span class="circulo secundario"><?= icono('map') ?></span>
            <span class="accion-cuerpo"><span class="accion-titulo">Papeleras inteligentes</span><br><span class="accion-sub">Ubicación y nivel de llenado</span></span>
            <?= icono('chevron') ?>
        </a>
        <a class="accion" href="Transacciones.php">
            <span class="circulo secundario"><?= icono('history') ?></span>
            <span class="accion-cuerpo"><span class="accion-titulo">Movimientos</span><br><span class="accion-sub">Historial de puntos y canjes</span></span>
            <?= icono('chevron') ?>
        </a>
        <a class="accion" href="Clasificacion.php">
            <span class="circulo"><?= icono('trophy') ?></span>
            <span class="accion-cuerpo"><span class="accion-titulo">Clasificación</span><br><span class="accion-sub">Top de recicladores</span></span>
            <?= icono('chevron') ?>
        </a>
        <button class="accion" type="button" onclick="window.abrirModalQR(<?= e(json_encode($nombre_completo)) ?>, <?= e(json_encode((string) $id_usuario)) ?>)">
            <span class="circulo secundario"><?= icono('qr') ?></span>
            <span class="accion-cuerpo"><span class="accion-titulo">Mi carné QR</span><br><span class="accion-sub">Identifícate en la estación</span></span>
            <?= icono('chevron') ?>
        </button>
        <?php if ($rol_usuario === 'admin'): ?>
        <a class="accion" href="AdminPanel.php">
            <span class="circulo" style="color:var(--admin)"><?= icono('shield') ?></span>
            <span class="accion-cuerpo"><span class="accion-titulo">Administración</span><br><span class="accion-sub">Usuarios y papeleras</span></span>
            <?= icono('chevron') ?>
        </a>
        <?php endif; ?>
    </div>

    <div class="acciones" style="margin-top:28px">
        <section>
            <h2 class="seccion-titulo" style="margin-top:0">Puntos por kilo</h2>
            <div class="tarjeta" style="padding:4px 20px">
                <ul class="lista">
                    <li><span class="lista-cuerpo">Plástico</span><span class="lista-valor">10 pts/kg</span></li>
                    <li><span class="lista-cuerpo">Orgánico</span><span class="lista-valor">15 pts/kg</span></li>
                    <li><span class="lista-cuerpo">Electrónico</span><span class="lista-valor">50 pts/kg</span></li>
                    <li><span class="lista-cuerpo">Papel y cartón</span><span class="lista-valor">5 pts/kg</span></li>
                </ul>
            </div>
        </section>
        <section>
            <h2 class="seccion-titulo" style="margin-top:0">Tip del día</h2>
            <div class="tarjeta" style="display:flex;gap:14px">
                <span class="circulo"><?= icono('idea') ?></span>
                <p style="margin:0">¿Sabías que reciclar una sola botella de plástico ahorra suficiente energía para encender una bombilla de 60 W durante 6 horas? ¡Sigue sumando puntos!</p>
            </div>
        </section>
    </div>
</main>
<?php paginaFin(); ?>
