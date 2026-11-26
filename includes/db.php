<?php
// db.php - Conexión compartida por la web y la API.
// La configuración vive en conexion_bd.php (variables de entorno en Render, XAMPP en local).

// Hora de Colombia para fechas generadas en PHP (referencias de canje, etc.)
date_default_timezone_set('America/Bogota');

require_once __DIR__ . "/conexion_bd.php";

try {
    $conn = abrirConexionBD();
} catch (Throwable $e) {
    error_log("[SmartWasteBin] " . $e->getMessage());
    // En la API, el manejador de excepciones de api/config.php lo convierte en JSON 500
    if (defined('API_SMARTBIN')) {
        throw $e;
    }
    http_response_code(503);
    die("El servicio no está disponible en este momento. Intenta de nuevo en unos minutos.");
}

// Sincroniza también la zona horaria de MySQL (NOW() en hora de Colombia)
mysqli_query($conn, "SET time_zone = '-05:00'");
