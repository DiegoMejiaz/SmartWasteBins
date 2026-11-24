<?php
// api/smartbins.php - Listado de papeleras inteligentes y telemetría de llenado
require_once __DIR__ . "/config.php";

$filas = obtenerFilas($conn, "SELECT id_bin, ubicacion, capacidad_maxima, nivel_actual, estado FROM smartbin ORDER BY id_bin ASC");

$bins = [];
foreach ($filas as $row) {
    $cap_max = floatval($row['capacidad_maxima']);
    $niv_act = floatval($row['nivel_actual']);
    $porcentaje = ($cap_max > 0) ? round(($niv_act / $cap_max) * 100, 1) : 0;

    $bins[] = [
        "id_bin" => intval($row['id_bin']),
        "ubicacion" => $row['ubicacion'],
        "capacidad_maxima" => $cap_max,
        "nivel_actual" => $niv_act,
        "porcentaje_llenado" => $porcentaje,
        "capacidad_restante" => round(max(0, $cap_max - $niv_act), 2),
        "estado" => $row['estado'],
        "disponible" => ($row['estado'] === 'Activo' && $niv_act < $cap_max)
    ];
}

responder(200, [
    "success" => true,
    "total" => count($bins),
    "smartbins" => $bins
]);
