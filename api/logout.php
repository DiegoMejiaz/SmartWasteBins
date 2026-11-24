<?php
// api/logout.php - Cierra la sesión de la app móvil invalidando el token
require_once __DIR__ . "/config.php";
exigirMetodo("POST");

$token = tokenDeLaPeticion();
if ($token !== '') {
    ejecutar($conn, "DELETE FROM sesion_token WHERE token_hash = ?", "s", hash('sha256', $token));
}

responder(200, ["success" => true, "message" => "Sesión cerrada."]);
