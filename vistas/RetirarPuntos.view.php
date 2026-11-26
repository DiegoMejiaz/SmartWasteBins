<?php
/** @var int $puntos_actuales  @var string $correo_actual, $usuario_actual  @var string|null $error_canje */
paginaInicio('Canjear', ['atras' => '../paginas/CuentaPuntos.php', 'puntos' => $puntos_actuales]);
$puede_canjear = $puntos_actuales >= CANJE_MINIMO_PUNTOS;
?>
<style>
    .pasos { display: flex; gap: 8px; margin-bottom: 16px; }
    .pasos li { flex: 1; list-style: none; font-size: .85rem; color: var(--texto-sec); }
    .pasos li::before { content: ""; display: block; height: 4px; border-radius: 2px; background: var(--superficie-var); margin-bottom: 6px; }
    .pasos li.activo, .pasos li.hecho { color: var(--texto); font-weight: 600; }
    .pasos li.activo::before, .pasos li.hecho::before { background: var(--primario); }
    .js .etapa { display: none; }
    .js .etapa.activa { display: block; }
    .metodo-logo { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: .9rem; }
</style>

<main class="contenedor contenedor-angosto">
    <div class="encabezado">
        <h1>Canjear puntos</h1>
        <p>1 punto = <?= formatoCop(CANJE_COP_POR_PUNTO) ?> COP · canje mínimo de <?= formatoPuntos(CANJE_MINIMO_PUNTOS) ?> pts.</p>
    </div>

    <section class="hero" style="padding:20px">
        <div class="hero-etiqueta">Saldo disponible</div>
        <div class="hero-valor" style="font-size:2.2rem"><?= formatoPuntos($puntos_actuales) ?> pts</div>
        <div class="hero-sub">Equivalen a <?= formatoCop($puntos_actuales * CANJE_COP_POR_PUNTO) ?> COP</div>
    </section>

    <?php if ($error_canje): ?>
        <div class="aviso" role="alert" style="margin-top:16px"><?= icono('error') ?><div><b>Canje no realizado.</b> <?= e($error_canje) ?></div></div>
    <?php endif; ?>

    <?php if (!$puede_canjear): ?>
        <div class="tarjeta centrado" style="margin-top:16px">
            <span class="circulo"><?= icono('eco') ?></span>
            <h2 style="font-size:1.1rem;margin-top:12px">Aún no puedes canjear</h2>
            <p class="texto-sec">Necesitas al menos <?= formatoPuntos(CANJE_MINIMO_PUNTOS) ?> pts. ¡Sigue reciclando!</p>
            <a class="boton" href="../paginas/RegistrarResiduos.php">Depositar residuos</a>
        </div>
    <?php else: ?>
    <form class="tarjeta" id="formCanje" method="POST" action="../acciones/procesar_retiro.php" style="margin-top:16px" novalidate>
        <?= csrfCampo() ?>
        <ol class="pasos" id="indicadorPasos" aria-label="Progreso del canje" style="padding:0;margin-top:0">
            <li class="activo">1. Monto</li><li>2. Destino</li><li>3. Confirmar</li>
        </ol>

        <!-- Paso 1: monto -->
        <div class="etapa activa" data-etapa="1">
            <div class="paso">¿Cuántos puntos quieres canjear?</div>
            <div class="campo">
                <div class="entrada">
                    <?= icono('eco') ?>
                    <input type="number" id="cantidad" name="cantidad" min="<?= CANJE_MINIMO_PUNTOS ?>" max="<?= $puntos_actuales ?>" step="1" inputmode="numeric" placeholder="Puntos" required aria-describedby="errorCantidad">
                    <span class="sufijo">pts</span>
                </div>
                <p class="ayuda error oculto" id="errorCantidad"></p>
            </div>
            <div class="chips">
                <button type="button" class="chip" data-monto="<?= CANJE_MINIMO_PUNTOS ?>">Mínimo · <?= formatoPuntos(CANJE_MINIMO_PUNTOS) ?></button>
                <?php if (intdiv($puntos_actuales, 2) >= CANJE_MINIMO_PUNTOS): ?>
                    <button type="button" class="chip" data-monto="<?= intdiv($puntos_actuales, 2) ?>">50 % · <?= formatoPuntos(intdiv($puntos_actuales, 2)) ?></button>
                <?php endif; ?>
                <button type="button" class="chip" data-monto="<?= $puntos_actuales ?>">Todo · <?= formatoPuntos($puntos_actuales) ?></button>
            </div>
            <div class="resumen"><?= icono('wallet', 'icono-sm') ?><span id="equivalencia">Ingresa los puntos para ver cuánto recibirás</span></div>
            <button type="button" class="boton bloque" data-siguiente="2" style="margin-top:24px">Continuar</button>
        </div>

        <!-- Paso 2: destino -->
        <div class="etapa" data-etapa="2">
            <div class="paso">Billetera de destino</div>
            <div class="segmentado" role="radiogroup" aria-label="Billetera">
                <label><input type="radio" name="metodo" value="NEQUI" checked><span><span class="metodo-logo" style="background:#200020">N</span> Nequi</span></label>
                <label><input type="radio" name="metodo" value="DAVIPLATA"><span><span class="metodo-logo" style="background:#E30613">D</span> Daviplata</span></label>
            </div>
            <div class="campo" style="margin-top:16px">
                <label for="telefono">Celular asociado a la billetera</label>
                <div class="entrada">
                    <?= icono('phone') ?>
                    <input type="tel" id="telefono" name="telefono" placeholder="3001234567" inputmode="numeric" maxlength="10" pattern="3[0-9]{9}" autocomplete="tel-national" required aria-describedby="errorTelefono">
                </div>
                <p class="ayuda" id="errorTelefono">10 dígitos, empieza por 3</p>
            </div>
            <div class="campo">
                <label>Titular</label>
                <div class="entrada"><?= icono('person') ?><input type="text" value="@<?= e($usuario_actual) ?> · <?= e($correo_actual) ?>" readonly></div>
            </div>
            <div class="botones" style="flex-direction:row">
                <button type="button" class="boton contorno" data-siguiente="1" style="flex:1">Atrás</button>
                <button type="button" class="boton" data-siguiente="3" style="flex:2">Continuar</button>
            </div>
        </div>

        <!-- Paso 3: confirmación -->
        <div class="etapa" data-etapa="3">
            <div class="paso">Confirma tu canje</div>
            <div class="centrado" style="margin:8px 0 16px">
                <div class="texto-sec">Recibirás</div>
                <div class="comprobante-valor" id="resValor">$0</div>
            </div>
            <ul class="detalle">
                <li><span>Puntos a canjear</span><b id="resPuntos">—</b></li>
                <li><span>Destino</span><b id="resDestino">—</b></li>
                <li><span>Saldo después del canje</span><b id="resSaldo">—</b></li>
            </ul>
            <p class="ayuda" style="margin-top:12px">Esta operación no se puede deshacer.</p>
            <div class="botones" style="flex-direction:row">
                <button type="button" class="boton contorno" data-siguiente="2" style="flex:1">Atrás</button>
                <button type="submit" class="boton" id="btnConfirmar" style="flex:2"><?= icono('check', 'icono-sm') ?> Confirmar canje</button>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <p class="ayuda centrado" style="margin-top:16px">Pasarela de demostración: no se transfiere dinero real.</p>
