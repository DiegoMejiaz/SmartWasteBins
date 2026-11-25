<?php
// api/clasificacion.php - Top 10 de recicladores (misma consulta que Clasificacion.php) y la posición propia
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../logica_puntos.php";

$id_usuario = exigirUsuario($conn);

$filas = obtenerFilas(
    $conn,
    "SELECT u.id_usuario, u.nombre, u.usuario, cp.saldo_actual
     FROM usuario u
     JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     ORDER BY cp.saldo_actual DESC, u.id_usuario ASC
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

// Posición del usuario autenticado (aunque no esté en el top 10)
$mio = obtenerFila($conn, "SELECT IFNULL(saldo_actual, 0) AS saldo FROM cuenta_puntos WHERE id_usuario = ?", "i", $id_usuario);
$mis_puntos = (int) ($mio['saldo'] ?? 0);
$delante = obtenerFila(
    $conn,
    "SELECT COUNT(*) AS n FROM cuenta_puntos WHERE saldo_actual > ? OR (saldo_actual = ? AND id_usuario < ?)",
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
