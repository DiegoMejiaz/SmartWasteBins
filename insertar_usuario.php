<?php
// insertar_usuario.php - Registro de usuario desde la web
include("db.php");
require_once __DIR__ . "/ui_web.php";

function responderError(string $mensaje): void {
    paginaAlerta('error', 'Revisa tus datos', e($mensaje), 'insertar_usuario.html', 'Corregir datos');
}

if (!isset($_POST['id_usuario'], $_POST['nombre_completo'], $_POST['correo'], $_POST['usuario'])) {
    responderError("Datos incompletos. Por favor, llena todos los campos.");
}

$id_usuario = trim($_POST['id_usuario']);
$nombre_completo = trim($_POST['nombre_completo']);
$correo = trim($_POST['correo']);
$usuario_nick = trim($_POST['usuario']);

// Validaciones
if (!ctype_digit($id_usuario)) {
    responderError("El ID debe contener únicamente números.");
}
if (strlen($id_usuario) !== 5) {
    responderError("El ID institucional debe tener exactamente 5 dígitos.");
}
if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u", $nombre_completo)) {
    responderError("El nombre completo solo debe contener letras y espacios.");
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    responderError("El correo electrónico no es válido.");
}
if (!preg_match("/^[a-zA-Z0-9]+$/", $usuario_nick)) {
    responderError("El nombre de usuario solo debe contener letras y números (sin espacios).");
}

$id = intval($id_usuario);

/** Devuelve true si la consulta (con un parámetro) encuentra alguna fila. */
function existe(mysqli $conn, string $sql, string $tipo, $valor): bool {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $tipo, $valor);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// Comprobaciones de unicidad
if (existe($conn, "SELECT 1 FROM usuario WHERE id_usuario = ?", "i", $id)) {
    responderError("El ID $id_usuario ya se encuentra registrado. Usa un identificador diferente.");
}
if (existe($conn, "SELECT 1 FROM usuario WHERE correo = ?", "s", $correo)) {
    responderError("El correo electrónico ya está asociado a otra cuenta activa.");
}
if (existe($conn, "SELECT 1 FROM usuario WHERE usuario = ?", "s", $usuario_nick)) {
    responderError("El nombre de usuario '$usuario_nick' ya está en uso. Por favor, elige otro.");
}

// Usuario + monedero de puntos en una sola transacción
mysqli_begin_transaction($conn);
$qr = 'SWB_USER_' . $id_usuario;
$stmt = mysqli_prepare($conn, "INSERT INTO usuario (id_usuario, nombre, correo, usuario, qr_code, fecha_registro) VALUES (?, ?, ?, ?, ?, NOW())");
mysqli_stmt_bind_param($stmt, "issss", $id, $nombre_completo, $correo, $usuario_nick, $qr);
$ok = mysqli_stmt_execute($stmt);
if ($ok) {
    $stmt = mysqli_prepare($conn, "INSERT INTO cuenta_puntos (id_usuario, saldo_actual, fecha_actualizacion) VALUES (?, 0, NOW())");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
}
if (!$ok) {
    mysqli_rollback($conn);
    responderError("No fue posible crear la cuenta. Intenta de nuevo.");
}
mysqli_commit($conn);

paginaAlerta(
    'success',
    '¡Registro exitoso!',
    'Bienvenido(a) <strong>' . e($nombre_completo) . '</strong>.<br>Tu carné digital QR y tu monedero de eco-puntos ya están activos.',
    'login.html',
    'Iniciar sesión'
);
