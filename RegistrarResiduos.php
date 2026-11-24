<?php
// RegistrarResiduos.php - Formulario de depósito de residuos
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$res_bins = mysqli_query($conn, "SELECT id_bin, ubicacion, nivel_actual, capacidad_maxima, estado FROM smartbin ORDER BY id_bin");
$bins = [];
while ($bin = mysqli_fetch_assoc($res_bins)) {
    $disponible = max(0, $bin['capacidad_maxima'] - $bin['nivel_actual']);
    $bins[] = [
        'id' => (int) $bin['id_bin'],
        'ubicacion' => $bin['ubicacion'],
        'disponible' => round($disponible, 2),
        'capacidad' => (float) $bin['capacidad_maxima'],
        'lleno' => ($bin['nivel_actual'] >= $bin['capacidad_maxima'] || $bin['estado'] === 'Bloqueado'),
    ];
}

$tipos = [
    'PLASTICO' => ['Plástico', 10],
    'ORGANICO' => ['Orgánico', 15],
    'ELECTRONICO' => ['Electrónico', 50],
    'PAPEL_CARTON' => ['Papel y cartón', 5],
];

include("RegistrarResiduos.view.php");
