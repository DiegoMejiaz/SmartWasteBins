<?php
// procesar_edicion.php - Guarda los cambios de la cuenta del usuario
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.html");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../paginas/EditarCuenta.php");
    exit();
}

$id_usuario = intval($_SESSION['user_id']);
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');

if ($nombre === '' || $correo === '' || $usuario === '') {
    paginaAlerta('warning', 'Datos incompletos', 'Llena todos los campos.', '../paginas/EditarCuenta.php', 'Corregir');
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    paginaAlerta('warning', 'Correo inválido', 'Escribe un correo electrónico válido.', '../paginas/EditarCuenta.php', 'Corregir');
}

$stmt = mysqli_prepare($conn, "SELECT 1 FROM usuario WHERE (correo = ? OR usuario = ?) AND id_usuario != ?");
mysqli_stmt_bind_param($stmt, "ssi", $correo, $usuario, $id_usuario);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    paginaAlerta('error', 'Datos en uso', 'El correo o el nombre de usuario ya pertenecen a otra cuenta.', '../paginas/EditarCuenta.php', 'Corregir');
}

$stmt = mysqli_prepare($conn, "UPDATE usuario SET nombre = ?, correo = ?, usuario = ? WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "sssi", $nombre, $correo, $usuario, $id_usuario);
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['user_name'] = $nombre;
    paginaAlerta('success', '¡Cuenta actualizada!', 'Tus datos se guardaron correctamente.', '../paginas/CuentaPuntos.php');
}
paginaAlerta('error', 'No se pudo guardar', 'Ocurrió un error al actualizar tu cuenta. Intenta de nuevo.', '../paginas/EditarCuenta.php');
