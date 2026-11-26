<?php
// Clasificacion.php - Top 10 de recicladores
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";
require_once __DIR__ . "/../includes/logica_puntos.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.html");
    exit();
}

// Se mantiene por compatibilidad: ahora usa las reglas compartidas de logica_puntos.php
function obtenerRango($puntos) {
    return rangoPorPuntos((int) $puntos);
}

$res_top = mysqli_query($conn,
    "SELECT u.id_usuario, u.nombre, u.usuario, " . sqlPuntosAcumulados() . " AS saldo_actual
     FROM usuario u
     JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     ORDER BY saldo_actual DESC, u.id_usuario ASC
     LIMIT 10");
$top = mysqli_fetch_all($res_top, MYSQLI_ASSOC);

include __DIR__ . "/../vistas/Clasificacion.view.php";
