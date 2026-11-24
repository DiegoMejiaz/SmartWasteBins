<?php
/** @var array $usuarios, $bins  @var int $total_users, $total_points, $total_canjes  @var float $total_kg  @var string $search */
paginaInicio('Administración', ['atras' => 'CuentaPuntos.php']);
?>
<main class="contenedor">
    <div class="encabezado">
        <h1>Panel de administración</h1>
        <p>Gestiona usuarios y el estado de las papeleras.</p>
    </div>

    <div class="estadisticas">
        <div class="tarjeta estadistica"><b><?= formatoPuntos($total_users) ?></b><span>Usuarios</span></div>
        <div class="tarjeta estadistica"><b><?= formatoPuntos($total_points) ?></b><span>Puntos en circulación</span></div>
        <div class="tarjeta estadistica"><b><?= formatoKg($total_kg) ?> kg</b><span>Reciclaje total</span></div>
        <div class="tarjeta estadistica"><b><?= formatoCop($total_canjes) ?></b><span>Canjeado (COP)</span></div>
    </div>

    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:28px 0 12px">
        <h2 class="seccion-titulo" style="margin:0;flex:1">Usuarios</h2>
        <form class="buscador" method="GET" action="AdminPanel.php" style="flex:1;min-width:260px">
            <div class="campo"><div class="entrada"><?= icono('search') ?><input type="search" name="search" placeholder="Nombre, usuario, correo o ID" value="<?= e($search) ?>" aria-label="Buscar usuarios"></div></div>
            <button class="boton tonal" type="submit">Buscar</button>
        </form>
    </div>

    <?php if ($usuarios): ?>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead><tr><th>ID</th><th>Nombre</th><th>Usuario / rol</th><th>Puntos</th><th>Registro</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($usuarios as $row): ?>
                    <tr>
                        <td>#<?= e($row['id_usuario']) ?></td>
                        <td><b><?= e($row['nombre']) ?></b><div class="lista-sub"><?= e($row['correo']) ?></div></td>
                        <td>@<?= e($row['usuario']) ?><br><span class="insignia <?= $row['rol'] === 'admin' ? 'admin' : 'neutra' ?>"><?= e($row['rol']) ?></span></td>
                        <td class="positivo"><b><?= formatoPuntos($row['saldo_actual']) ?></b></td>
                        <td class="texto-sec"><?= $row['fecha_registro'] ? e(date('d/m/Y', strtotime($row['fecha_registro']))) : '—' ?></td>
                        <td>
                            <?php if ((string) $row['id_usuario'] !== (string) $_SESSION['user_id']): ?>
                                <button class="boton peligro pequeno" type="button"
                                        onclick="eliminarUsuario(<?= (int) $row['id_usuario'] ?>, <?= e(json_encode($row['usuario'])) ?>)">Borrar</button>
                            <?php else: ?>
                                <span class="texto-sec">(tú)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="tarjeta centrado texto-sec">
            No se encontraron resultados para “<b><?= e($search) ?></b>”. <a href="AdminPanel.php">Ver todos</a>
        </div>
    <?php endif; ?>

    <h2 class="seccion-titulo">Papeleras</h2>
    <div class="tabla-envoltura">
        <table class="tabla">
            <thead><tr><th>ID</th><th>Ubicación</th><th style="min-width:200px">Nivel / capacidad</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($bins as $bin):
                $pct = $bin['capacidad_maxima'] > 0 ? ($bin['nivel_actual'] / $bin['capacidad_maxima']) * 100 : 0;
                $lleno = $bin['estado'] === 'Bloqueado' || $pct >= 100;
                $clase = $lleno ? 'llena' : ($pct >= 75 ? 'alerta' : 'ok'); ?>
                <tr>
                    <td>#<?= (int) $bin['id_bin'] ?></td>
                    <td><b><?= e($bin['ubicacion']) ?></b></td>
                    <td>
                        <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:6px">
                            <span><?= formatoKg($bin['nivel_actual']) ?> / <?= formatoKg($bin['capacidad_maxima']) ?> kg</span>
                            <span class="texto-sec"><?= round($pct, 1) ?>%</span>
                        </div>
                        <div class="progreso"><span style="width:<?= min(100, $pct) ?>%;background:var(--<?= $clase ?>)"></span></div>
                    </td>
                    <td><span class="insignia <?= $clase ?>"><?= $lleno ? 'Llena / bloqueada' : 'Activa' ?></span></td>
                    <td><a class="boton tonal pequeno" href="vaciar_bin.php?id=<?= (int) $bin['id_bin'] ?>" onclick="return confirm('¿Vaciar esta papelera?')">Vaciar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<script>
function eliminarUsuario(id, usuario) {
    if (confirm("¿Eliminar permanentemente al usuario @" + usuario + "?\nEsta acción no se puede deshacer.")) {
        window.location.href = "eliminar_usuario_admin.php?id=" + encodeURIComponent(id);
    }
}
</script>
<?php paginaFin(); ?>
