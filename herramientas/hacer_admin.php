<?php
// herramientas/hacer_admin.php - Da (o quita) el rol de administrador a una cuenta.
// Uso (consola, con las variables de la base configuradas igual que importar_bd.php):
//   php herramientas/hacer_admin.php <alias o documento>          -> la vuelve admin
//   php herramientas/hacer_admin.php <alias o documento> --quitar -> la vuelve usuario normal
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo se puede ejecutar desde la consola.\n");
}
require_once __DIR__ . '/../includes/conexion_bd.php';

$cuenta = $argv[1] ?? '';
if ($cuenta === '') {
    exit("Uso: php herramientas/hacer_admin.php <alias o documento> [--quitar]\n");
}
$rol = in_array('--quitar', $argv, true) ? 'user' : 'admin';
$conn = abrirConexionBD();

$campo = ctype_digit($cuenta) ? 'id_usuario' : 'usuario';
$stmt = mysqli_prepare($conn, "UPDATE usuario SET rol = ? WHERE $campo = ?");
mysqli_stmt_bind_param($stmt, ctype_digit($cuenta) ? "si" : "ss", $rol, $cuenta);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo "Listo: @$cuenta ahora tiene el rol '$rol'. Cierra sesión y vuelve a entrar.\n";
} else {
    echo "Sin cambios: no existe esa cuenta o ya tenía el rol '$rol'.\n";
}
