<?php
// eliminar_cuenta.php - El usuario elimina su propia cuenta
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$id_usuario = intval($_SESSION['user_id']);
$stmt = mysqli_prepare($conn, "DELETE FROM usuario WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_usuario);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION = [];
    session_destroy();
    paginaAlerta('success', 'Cuenta eliminada', 'Tu cuenta se eliminó permanentemente. Esperamos verte pronto.', 'insertar_usuario.html', 'Entendido');
}
paginaAlerta('error', 'No se pudo eliminar', 'Ocurrió un error al eliminar la cuenta. Intenta de nuevo.', 'CuentaPuntos.php');
