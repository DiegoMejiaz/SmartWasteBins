<?php
// Clasificacion.php - Top 10 de recicladores
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";
require_once __DIR__ . "/logica_puntos.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Se mantiene por compatibilidad: ahora usa las reglas compartidas de logica_puntos.php
function obtenerRango($puntos) {
    return rangoPorPuntos((int) $puntos);
}

$res_top = mysqli_query($conn,
    "SELECT u.id_usuario, u.nombre, u.usuario, cp.saldo_actual
     FROM usuario u
     JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     ORDER BY cp.saldo_actual DESC
     LIMIT 10");
$top = mysqli_fetch_all($res_top, MYSQLI_ASSOC);

include("Clasificacion.view.php");
