<?php
// login.php - Inicio de sesión web (correo + nombre de usuario)
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";

if (!isset($_POST['correo'], $_POST['usuario'])) {
    header("Location: login.html");
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id_usuario, nombre, rol FROM usuario WHERE correo = ? AND usuario = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ss", $_POST['correo'], $_POST['usuario']);
mysqli_stmt_execute($stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($user_data) {
    session_regenerate_id(true); // evita fijación de sesión
    $_SESSION['user_id'] = $user_data['id_usuario'];
    $_SESSION['user_name'] = $user_data['nombre'];
    $_SESSION['user_role'] = $user_data['rol'];

    paginaAlerta(
        'success',
        '¡Bienvenido(a), ' . $user_data['nombre'] . '!',
        'Cargando tu panel…',
        'CuentaPuntos.php',
        'Continuar',
        1500
    );
}

paginaAlerta(
    'error',
    'Credenciales incorrectas',
    'El correo o el nombre de usuario no coinciden con nuestros registros.',
    'login.html',
    'Volver a intentar'
);
