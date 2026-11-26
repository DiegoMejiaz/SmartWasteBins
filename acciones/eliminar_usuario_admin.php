<?php
// eliminar_usuario_admin.php - Un administrador elimina a otro usuario
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../paginas/CuentaPuntos.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: ../paginas/AdminPanel.php");
    exit();
}

$id_a_eliminar = intval($_GET['id']);
if ($id_a_eliminar === intval($_SESSION['user_id'])) {
    paginaAlerta('warning', 'Acción no permitida', 'No puedes eliminar tu propia cuenta desde el panel de administración.', '../paginas/AdminPanel.php');
}

$stmt = mysqli_prepare($conn, "DELETE FROM usuario WHERE id_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $id_a_eliminar);
if (mysqli_stmt_execute($stmt)) {
    paginaAlerta('success', 'Usuario eliminado', 'El usuario se eliminó correctamente.', '../paginas/AdminPanel.php');
}
paginaAlerta('error', 'No se pudo eliminar', 'Ocurrió un error al eliminar el usuario.', '../paginas/AdminPanel.php');
