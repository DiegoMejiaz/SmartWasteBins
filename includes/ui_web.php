<?php
// ui_web.php - Plantilla común de la web (mismo diseño que la app móvil):
// cabecera con barra superior, menú de usuario, íconos, alertas y protección CSRF.

/** Escapa texto para HTML. */
function e($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Ícono del sprite iconos.svg. */
function icono(string $id, string $clase = ''): string {
    return '<svg class="icono ' . e($clase) . '" aria-hidden="true"><use href="../assets/img/iconos.svg#' . e($id) . '"></use></svg>';
}

function formatoPuntos($n): string {
    return number_format((float) $n, 0, ',', '.');
}

function formatoCop($n): string {
    return '$' . number_format((float) $n, 0, ',', '.');
}

function formatoKg($n): string {
    $n = (float) $n;
    return ($n == floor($n)) ? number_format($n, 0, ',', '.') : number_format($n, 1, ',', '.');
}

// ---------------------------------------------------------------- CSRF

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfCampo(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function csrfValido(?string $token): bool {
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

// ---------------------------------------------------------------- Estructura de página

/**
 * Abre la página.
 * $opciones:
 *   'atras'   => URL para la flecha de volver (páginas internas). Sin ella se muestra la marca.
 *   'puntos'  => saldo a mostrar en la píldora de la barra (opcional).
 *   'swal'    => true para cargar SweetAlert2.
 *   'scripts' => array de URLs de scripts extra para el <head>.
 */
function paginaInicio(string $titulo, array $opciones = []): void {
    $atras = $opciones['atras'] ?? null;
    $puntos = $opciones['puntos'] ?? null;
    $nombre = $_SESSION['user_name'] ?? '';
    $id = $_SESSION['user_id'] ?? '';
    $esAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#F5F8F4" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0F1410" media="(prefers-color-scheme: dark)">
    <title><?= e($titulo) ?> · Smart Waste Bin</title>
    <link rel="stylesheet" href="../assets/css/eco_widgets.css">
    <link rel="stylesheet" href="../assets/css/app.css">
    <?php if (!empty($opciones['swal'])): ?><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script><?php endif; ?>
    <?php foreach ($opciones['scripts'] ?? [] as $src): ?><script src="<?= e($src) ?>"></script><?php endforeach; ?>
</head>
<body>
<header class="barra">
    <div class="barra-interior">
        <?php if ($atras): ?>
            <a class="boton-icono" href="<?= e($atras) ?>" aria-label="Volver"><?= icono('back') ?></a>
            <div class="barra-titulo"><?= e($titulo) ?></div>
        <?php else: ?>
            <a class="marca barra-titulo" href="../paginas/CuentaPuntos.php"><?= icono('eco') ?> Smart Waste Bin</a>
        <?php endif; ?>

        <?php if ($puntos !== null): ?>
            <span class="pildora-puntos"><?= icono('eco', 'icono-sm') ?><?= formatoPuntos($puntos) ?> pts</span>
        <?php endif; ?>

        <?php if ($id !== ''): ?>
        <div class="menu" id="menuUsuario">
            <button class="boton-icono" type="button" aria-label="Menú de la cuenta" aria-haspopup="true" aria-expanded="false" id="botonMenu"><?= icono('person') ?></button>
            <div class="menu-lista" role="menu">
                <div class="menu-item" style="cursor:default"><b><?= e($nombre) ?></b></div>
                <div class="menu-separador"></div>
                <?php if ($esAdmin): ?>
                    <a class="menu-item" href="../paginas/AdminPanel.php" style="color:var(--admin)"><?= icono('shield') ?> Panel de administración</a>
                <?php endif; ?>
                <button class="menu-item" type="button" onclick="window.abrirModalQR(<?= e(json_encode($nombre)) ?>, <?= e(json_encode((string) $id)) ?>)"><?= icono('qr') ?> Mi carné QR</button>
                <button class="menu-item" type="button" onclick="window.abrirEcoModal('ecoModalTestimonios')"><?= icono('star') ?> Calificar app</button>
                <button class="menu-item" type="button" onclick="window.abrirEcoModal('ecoModalSoporte')"><?= icono('soporte') ?> Soporte técnico</button>
                <a class="menu-item" href="../paginas/EditarCuenta.php"><?= icono('edit') ?> Editar cuenta</a>
                <div class="menu-separador"></div>
                <button class="menu-item peligro" type="button" onclick="confirmarEliminacion()"><?= icono('bin') ?> Eliminar cuenta</button>
                <a class="menu-item" href="../acciones/cerrar_sesion.php"><?= icono('logout') ?> Cerrar sesión</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>
<?php
}

function paginaFin(): void {
    ?>
<script>
(function () {
    const menu = document.getElementById('menuUsuario');
    if (menu) {
        const boton = document.getElementById('botonMenu');
        boton.addEventListener('click', function (e) {
            e.stopPropagation();
            const abierto = menu.classList.toggle('abierto');
            boton.setAttribute('aria-expanded', abierto);
        });
        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target)) { menu.classList.remove('abierto'); boton.setAttribute('aria-expanded', 'false'); }
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') menu.classList.remove('abierto'); });
    }
})();
function confirmarEliminacion() {
    if (confirm("¿Seguro que deseas eliminar tu cuenta? Esta acción es irreversible y perderás todos tus puntos.")) {
        window.location.href = "../acciones/eliminar_cuenta.php";
    }
}
</script>
<script src="../assets/js/eco_widgets.js"></script>
</body>
</html>
<?php
}

/**
 * Página completa que solo muestra una alerta (SweetAlert2 con el estilo de la app) y redirige.
 * $html se inserta como HTML: escapa los datos del usuario antes de pasarlos.
 */
function paginaAlerta(string $icono, string $titulo, string $html, string $redirigir, string $boton = 'Continuar', ?int $temporizadorMs = null): void {
    $config = [
        'icon' => $icono,
        'titleText' => $titulo, // texto plano: nunca se interpreta como HTML
        'html' => $html,
        'confirmButtonText' => $boton,
        'allowOutsideClick' => false,
    ];
    if ($temporizadorMs) {
        $config['timer'] = $temporizadorMs;
        $config['timerProgressBar'] = true;
        $config['showConfirmButton'] = false;
    }
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> · Smart Waste Bin</title>
    <link rel="stylesheet" href="../assets/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
</head>
<body>
<script>
    Swal.fire(<?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>)
        .then(function () { window.location.href = <?= json_encode($redirigir) ?>; });
</script>
</body>
</html>
<?php
    exit();
}
