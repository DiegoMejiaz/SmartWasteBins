<?php
// api/canjear.php - Pasarela de canje de puntos desde la app móvil (requiere sesión)
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../logica_puntos.php";
exigirMetodo("POST");

$id_usuario = exigirUsuario($conn);
$data = getRequestData();

try {
    $comprobante = canjearPuntos(
        $conn,
        $id_usuario,
        (string)($data['metodo'] ?? ''),
        (string)($data['telefono'] ?? ''),
        intval($data['puntos'] ?? 0)
    );
} catch (CanjeException $e) {
    responderError($e->codigoHttp, $e->getMessage());
}

responder(200, [
    "success" => true,
    "message" => "¡Canje aprobado!",
    "comprobante" => $comprobante
]);
