<?php
// api/admin.php - Panel de administración para la app (solo usuarios con rol 'admin').
//   GET                                  -> resumen, usuarios (con correo) y papeleras
//   POST {"accion":"vaciar","id_bin":N}  -> vacía la papelera y la deja Activa
require_once __DIR__ . "/config.php";

$id_admin = exigirUsuario($conn);
$yo = obtenerFila($conn, "SELECT rol FROM usuario WHERE id_usuario = ?", "i", $id_admin);
if (($yo['rol'] ?? '') !== 'admin') {
    responderError(403, "Solo los administradores pueden entrar aquí.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = getRequestData();
    $id_bin = intval($data['id_bin'] ?? 0);
    if (($data['accion'] ?? '') !== 'vaciar' || $id_bin <= 0) {
        responderError(400, "Acción no válida.");
    }
    $bin = obtenerFila($conn, "SELECT ubicacion FROM smartbin WHERE id_bin = ?", "i", $id_bin);
    if (!$bin) {
        responderError(404, "La papelera no existe.");
    }
    ejecutar($conn, "UPDATE smartbin SET nivel_actual = 0, estado = 'Activo' WHERE id_bin = ?", "i", $id_bin);
    responder(200, ["success" => true, "message" => "Papelera \"" . $bin['ubicacion'] . "\" vaciada."]);
}

$usuarios = array_map(fn($u) => [
    "id_usuario" => (int) $u['id_usuario'],
    "nombre" => $u['nombre'],
    "correo" => $u['correo'],
    "usuario" => $u['usuario'],
    "rol" => $u['rol'],
    "saldo" => (int) $u['saldo'],
    "fecha_registro" => $u['fecha_registro'],
], obtenerFilas($conn,
    "SELECT u.id_usuario, u.nombre, u.correo, u.usuario, u.rol, u.fecha_registro, IFNULL(cp.saldo_actual, 0) AS saldo
     FROM usuario u LEFT JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     ORDER BY u.fecha_registro DESC"));

$papeleras = array_map(function ($b) {
    $cap = (float) $b['capacidad_maxima'];
    $nivel = (float) $b['nivel_actual'];
    return [
        "id_bin" => (int) $b['id_bin'],
        "ubicacion" => $b['ubicacion'],
        "capacidad_maxima" => $cap,
        "nivel_actual" => $nivel,
        "porcentaje_llenado" => $cap > 0 ? round($nivel / $cap * 100, 1) : 0,
        "estado" => $b['estado'],
    ];
}, obtenerFilas($conn, "SELECT id_bin, ubicacion, capacidad_maxima, nivel_actual, estado FROM smartbin ORDER BY id_bin"));

$tot = obtenerFila($conn,
    "SELECT (SELECT COUNT(*) FROM usuario) AS usuarios,
            (SELECT IFNULL(SUM(peso), 0) FROM residuo) AS kg,
            (SELECT IFNULL(SUM(valor_cop), 0) FROM canje) AS canjes_cop");

responder(200, [
    "success" => true,
    "resumen" => [
        "usuarios" => (int) $tot['usuarios'],
        "kg_reciclados" => round((float) $tot['kg'], 2),
        "canjes_cop" => (int) $tot['canjes_cop'],
    ],
    "usuarios" => $usuarios,
    "papeleras" => $papeleras,
]);
