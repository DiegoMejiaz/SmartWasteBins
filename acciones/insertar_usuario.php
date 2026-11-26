<?php
// insertar_usuario.php - Registro de usuario desde la web (mismas reglas que la app: includes/cuentas.php)
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";
require_once __DIR__ . "/../includes/cuentas.php";

function errorRegistro(string $mensaje): void {
    paginaAlerta('error', 'Revisa tus datos', e($mensaje), '../paginas/insertar_usuario.html', 'Corregir datos');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['id_usuario'], $_POST['nombre_completo'], $_POST['correo'], $_POST['usuario'], $_POST['contrasena'])) {
    errorRegistro("Datos incompletos. Por favor, llena todos los campos.");
}

$documento = trim((string) $_POST['id_usuario']);
$nombre = trim((string) $_POST['nombre_completo']);
$contrasena = (string) $_POST['contrasena'];

if (!ctype_digit($documento) || strlen($documento) > 10) {
    errorRegistro("El documento de identidad solo debe contener números.");
}
if ($contrasena !== (string) ($_POST['confirmar'] ?? '')) {
    errorRegistro("Las contraseñas no coinciden.");
}

try {
    $reclamada = registrarUsuario($conn, intval($documento), $nombre, (string) $_POST['correo'], (string) $_POST['usuario'], $contrasena);
} catch (CuentaException $e) {
    errorRegistro($e->getMessage());
}

paginaAlerta(
    'success',
    $reclamada ? '¡Contraseña asignada!' : '¡Registro exitoso!',
    $reclamada
        ? 'Tu cuenta ya existía; ahora tiene contraseña. Inicia sesión con tu documento o tu alias.'
        : 'Bienvenido(a) <strong>' . e($nombre) . '</strong>.<br>Inicia sesión con tu documento o con tu alias y tu contraseña.',
    '../paginas/login.html',
    'Iniciar sesión'
);
