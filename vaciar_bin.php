<?php
session_start();
include("db.php");

// Verificar si el usuario ha iniciado sesión y si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

if (isset($_GET['id'])) {
    $id_bin = intval($_GET['id']);

    // Vaciar el bin: nivel_actual = 0 y estado = 'Activo'
    $query = "UPDATE smartbin SET nivel_actual = 0.0, estado = 'Activo' WHERE id_bin = $id_bin";

    if (mysqli_query($conn, $query)) {
        $referer = $_SERVER['HTTP_REFERER'] ?? 'AdminPanel.php';
        header("Location: $referer");
        exit();
    } else {
        echo "Error al vaciar la papelera: " . mysqli_error($conn);
    }
} else {
    header("Location: AdminPanel.php");
    exit();
}
?>
