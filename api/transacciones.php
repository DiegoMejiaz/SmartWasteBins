<?php
// api/transacciones.php - Historial de transacciones de puntos del usuario
require_once __DIR__ . "/config.php";

// Requiere sesión: solo se puede consultar el historial propio
$id_usuario = exigirUsuario($conn);

$filas = obtenerFilas(
    $conn,
    "SELECT tp.id_transaccion, tp.tipo, tp.cantidad, tp.fecha
     FROM transaccion_puntos tp
     INNER JOIN cuenta_puntos cp ON tp.id_cuenta = cp.id_cuenta
     WHERE cp.id_usuario = ?
     ORDER BY tp.fecha DESC
     LIMIT 20",
    "i", $id_usuario
);

$transacciones = array_map(fn($row) => [
    "id_transaccion" => intval($row['id_transaccion']),
    "tipo" => $row['tipo'],
    "cantidad" => intval($row['cantidad']),
    "fecha" => $row['fecha']
], $filas);

responder(200, [
    "success" => true,
    "total" => count($transacciones),
    "transacciones" => $transacciones
]);
