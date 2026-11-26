<?php
// login.php - Inicio de sesión web (documento o alias + contraseña, igual que la app móvil)
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";
require_once __DIR__ . "/../includes/cuentas.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['usuario'], $_POST['contrasena'])) {
    header("Location: ../paginas/login.html");
    exit();
}

try {
    $user_data = autenticarUsuario($conn, (string) $_POST['usuario'], (string) $_POST['contrasena']);
} catch (CuentaException $e) {
    $titulo = $e->getCode() === 403 ? 'Tu cuenta no tiene contraseña' : 'No pudimos iniciar sesión';
    paginaAlerta('error', $titulo, e($e->getMessage()), '../paginas/login.html', 'Volver a intentar');
}

session_regenerate_id(true); // evita fijación de sesión
$_SESSION['user_id'] = $user_data['id_usuario'];
$_SESSION['user_name'] = $user_data['nombre'];
$_SESSION['user_role'] = $user_data['rol'];

paginaAlerta(
    'success',
    '¡Bienvenido(a), ' . $user_data['nombre'] . '!',
    'Cargando tu panel…',
    '../paginas/CuentaPuntos.php',
    'Continuar',
    1500
);
