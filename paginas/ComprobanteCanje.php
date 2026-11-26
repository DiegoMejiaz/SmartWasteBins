<?php
// ComprobanteCanje.php - Comprobante de un canje (solo visible para su dueño)
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";
require_once __DIR__ . "/../includes/logica_puntos.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.html");
    exit();
}

$id_usuario = intval($_SESSION['user_id']);
$referencia = (string) ($_GET['ref'] ?? '');
$comprobante = preg_match('/^SWB-\d{6}-[A-F0-9]{6}$/', $referencia) ? obtenerCanje($conn, $referencia, $id_usuario) : null;
if (!$comprobante) {
    header("Location: ../paginas/Transacciones.php");
    exit();
}
$es_nuevo = isset($_GET['nuevo']);

$stmt = mysqli_prepare($conn, "SELECT IFNULL(saldo_actual, 0) FROM cuenta_puntos WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$saldo = (int) (mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0] ?? 0);

$fecha = date('d/m/Y h:i a', strtotime($comprobante['fecha']));

paginaInicio('Comprobante', [
    'atras' => $es_nuevo ? '../paginas/CuentaPuntos.php' : '../paginas/Transacciones.php',
    'puntos' => $saldo,
    'scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'],
]);
?>
<main class="contenedor contenedor-angosto">
    <div id="comprobante" class="centrado" style="padding:24px 0 8px">
        <span class="circulo grande"><?= icono('check') ?></span>
        <h1 style="font-size:1.6rem;margin-top:16px"><?= $es_nuevo ? '¡Canje aprobado!' : 'Comprobante de canje' ?></h1>
        <div class="comprobante-valor"><?= formatoCop($comprobante['valor_cop']) ?> COP</div>
        <p class="texto-sec" style="margin:4px 0 24px">Enviados a <?= e($comprobante['metodo_nombre']) ?> · <?= e($comprobante['telefono_enmascarado']) ?></p>

        <div class="tarjeta" style="text-align:left;padding:4px 20px">
            <ul class="detalle">
                <li><span>Referencia</span><b><?= e($comprobante['referencia']) ?></b></li>
                <li><span>Fecha</span><b><?= e($fecha) ?></b></li>
                <li><span>Titular</span><b><?= e($_SESSION['user_name'] ?? '') ?></b></li>
                <li><span>Puntos canjeados</span><b><?= formatoPuntos($comprobante['puntos']) ?> pts</b></li>
                <li><span>Billetera</span><b><?= e($comprobante['metodo_nombre']) ?></b></li>
                <li><span>Estado</span><b><span class="insignia ok">Aprobado</span></b></li>
            </ul>
        </div>
        <p class="ayuda" style="margin-top:12px">Pasarela de demostración: no se transfiere dinero real.</p>
    </div>

    <div class="botones no-imprimir">
        <button type="button" class="boton contorno bloque" id="btnPdf"><?= icono('download', 'icono-sm') ?> Descargar PDF</button>
        <a class="boton bloque" href="../paginas/CuentaPuntos.php">Volver al inicio</a>
    </div>
</main>
<script>
document.getElementById('btnPdf').addEventListener('click', function () {
    if (typeof html2pdf === 'undefined') { window.print(); return; }
    const btn = this;
    btn.disabled = true;
    html2pdf().set({
        margin: 0.5,
        filename: 'Comprobante_<?= e($comprobante['referencia']) ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, backgroundColor: getComputedStyle(document.body).backgroundColor },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    }).from(document.getElementById('comprobante')).save().then(function () { btn.disabled = false; });
});
</script>
<?php paginaFin(); ?>
