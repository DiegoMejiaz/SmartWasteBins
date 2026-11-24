<?php
// RetirarPuntos.php - Pasarela de canje de puntos (paso a paso)
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";
require_once __DIR__ . "/logica_puntos.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$id_usuario = intval($_SESSION['user_id']);
$stmt = mysqli_prepare($conn,
    "SELECT u.correo, u.usuario, IFNULL(cp.saldo_actual, 0) AS saldo_actual
     FROM usuario u LEFT JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     WHERE u.id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$puntos_actuales = (int) ($user_data['saldo_actual'] ?? 0);
$correo_actual = $user_data['correo'] ?? '';
$usuario_actual = $user_data['usuario'] ?? '';

// Mensaje de error del último intento (se muestra una sola vez)
$error_canje = $_SESSION['error_canje'] ?? null;
unset($_SESSION['error_canje']);

include("RetirarPuntos.view.php");
