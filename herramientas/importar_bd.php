<?php
// herramientas/importar_bd.php - Crea las tablas de Smart Waste Bin en la base MySQL de Aiven.
//
// Se ejecuta desde tu PC (no desde la web), con las mismas variables de entorno que Render:
//   $env:DB_HOST="..."; $env:DB_PORT="..."; $env:DB_USER="avnadmin"; $env:DB_PASSWORD="..."
//   $env:DB_NAME="bd_smartbin"; $env:DB_CA_CERT = Get-Content .\ca.pem -Raw
//   C:\xampp\php\php.exe herramientas\importar_bd.php
//
// Por seguridad NO toca una base que ya tenga tablas. Para reemplazarla (borra todos los datos):
//   C:\xampp\php\php.exe herramientas\importar_bd.php --forzar

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

require_once __DIR__ . '/../conexion_bd.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$forzar = in_array('--forzar', $argv, true);
$base = getenv('DB_NAME') ?: 'bd_smartbin';

if (!getenv('DB_HOST')) {
    fwrite(STDERR, "Falta DB_HOST. Define las variables de entorno de Aiven antes de ejecutar (ver encabezado).\n");
    exit(1);
}
if (!preg_match('/^[A-Za-z0-9_]+$/', $base)) {
    fwrite(STDERR, "DB_NAME solo puede tener letras, números y guion bajo.\n");
    exit(1);
}

echo "Conectando a " . getenv('DB_HOST') . ":" . (getenv('DB_PORT') ?: 3306) . " ...\n";
try {
    $conn = abrirConexionBD(false);
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Revisa DB_HOST, DB_PORT, DB_USER y DB_PASSWORD (Aiven > tu servicio > Overview > Connection information).\n");
    exit(1);
}
$cifrado = mysqli_fetch_row(mysqli_query($conn, "SHOW SESSION STATUS LIKE 'Ssl_cipher'"))[1] ?? '';
echo "Conectado. Cifrado SSL: " . ($cifrado !== '' ? $cifrado : 'NO (revisa la configuración)') . "\n";

// ¿La base ya tiene tablas?
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?");
mysqli_stmt_bind_param($stmt, "s", $base);
mysqli_stmt_execute($stmt);
$tablas = (int) mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];
mysqli_stmt_close($stmt);

if ($tablas > 0 && !$forzar) {
    echo "La base '$base' ya tiene $tablas tablas. No se modificó nada.\n";
    echo "Si de verdad quieres reemplazarla (se BORRAN todos los datos), ejecuta con --forzar.\n";
    exit(0);
}

$sql = file_get_contents(__DIR__ . '/../bd_smartbin.sql');
if ($sql === false) {
    fwrite(STDERR, "No se encontró bd_smartbin.sql.\n");
    exit(1);
}
// El script crea `bd_smartbin`; se adapta al nombre configurado en DB_NAME
$sql = str_replace('`bd_smartbin`', '`' . $base . '`', $sql);

echo "Importando esquema y papeleras en '$base' ...\n";
mysqli_multi_query($conn, $sql);
do {
    if ($res = mysqli_store_result($conn)) {
        mysqli_free_result($res);
    }
} while (mysqli_more_results($conn) && mysqli_next_result($conn));

mysqli_select_db($conn, $base);
$res = mysqli_query($conn, "SHOW TABLES");
$lista = array_map(fn($f) => $f[0], mysqli_fetch_all($res));
$bins = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM smartbin"))[0];

echo "Listo. Tablas: " . implode(', ', $lista) . "\n";
echo "Papeleras cargadas: $bins\n";
