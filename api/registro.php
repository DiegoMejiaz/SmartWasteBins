<?php
// api/registro.php - Registro de usuario desde la app móvil
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../includes/cuentas.php";
exigirMetodo("POST");

$data = getRequestData();

$id_usuario = isset($data['id_usuario']) ? intval($data['id_usuario']) : 0;
$nombre     = trim($data['nombre'] ?? '');
$correo     = trim($data['correo'] ?? '');
$usuario    = trim($data['usuario'] ?? '');
$contrasena = (string)($data['contrasena'] ?? '');

try {
    $reclamada = registrarUsuario($conn, $id_usuario, $nombre, $correo, $usuario, $contrasena);
} catch (CuentaException $e) {
    responderError($e->getCode(), $e->getMessage());
}

responder($reclamada ? 200 : 201, [
    "success" => true,
    "message" => $reclamada
        ? "Tu cuenta ya existía; se le asignó la contraseña. Ya puedes iniciar sesión."
        : "Cuenta creada correctamente.",
    "usuario" => [
        "id_usuario" => $id_usuario,
        "nombre" => $nombre,
        "correo" => $correo,
        "usuario" => $usuario,
        "rol" => "user"
    ]
]);
