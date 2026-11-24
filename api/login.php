<?php
// api/login.php - Autenticación de usuario desde la app móvil (ID o alias + contraseña)
require_once __DIR__ . "/config.php";
exigirMetodo("POST");

$data = getRequestData();
$login_input = trim($data['usuario'] ?? '');
$contrasena  = (string)($data['contrasena'] ?? '');

if ($login_input === '' || $contrasena === '') {
    responderError(400, "Ingresa tu ID o alias y tu contraseña.");
}

// Buscar por documento (si es numérico) o por alias
$sql = "SELECT id_usuario, nombre, correo, usuario, contrasena, rol FROM usuario WHERE ";
$row = ctype_digit($login_input)
    ? obtenerFila($conn, $sql . "id_usuario = ? LIMIT 1", "i", intval($login_input))
    : obtenerFila($conn, $sql . "usuario = ? LIMIT 1", "s", $login_input);

// Mismo mensaje si no existe o si la contraseña es incorrecta (no revela qué cuentas existen)
if (!$row || empty($row['contrasena']) || !password_verify($contrasena, $row['contrasena'])) {
    if ($row && empty($row['contrasena'])) {
        responderError(403, "Tu cuenta aún no tiene contraseña. Ve a \"Crear cuenta\" e ingresa tu mismo documento, correo y alias para asignarle una.");
    }
    responderError(401, "Usuario o contraseña incorrectos.");
}

// Actualizar el hash si PHP recomienda un algoritmo más reciente
if (password_needs_rehash($row['contrasena'], PASSWORD_DEFAULT)) {
    ejecutar($conn, "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?", "si",
        password_hash($contrasena, PASSWORD_DEFAULT), intval($row['id_usuario']));
}

responder(200, [
    "success" => true,
    "message" => "Autenticación exitosa.",
    "token" => crearToken($conn, intval($row['id_usuario'])),
    "expira_en_dias" => DIAS_VIGENCIA_TOKEN,
    "usuario" => usuarioPublico($row)
]);
