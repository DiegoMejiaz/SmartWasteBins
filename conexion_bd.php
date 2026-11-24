<?php
// conexion_bd.php - Abre la conexión MySQL según el entorno.
//
// - Producción (Render): se configura con variables de entorno, nunca con datos en el código.
//     DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_NAME
//     DB_CA_CERT  (opcional) contenido PEM del certificado CA de Aiven, para verificar el servidor
// - Desarrollo (XAMPP): si DB_HOST no está definida, se usa root@localhost / bd_smartbin.

/**
 * Devuelve una conexión mysqli o lanza mysqli_sql_exception / RuntimeException.
 * $conBaseDatos = false conecta sin seleccionar base (lo usa el importador para crearla).
 */
function abrirConexionBD(bool $conBaseDatos = true): mysqli {
    $host = getenv('DB_HOST');

    if ($host === false || $host === '') {
        // ---- Desarrollo local (XAMPP)
        $conn = mysqli_connect("localhost", "root", "", $conBaseDatos ? "bd_smartbin" : null);
        if (!$conn) {
            throw new RuntimeException("No se pudo conectar a MySQL local: " . mysqli_connect_error());
        }
        mysqli_set_charset($conn, "utf8mb4");
        return $conn;
    }

    // ---- Producción (Aiven exige conexión cifrada con SSL)
    $puerto = (int) (getenv('DB_PORT') ?: 3306);
    $usuario = getenv('DB_USER') ?: '';
    $clave = getenv('DB_PASSWORD') ?: '';
    $base = $conBaseDatos ? (getenv('DB_NAME') ?: 'bd_smartbin') : null;

    $conn = mysqli_init();
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    $ca = getenv('DB_CA_CERT');
    $banderas = MYSQLI_CLIENT_SSL;
    if ($ca !== false && trim($ca) !== '') {
        // Verifica el certificado del servidor con la CA de Aiven
        $rutaCa = sys_get_temp_dir() . '/aiven-ca-' . md5($ca) . '.pem';
        if (!is_file($rutaCa)) {
            file_put_contents($rutaCa, str_replace('\n', "\n", trim($ca)) . "\n");
        }
        mysqli_ssl_set($conn, null, null, $rutaCa, null, null);
    } else {
        // Cifrado sin verificar la identidad del servidor (menos seguro; configura DB_CA_CERT)
        $banderas |= MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
    }

    if (!@mysqli_real_connect($conn, $host, $usuario, $clave, $base, $puerto, null, $banderas)) {
        throw new RuntimeException("No se pudo conectar a la base de datos remota: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}
