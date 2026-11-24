<?php
// eliminar_usuario_admin.php - Un administrador elimina a otro usuario
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: CuentaPuntos.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: AdminPanel.php");
    exit();
}

$id_a_eliminar = intval($_GET['id']);
if ($id_a_eliminar === intval($_SESSION['user_id'])) {
    paginaAlerta('warning', 'Acción no permitida', 'No puedes eliminar tu propia cuenta desde el panel de administración.', 'AdminPanel.php');
}

$stmt = mysqli_prepare($conn, "DELETE FROM usuario WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_a_eliminar);
if (mysqli_stmt_execute($stmt)) {
    paginaAlerta('success', 'Usuario eliminado', 'El usuario se eliminó correctamente.', 'AdminPanel.php');
}
paginaAlerta('error', 'No se pudo eliminar', 'Ocurrió un error al eliminar el usuario.', 'AdminPanel.php');
