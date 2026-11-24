<?php
// CuentaPuntos.php - Panel principal del usuario
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
    "SELECT u.nombre, u.usuario, u.rol, IFNULL(cp.saldo_actual, 0) AS saldo_actual,
            (SELECT IFNULL(SUM(peso), 0) FROM residuo r WHERE r.id_usuario = u.id_usuario) AS total_peso
     FROM usuario u
     LEFT JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     WHERE u.id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user_data) {
    // La cuenta ya no existe (p. ej. fue eliminada): cerrar la sesión
    header("Location: cerrar_sesion.php");
    exit();
}

$nombre_completo = !empty($user_data['nombre']) ? $user_data['nombre'] : "Usuario";
$nombre_usuario = !empty($user_data['usuario']) ? $user_data['usuario'] : "Invitado";
$puntos = (int) $user_data['saldo_actual'];
$total_peso = (float) $user_data['total_peso'];
$rol_usuario = $user_data['rol'] ?? 'user';
$rango = rangoPorPuntos($puntos);
$siguiente = siguienteRango($puntos);

// Mantener la sesión al día con los datos actuales
$_SESSION['user_name'] = $nombre_completo;
$_SESSION['user_role'] = $rol_usuario;

include("CuentaPuntos.view.php");
