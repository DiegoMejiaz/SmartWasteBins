<?php
// AdminPanel.php - Panel de administración (usuarios y papeleras)
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../paginas/CuentaPuntos.php");
    exit();
}

$search = trim($_GET['search'] ?? '');

$total_users = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM usuario"))[0];
$total_points = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(saldo_actual) FROM cuenta_puntos"))[0] ?? 0);
$total_kg = (float) (mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(peso) FROM residuo"))[0] ?? 0);
$total_canjes = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT IFNULL(SUM(valor_cop), 0) FROM canje"))[0] ?? 0);

$sql_users = "SELECT u.id_usuario, u.nombre, u.correo, u.usuario, u.rol, u.fecha_registro, cp.saldo_actual
              FROM usuario u
              LEFT JOIN cuenta_puntos cp ON u.id_usuario = cp.id_usuario";
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, $sql_users . " WHERE u.nombre LIKE ? OR u.usuario LIKE ? OR u.correo LIKE ? OR u.id_usuario LIKE ? ORDER BY u.fecha_registro DESC");
    mysqli_stmt_bind_param($stmt, "ssss", $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $usuarios = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
} else {
    $usuarios = mysqli_fetch_all(mysqli_query($conn, $sql_users . " ORDER BY u.fecha_registro DESC"), MYSQLI_ASSOC);
}

$bins = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM smartbin ORDER BY id_bin ASC"), MYSQLI_ASSOC);

include __DIR__ . "/../vistas/AdminPanel.view.php";
