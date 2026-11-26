<?php
// Transacciones.php - Historial de movimientos de puntos
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.html");
    exit();
}

$id_usuario = intval($_SESSION['user_id']);

$stmt = mysqli_prepare($conn,
    "SELECT tp.id_transaccion, tp.tipo, tp.cantidad, tp.fecha, c.referencia
     FROM transaccion_puntos tp
     JOIN cuenta_puntos cp ON tp.id_cuenta = cp.id_cuenta
     LEFT JOIN canje c ON c.id_transaccion = tp.id_transaccion
     WHERE cp.id_usuario = ?
     ORDER BY tp.fecha DESC, tp.id_transaccion DESC");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$movimientos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$stmt = mysqli_prepare($conn, "SELECT IFNULL(saldo_actual, 0) FROM cuenta_puntos WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$saldo = (int) (mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0] ?? 0);

/** Texto legible, ícono y signo de cada tipo de movimiento. */
function describirMovimiento(string $tipo): array {
    if (strpos($tipo, 'CANJE_') === 0 || strpos($tipo, 'RETIRO') !== false) {
        $destino = strpos($tipo, 'DAVIPLATA') !== false ? 'Daviplata' : (strpos($tipo, 'NEQUI') !== false ? 'Nequi' : 'billetera');
        return ['Canje a ' . $destino, 'wallet', false];
    }
    $nombres = ['PLASTICO' => 'plástico', 'ORGANICO' => 'orgánico', 'ELECTRONICO' => 'electrónico', 'PAPEL_CARTON' => 'papel y cartón'];
    if (strpos($tipo, 'DEPOSITO_') === 0) {
        $clave = substr($tipo, strlen('DEPOSITO_'));
        return ['Depósito de ' . ($nombres[$clave] ?? strtolower($clave)) . ' (app)', 'eco', true];
    }
    if ($tipo === 'ABONO_RECICLAJE') {
        return ['Abono por reciclaje', 'eco', true];
    }
    return [ucfirst(strtolower(str_replace('_', ' ', $tipo))), 'history', true];
}

include __DIR__ . "/../vistas/Transacciones.view.php";
