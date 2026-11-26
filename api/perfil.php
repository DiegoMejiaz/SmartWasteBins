<?php
// api/perfil.php - Datos de perfil, saldo de puntos, rango y total kg
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../includes/logica_puntos.php";

// Requiere sesión: solo se puede consultar el perfil propio
$id_usuario = exigirUsuario($conn);

// 1. Datos de usuario y puntos
$user = obtenerFila(
    $conn,
    "SELECT u.id_usuario, u.nombre, u.correo, u.usuario, u.rol, IFNULL(cp.saldo_actual, 0) AS saldo_actual,
            " . sqlPuntosAcumulados() . " AS acumulados
     FROM usuario u
     LEFT JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario
     WHERE u.id_usuario = ? LIMIT 1",
    "i", $id_usuario
);
if (!$user) {
    responderError(404, "Usuario no encontrado.");
}

// 2. Total de kg reciclados
$peso_row = obtenerFila($conn, "SELECT IFNULL(SUM(peso), 0) AS total_peso FROM residuo WHERE id_usuario = ?", "i", $id_usuario);
$total_peso = floatval($peso_row['total_peso']);
$puntos = intval($user['saldo_actual']);
$acumulados = intval($user['acumulados']); // el rango no baja al canjear

// 3. Rango ecológico (mismas reglas que la web, ver logica_puntos.php)
responder(200, [
    "success" => true,
    "perfil" => usuarioPublico($user) + [
        "saldo_actual" => $puntos,
        "total_peso_kg" => round($total_peso, 2),
        "puntos_acumulados" => $acumulados,
        "rango" => rangoPorPuntos($acumulados),
        "siguiente_rango" => siguienteRango($acumulados),
        "canje" => [
            "cop_por_punto" => CANJE_COP_POR_PUNTO,
            "minimo_puntos" => CANJE_MINIMO_PUNTOS
        ]
    ]
]);
