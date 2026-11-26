<?php
// EditarCuenta.php - Formulario para editar los datos de la cuenta
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.html");
    exit();
}

$id_usuario = intval($_SESSION['user_id']);
$stmt = mysqli_prepare($conn, "SELECT nombre, correo, usuario FROM usuario WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: ['nombre' => '', 'correo' => '', 'usuario' => ''];

include __DIR__ . "/../vistas/EditarCuenta.view.php";
