<?php
// api/clasificacion.php - Top 10 de recicladores (misma consulta que Clasificacion.php) y la posición propia
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../includes/logica_puntos.php";

$id_usuario = exigirUsuario($conn);

$filas = obtenerFilas(
    $conn,
    "SELECT u.id_usuario, u.nombre, u.usuario, " . sqlPuntosAcumulados() . " AS saldo_actual
     FROM usuario u
     JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     ORDER BY saldo_actual DESC, u.id_usuario ASC
     LIMIT 10"
);

$top = [];
foreach ($filas as $i => $f) {
    $puntos = (int) $f['saldo_actual'];
    $top[] = [
        "posicion" => $i + 1,
        "nombre" => $f['nombre'],
        "usuario" => $f['usuario'],
        "puntos" => $puntos,
        "rango" => rangoPorPuntos($puntos),
        "soy_yo" => (int) $f['id_usuario'] === $id_usuario,
    ];
}

// Posición del usuario autenticado (aunque no esté en el top 10), por puntos acumulados
$acumulados = "SELECT cp.id_usuario, " . sqlPuntosAcumulados() . " AS pts FROM cuenta_puntos cp";
$mio = obtenerFila($conn, "SELECT pts FROM ($acumulados) a WHERE id_usuario = ?", "i", $id_usuario);
$mis_puntos = (int) ($mio['pts'] ?? 0);
$delante = obtenerFila(
    $conn,
    "SELECT COUNT(*) AS n FROM ($acumulados) a WHERE pts > ? OR (pts = ? AND id_usuario < ?)",
    "iii", $mis_puntos, $mis_puntos, $id_usuario
);
$total = obtenerFila($conn, "SELECT COUNT(*) AS n FROM cuenta_puntos");

responder(200, [
    "success" => true,
    "top" => $top,
    "mi_posicion" => (int) $delante['n'] + 1,
    "mis_puntos" => $mis_puntos,
    "total_usuarios" => (int) $total['n'],
]);
