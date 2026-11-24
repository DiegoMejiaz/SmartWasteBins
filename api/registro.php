<?php
// api/registro.php - Registro de usuario desde la app móvil
require_once __DIR__ . "/config.php";
exigirMetodo("POST");

$data = getRequestData();

$id_usuario = isset($data['id_usuario']) ? intval($data['id_usuario']) : 0;
$nombre     = trim($data['nombre'] ?? '');
$correo     = trim($data['correo'] ?? '');
$usuario    = trim($data['usuario'] ?? '');
$contrasena = (string)($data['contrasena'] ?? '');

// Validaciones (la app valida lo mismo, pero el servidor nunca confía en el cliente)
if ($id_usuario <= 0 || $nombre === '' || $correo === '' || $usuario === '' || $contrasena === '') {
    responderError(400, "Todos los campos son obligatorios.");
}
if (mb_strlen($nombre) > 100) {
    responderError(400, "El nombre no puede superar 100 caracteres.");
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 100) {
    responderError(400, "El correo electrónico no es válido.");
}
if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $usuario)) {
    responderError(400, "El alias debe tener entre 3 y 30 caracteres (letras, números, punto o guion bajo).");
}
if (ctype_digit($usuario)) {
    responderError(400, "El alias no puede ser solo números.");
}
if (strlen($contrasena) < 6 || strlen($contrasena) > 72) {
    responderError(400, "La contraseña debe tener entre 6 y 72 caracteres.");
}

// 1. Verificar existencia previa (ID, correo o alias)
$existente = obtenerFila(
    $conn,
    "SELECT id_usuario, correo, usuario, contrasena FROM usuario WHERE id_usuario = ? OR correo = ? OR usuario = ? LIMIT 1",
    "iss", $id_usuario, $correo, $usuario
);

// Cuenta antigua sin contraseña (creada desde la web): si documento, correo y alias coinciden,
// se le asigna la contraseña. Es la misma verificación que usa el login web (correo + alias).
if ($existente && empty($existente['contrasena'])
    && intval($existente['id_usuario']) === $id_usuario
    && strcasecmp($existente['correo'] ?? '', $correo) === 0
    && strcasecmp($existente['usuario'] ?? '', $usuario) === 0) {
    mysqli_begin_transaction($conn);
    try {
        ejecutar($conn, "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?", "si",
            password_hash($contrasena, PASSWORD_DEFAULT), $id_usuario);
        if (!obtenerFila($conn, "SELECT id_cuenta FROM cuenta_puntos WHERE id_usuario = ?", "i", $id_usuario)) {
            ejecutar($conn, "INSERT INTO cuenta_puntos (id_usuario, saldo_actual, fecha_actualizacion) VALUES (?, 0, NOW())", "i", $id_usuario);
        }
        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;
    }
    responder(200, [
        "success" => true,
        "message" => "Tu cuenta ya existía; se le asignó la contraseña. Ya puedes iniciar sesión.",
        "usuario" => ["id_usuario" => $id_usuario, "nombre" => $nombre, "correo" => $correo, "usuario" => $usuario, "rol" => "user"]
    ]);
}

if ($existente) {
    if (intval($existente['id_usuario']) === $id_usuario) {
        responderError(409, "Ya existe una cuenta con ese documento de identidad.");
    }
    if (strcasecmp($existente['correo'] ?? '', $correo) === 0) {
        responderError(409, "Ese correo electrónico ya está registrado.");
    }
    responderError(409, "Ese alias ya está en uso. Elige otro.");
}

// 2. Usuario + cuenta de puntos en una sola transacción (todo o nada)
$hash = password_hash($contrasena, PASSWORD_DEFAULT);
mysqli_begin_transaction($conn);
try {
    ejecutar(
        $conn,
        "INSERT INTO usuario (id_usuario, nombre, correo, usuario, contrasena, rol) VALUES (?, ?, ?, ?, ?, 'user')",
        "issss", $id_usuario, $nombre, $correo, $usuario, $hash
    );
    ejecutar(
        $conn,
        "INSERT INTO cuenta_puntos (id_usuario, saldo_actual, fecha_actualizacion) VALUES (?, 0, NOW())",
        "i", $id_usuario
    );
    mysqli_commit($conn);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    throw $e;
}

responder(201, [
    "success" => true,
    "message" => "Cuenta creada correctamente.",
    "usuario" => [
        "id_usuario" => $id_usuario,
        "nombre" => $nombre,
        "correo" => $correo,
        "usuario" => $usuario,
        "rol" => "user"
    ]
]);