</main>

<?php if ($puede_canjear): ?>
<script>
(function () {
    document.documentElement.classList.add('js');
    const SALDO = <?= $puntos_actuales ?>, MINIMO = <?= CANJE_MINIMO_PUNTOS ?>, COP = <?= CANJE_COP_POR_PUNTO ?>;
    const f = new Intl.NumberFormat('es-CO');
    const form = document.getElementById('formCanje');
    const cantidad = document.getElementById('cantidad');
    const telefono = document.getElementById('telefono');
    const errCantidad = document.getElementById('errorCantidad');
    const errTelefono = document.getElementById('errorTelefono');
    const etapas = form.querySelectorAll('.etapa');
    const indicador = document.querySelectorAll('#indicadorPasos li');

    function puntos() { return parseInt(cantidad.value, 10) || 0; }
    function metodo() { return form.querySelector('input[name="metodo"]:checked').value; }

    function validarCantidad() {
        const p = puntos();
        let msg = '';
        if (p < MINIMO) msg = 'El mínimo es ' + f.format(MINIMO) + ' pts';
        else if (p > SALDO) msg = 'Solo tienes ' + f.format(SALDO) + ' pts';
        errCantidad.textContent = msg;
        errCantidad.classList.toggle('oculto', !msg);
        return !msg;
    }
    function validarTelefono() {
        telefono.value = telefono.value.replace(/\D/g, '').slice(0, 10);
        const ok = /^3\d{9}$/.test(telefono.value);
        errTelefono.textContent = ok ? '10 dígitos, empieza por 3' : 'Debe tener 10 dígitos y empezar por 3';
        errTelefono.classList.toggle('error', !ok);
        return ok;
    }
    function actualizar() {
        const p = puntos();
        document.getElementById('equivalencia').textContent = p > 0
            ? 'Recibirás $' + f.format(p * COP) + ' COP'
            : 'Ingresa los puntos para ver cuánto recibirás';
    }
    function irA(n) {
        if (n >= 2 && !validarCantidad()) { cantidad.focus(); return; }
        if (n >= 3 && !validarTelefono()) { telefono.focus(); return; }
        if (n === 3) {
            const p = puntos(), t = telefono.value;
            document.getElementById('resValor').textContent = '$' + f.format(p * COP) + ' COP';
            document.getElementById('resPuntos').textContent = f.format(p) + ' pts';
            document.getElementById('resDestino').textContent = (metodo() === 'NEQUI' ? 'Nequi' : 'Daviplata') + ' · ' + t.slice(0, 3) + ' *** ' + t.slice(6);
            document.getElementById('resSaldo').textContent = f.format(SALDO - p) + ' pts';
        }
        etapas.forEach(function (e) { e.classList.toggle('activa', e.dataset.etapa == n); });
        indicador.forEach(function (li, i) {
            li.classList.toggle('activo', i + 1 === n);
            li.classList.toggle('hecho', i + 1 < n);
        });
        const primerCampo = form.querySelector('.etapa.activa input:not([type=hidden]):not([readonly])');
        if (primerCampo && n < 3) primerCampo.focus();
    }

    form.querySelectorAll('[data-monto]').forEach(function (b) {
        b.addEventListener('click', function () { cantidad.value = b.dataset.monto; actualizar(); validarCantidad(); });
    });
    form.querySelectorAll('[data-siguiente]').forEach(function (b) {
        b.addEventListener('click', function () { irA(parseInt(b.dataset.siguiente, 10)); });
    });
    cantidad.addEventListener('input', function () { actualizar(); if (!errCantidad.classList.contains('oculto')) validarCantidad(); });
    telefono.addEventListener('input', function () { if (errTelefono.classList.contains('error')) validarTelefono(); });
    form.addEventListener('keydown', function (e) {
        // Enter avanza de paso en lugar de enviar el formulario antes de tiempo
        if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
            const actual = parseInt(form.querySelector('.etapa.activa').dataset.etapa, 10);
            if (actual < 3) { e.preventDefault(); irA(actual + 1); }
        }
    });
    form.addEventListener('submit', function (e) {
        if (!validarCantidad() || !validarTelefono()) {
            e.preventDefault();
            e.stopPropagation(); // evita que eco_widgets muestre la pantalla de carga
            irA(validarCantidad() ? 2 : 1);
            return;
        }
        const btn = document.getElementById('btnConfirmar');
        btn.disabled = true;
        btn.textContent = 'Procesando pago…';
        if (window.showEcoLoader) window.showEcoLoader('Procesando pago…', 'Conectando con la billetera digital');
    });
})();
</script>
<?php endif; ?>
<?php paginaFin(); ?>
