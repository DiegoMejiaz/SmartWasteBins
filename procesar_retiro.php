<?php
// procesar_retiro.php - Ejecuta el canje de la pasarela web y redirige al comprobante
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";
require_once __DIR__ . "/logica_puntos.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // cualquier fallo SQL revierte el canje

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: RetirarPuntos.php");
    exit();
}

function volverConError(string $mensaje): void {
    $_SESSION['error_canje'] = $mensaje;
    header("Location: RetirarPuntos.php");
    exit();
}

if (!csrfValido($_POST['csrf'] ?? null)) {
    volverConError("La sesión del formulario expiró. Vuelve a intentarlo.");
}

try {
    $comprobante = canjearPuntos(
        $conn,
        intval($_SESSION['user_id']),
        (string) ($_POST['metodo'] ?? ''),
        (string) ($_POST['telefono'] ?? ''),
        intval($_POST['cantidad'] ?? 0)
    );
} catch (CanjeException $e) {
    volverConError($e->getMessage());
} catch (Throwable $e) {
    error_log("[SmartWasteBin] Canje fallido: " . $e->getMessage());
    volverConError("Ocurrió un error al procesar el pago. No se descontaron puntos.");
}

// Patrón POST → redirección → GET: recargar el comprobante no repite el canje
header("Location: ComprobanteCanje.php?ref=" . urlencode($comprobante['referencia']) . "&nuevo=1");
exit();
