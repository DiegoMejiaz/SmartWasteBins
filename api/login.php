<?php
// api/login.php - Autenticación de usuario desde la app móvil (ID o alias + contraseña)
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../includes/cuentas.php";
exigirMetodo("POST");

$data = getRequestData();
$login_input = trim($data['usuario'] ?? '');
$contrasena  = (string)($data['contrasena'] ?? '');

try {
    $row = autenticarUsuario($conn, $login_input, $contrasena);
} catch (CuentaException $e) {
    responderError($e->getCode(), $e->getMessage());
}

responder(200, [
    "success" => true,
    "message" => "Autenticación exitosa.",
    "token" => crearToken($conn, intval($row['id_usuario'])),
    "expira_en_dias" => DIAS_VIGENCIA_TOKEN,
    "usuario" => usuarioPublico($row)
]);
