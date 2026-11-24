<?php
// procesar_deposito.php - Registra un depósito de residuos desde la web
session_start();
include("db.php");
require_once __DIR__ . "/ui_web.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // cualquier fallo SQL revierte la transacción

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: RegistrarResiduos.php");
    exit();
}

function responderAlerta(string $icono, string $titulo, string $html, string $redirigir = 'RegistrarResiduos.php'): void {
    paginaAlerta($icono, $titulo, $html, $redirigir);
}

$id_usuario = intval($_SESSION['user_id']);
$tipo = strtoupper(trim($_POST['tipo'] ?? ''));
$peso = floatval($_POST['peso'] ?? 0);
$id_bin = intval($_POST['bin'] ?? 0);

$puntos_por_tipo = ['PLASTICO' => 10, 'ORGANICO' => 15, 'ELECTRONICO' => 50, 'PAPEL_CARTON' => 5];
$nombres_tipo = ['PLASTICO' => 'Plástico', 'ORGANICO' => 'Orgánico', 'ELECTRONICO' => 'Electrónico', 'PAPEL_CARTON' => 'Papel y cartón'];

if (!isset($puntos_por_tipo[$tipo])) {
    responderAlerta('warning', 'Tipo de residuo inválido', 'Selecciona plástico, orgánico, electrónico o papel y cartón.');
}
if ($peso <= 0) {
    responderAlerta('warning', 'Pesaje no detectado', 'El residuo colocado no registra peso suficiente en la celda de carga HX711.');
}
if ($id_bin <= 0) {
    responderAlerta('warning', 'Selecciona una papelera', 'Elige la estación SmartBin donde depositaste el residuo.');
}

$puntos_ganados = (int) round($peso * $puntos_por_tipo[$tipo]);

mysqli_begin_transaction($conn);
try {
    // Bloquear la papelera para evitar que dos depósitos simultáneos superen su capacidad
    $stmt = mysqli_prepare($conn, "SELECT ubicacion, nivel_actual, capacidad_maxima, estado FROM smartbin WHERE id_bin = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $id_bin);
    mysqli_stmt_execute($stmt);
    $bin_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$bin_info) {
        mysqli_rollback($conn);
        responderAlerta('error', 'Papelera no encontrada', 'La estación seleccionada no existe.');
    }
    $ubicacion = e($bin_info['ubicacion']);
    $nivel = (float) $bin_info['nivel_actual'];
    $capacidad = (float) $bin_info['capacidad_maxima'];

    if ($bin_info['estado'] === 'Bloqueado' || $nivel >= $capacidad) {
        mysqli_rollback($conn);
        responderAlerta('error', 'Contenedor bloqueado', "La estación <strong>$ubicacion</strong> alcanzó su capacidad máxima (" . formatoKg($capacidad) . " kg). Usa otra papelera.");
    }
    $capacidad_restante = $capacidad - $nivel;
    if ($peso > $capacidad_restante) {
        mysqli_rollback($conn);
        responderAlerta('warning', 'Capacidad insuficiente', "El residuo (" . formatoKg($peso) . " kg) supera la capacidad disponible (" . number_format($capacidad_restante, 2, ',', '.') . " kg) de esta estación.");
    }

    // 1. Residuo
    $stmt = mysqli_prepare($conn, "INSERT INTO residuo (id_usuario, id_bin, tipo, peso, fecha) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "iisd", $id_usuario, $id_bin, $tipo, $peso);
    mysqli_stmt_execute($stmt);

    // 2. Saldo
    $stmt = mysqli_prepare($conn, "UPDATE cuenta_puntos SET saldo_actual = saldo_actual + ?, fecha_actualizacion = NOW() WHERE id_usuario = ?");
    mysqli_stmt_bind_param($stmt, "ii", $puntos_ganados, $id_usuario);
    mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($conn, "SELECT id_cuenta, saldo_actual FROM cuenta_puntos WHERE id_usuario = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $cuenta = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$cuenta) {
        throw new Exception("Cuenta de puntos inexistente");
    }

    // 3. Auditoría
    $concepto = 'ABONO_RECICLAJE';
    $stmt = mysqli_prepare($conn, "INSERT INTO transaccion_puntos (id_cuenta, tipo, cantidad, fecha) VALUES (?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "isi", $cuenta['id_cuenta'], $concepto, $puntos_ganados);
    mysqli_stmt_execute($stmt);

    // 4. Nivel de la papelera
    $nuevo_nivel = $nivel + $peso;
    $estado_nuevo = ($nuevo_nivel >= $capacidad) ? 'Bloqueado' : 'Activo';
    $stmt = mysqli_prepare($conn, "UPDATE smartbin SET nivel_actual = ?, estado = ? WHERE id_bin = ?");
    mysqli_stmt_bind_param($stmt, "dsi", $nuevo_nivel, $estado_nuevo, $id_bin);
    mysqli_stmt_execute($stmt);

    mysqli_commit($conn);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    error_log("[SmartWasteBin] Depósito fallido: " . $e->getMessage());
    responderAlerta('error', 'No se pudo registrar', 'Ocurrió un error al guardar el depósito. Intenta de nuevo.');
}

$extra = ($estado_nuevo === 'Bloqueado')
    ? "<br><br><span style='color:var(--llena)'>Esta papelera alcanzó su capacidad y se bloqueó preventivamente.</span>"
    : "";

responderAlerta(
    'success',
    '¡Depósito registrado!',
    "Depositaste <strong>" . formatoKg($peso) . " kg</strong> de <strong>{$nombres_tipo[$tipo]}</strong> en <strong>$ubicacion</strong>.<br><br>"
    . "Ganaste <strong style='color:var(--primario);font-size:1.2rem'>+" . formatoPuntos($puntos_ganados) . " pts</strong><br>"
    . "Tu saldo: <strong>" . formatoPuntos($cuenta['saldo_actual']) . " pts</strong>$extra",
    'CuentaPuntos.php'
);
