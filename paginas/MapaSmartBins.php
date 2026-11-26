<?php
// MapaSmartBins.php - Mapa y estado de las papeleras inteligentes
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/ui_web.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../paginas/login.html");
    exit();
}

/**
 * Coordenadas manuales de cada papelera sobre la imagen del Centro Histórico.
 * id_bin => [x, y] (en porcentaje)
 */
$coordenadas = [
    1 => [27, 42], // Hotel Casa Don Luis
    2 => [13, 72], // Santo Domingo
    3 => [37, 83], // Sophia Hotel
    4 => [79, 78], // Casa Jaguar Hotel
    5 => [46, 46], // Hotel Casona del Porvenir
    6 => [74, 40], // Osh Hotel Cartagena
    7 => [69, 56], // Los Patios Hostel
    8 => [64, 78], // GHL Arsenal Hotel
];

$res = mysqli_query($conn, "SELECT id_bin, ubicacion, capacidad_maxima, nivel_actual, estado FROM smartbin ORDER BY id_bin");
$smartbins = [];
while ($row = mysqli_fetch_assoc($res)) {
    $id = (int) $row['id_bin'];
    $nivel = $row['capacidad_maxima'] > 0 ? ($row['nivel_actual'] / $row['capacidad_maxima']) * 100 : 0;
    $bloqueada = $row['estado'] === 'Bloqueado' || $nivel >= 100;
    $smartbins[] = [
        'id' => $id,
        'nombre' => $row['ubicacion'],
        'x' => $coordenadas[$id][0] ?? null,
        'y' => $coordenadas[$id][1] ?? null,
        'nivel' => round($nivel, 1),
        'kg_actual' => (float) $row['nivel_actual'],
        'kg_max' => (float) $row['capacidad_maxima'],
        // Mismo criterio de colores que la app móvil
        'estado' => $bloqueada ? 'llena' : ($nivel >= 75 ? 'alerta' : 'ok'),
    ];
}
$disponibles = count(array_filter($smartbins, fn($b) => $b['estado'] !== 'llena'));

include __DIR__ . "/../vistas/MapaSmartBins.view.php";
