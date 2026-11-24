<?php
/** @var array $bins, $tipos */
paginaInicio('Depositar', ['atras' => 'CuentaPuntos.php']);
?>
<main class="contenedor contenedor-angosto">
    <div class="encabezado">
        <h1>Nuevo depósito</h1>
        <p>Elige la papelera, el tipo de residuo y su peso.</p>
    </div>

    <form class="tarjeta" method="POST" action="procesar_deposito.php">
        <div class="paso">1. Papelera</div>
        <div class="campo">
            <div class="entrada">
                <?= icono('place') ?>
                <select name="bin" id="bin" required aria-label="Papelera">
                    <option value="" disabled <?= empty($bins) ? 'selected' : '' ?>>Selecciona una papelera</option>
                    <?php $preseleccion = false; foreach ($bins as $b):
                        $sel = (!$preseleccion && !$b['lleno']) ? 'selected' : '';
                        if ($sel) $preseleccion = true; ?>
                        <option value="<?= $b['id'] ?>" data-max="<?= $b['disponible'] ?>" data-cap="<?= $b['capacidad'] ?>" <?= $b['lleno'] ? 'disabled' : '' ?> <?= $sel ?>>
                            <?= e($b['ubicacion']) ?><?= $b['lleno'] ? ' (llena)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="ayuda" id="ayudaBin"></p>
        </div>

        <div class="paso">2. Tipo de residuo</div>
        <div class="chips" role="radiogroup" aria-label="Tipo de residuo">
            <?php $primero = true; foreach ($tipos as $clave => [$nombre, $pts]): ?>
                <label class="chip">
                    <input type="radio" name="tipo" value="<?= e($clave) ?>" data-pts="<?= $pts ?>" <?= $primero ? 'checked' : '' ?> required>
                    <span><?= e($nombre) ?></span>
                </label>
            <?php $primero = false; endforeach; ?>
        </div>

        <div class="paso">3. Peso</div>
        <div class="campo">
            <div class="entrada">
                <input type="number" step="0.01" min="0.01" name="peso" id="peso" placeholder="Peso del residuo" inputmode="decimal" required aria-label="Peso en kilogramos">
                <span class="sufijo">kg</span>
            </div>
        </div>

        <div class="resumen">
            <?= icono('eco', 'icono-sm') ?>
            <span id="estimacion">Ingresa el peso para calcular tus puntos</span>
        </div>

        <button type="submit" class="boton bloque" style="margin-top:24px">Confirmar depósito</button>
    </form>
</main>

<script>
(function () {
    const bin = document.getElementById('bin');
    const peso = document.getElementById('peso');
    const ayuda = document.getElementById('ayudaBin');
    const estimacion = document.getElementById('estimacion');
    const formato = new Intl.NumberFormat('es-CO');

    function actualizarBin() {
        const op = bin.options[bin.selectedIndex];
        const max = op ? op.getAttribute('data-max') : null;
        if (max) {
            peso.max = max;
            ayuda.textContent = 'Capacidad disponible: ' + formato.format(max) + ' kg de ' + formato.format(op.getAttribute('data-cap')) + ' kg';
        } else {
            peso.removeAttribute('max');
            ayuda.textContent = '';
        }
    }

    function actualizarEstimacion() {
        const tipo = document.querySelector('input[name="tipo"]:checked');
        const kg = parseFloat(peso.value);
        if (!tipo) return;
        const pts = parseInt(tipo.dataset.pts, 10);
        const nombre = tipo.nextElementSibling.textContent;
        estimacion.textContent = (kg > 0)
            ? 'Ganarás aproximadamente ' + formato.format(Math.round(kg * pts)) + ' eco-puntos (' + pts + ' pts por kg)'
            : nombre + ': ' + pts + ' pts por kg. Ingresa el peso para calcular tus puntos.';
    }

    bin.addEventListener('change', actualizarBin);
    peso.addEventListener('input', actualizarEstimacion);
    document.querySelectorAll('input[name="tipo"]').forEach(function (r) { r.addEventListener('change', actualizarEstimacion); });
    actualizarBin();
    actualizarEstimacion();
})();
</script>
<?php paginaFin(); ?>
