<?php
// api/config.php - Cabeceras, conexión común y utilidades compartidas de la API móvil
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// La API siempre responde JSON: nunca imprimir warnings/HTML al cliente
ini_set('display_errors', '0');
error_reporting(E_ALL);

/**
 * Envía una respuesta JSON con el código HTTP indicado y termina la ejecución.
 */
function responder(int $codigo, array $datos): void {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit();
}

function responderError(int $codigo, string $mensaje): void {
    responder($codigo, ["success" => false, "message" => $mensaje]);
}

// Cualquier excepción no controlada (BD caída, SQL inválido...) se devuelve como JSON 500
set_exception_handler(function (Throwable $e) {
    error_log("[API SmartWasteBin] " . $e->getMessage());
    responderError(500, "Error interno del servidor. Intenta de nuevo más tarde.");
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    responder(200, ["success" => true]);
}

// mysqli lanza excepciones en vez de devolver false (capturadas por el handler anterior)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
define('API_SMARTBIN', true); // db.php lanza la excepción en vez de imprimir texto
require_once __DIR__ . "/../db.php";

/**
 * Lee parámetros enviados como JSON o como formulario.
 */
function getRequestData(): array {
    $rawInput = file_get_contents("php://input");
    $jsonData = json_decode($rawInput, true);
    if (is_array($jsonData)) {
        return array_merge($_REQUEST, $jsonData);
    }
    return $_REQUEST;
}

function exigirMetodo(string $metodo): void {
    if ($_SERVER["REQUEST_METHOD"] !== $metodo) {
        responderError(405, "Método no permitido. Se requiere $metodo.");
    }
}

/**
 * Ejecuta una consulta preparada. $tipos usa la notación de mysqli ("i", "s", "d").
 * Devuelve el mysqli_stmt ya ejecutado.
 */
function ejecutar(mysqli $conn, string $sql, string $tipos = "", ...$params): mysqli_stmt {
    $stmt = mysqli_prepare($conn, $sql);
    if ($tipos !== "") {
        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return $stmt;
}

/** Devuelve la primera fila (array asociativo) o null. */
function obtenerFila(mysqli $conn, string $sql, string $tipos = "", ...$params): ?array {
    $res = mysqli_stmt_get_result(ejecutar($conn, $sql, $tipos, ...$params));
    $fila = mysqli_fetch_assoc($res);
    return $fila ?: null;
}

/** Devuelve todas las filas como arrays asociativos. */
function obtenerFilas(mysqli $conn, string $sql, string $tipos = "", ...$params): array {
    $res = mysqli_stmt_get_result(ejecutar($conn, $sql, $tipos, ...$params));
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

// ---------------------------------------------------------------- Autenticación por token

const DIAS_VIGENCIA_TOKEN = 30;

/** Crea un token de sesión para el usuario y devuelve el token en texto plano (solo se guarda su hash). */
function crearToken(mysqli $conn, int $id_usuario): string {
    // Limpieza de sesiones vencidas
    ejecutar($conn, "DELETE FROM sesion_token WHERE expira < NOW()");
    $token = bin2hex(random_bytes(32));
    ejecutar(
        $conn,
        "INSERT INTO sesion_token (token_hash, id_usuario, creado, expira) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))",
        "sii", hash('sha256', $token), $id_usuario, DIAS_VIGENCIA_TOKEN
    );
    return $token;
}

/** Lee el token de la cabecera "Authorization: Bearer <token>" (o "X-Auth-Token" como respaldo). */
function tokenDeLaPeticion(): string {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($auth === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $nombre => $valor) {
            if (strcasecmp($nombre, 'Authorization') === 0) $auth = $valor;
        }
    }
    if (preg_match('/^Bearer\s+([a-f0-9]{64})$/i', trim($auth), $m)) {
        return strtolower($m[1]);
    }
    $alterno = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? '';
    return preg_match('/^[a-f0-9]{64}$/i', $alterno) ? strtolower($alterno) : '';
}

/**
 * Exige una sesión válida y devuelve el id del usuario autenticado.
 * Responde 401 si falta el token o si expiró. El id_usuario enviado por el cliente
 * nunca se usa para identificar a quién se le aplican los cambios.
 */
function exigirUsuario(mysqli $conn): int {
    $token = tokenDeLaPeticion();
    if ($token === '') {
        responderError(401, "Debes iniciar sesión.");
    }
    $fila = obtenerFila(
        $conn,
        "SELECT id_usuario FROM sesion_token WHERE token_hash = ? AND expira > NOW() LIMIT 1",
        "s", hash('sha256', $token)
    );
    if (!$fila) {
        responderError(401, "Tu sesión expiró. Inicia sesión de nuevo.");
    }
    return intval($fila['id_usuario']);
}

function usuarioPublico(array $row): array {
    return [
        "id_usuario" => intval($row['id_usuario']),
        "nombre" => $row['nombre'],
        "correo" => $row['correo'],
        "usuario" => $row['usuario'],
        "rol" => $row['rol']
    ];
}
