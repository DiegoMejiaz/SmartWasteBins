<?php
// api/depositar.php - Registro de depósito de residuos desde la app móvil
require_once __DIR__ . "/config.php";
exigirMetodo("POST");

// Requiere sesión: el depósito siempre se acredita al usuario dueño del token
$id_usuario = exigirUsuario($conn);

$data = getRequestData();
$id_bin     = isset($data['id_bin']) ? intval($data['id_bin']) : 0;
$tipo       = strtoupper(trim($data['tipo'] ?? ''));
$peso       = isset($data['peso']) ? floatval($data['peso']) : 0.0;

if ($id_usuario <= 0 || $id_bin <= 0 || $tipo === '' || $peso <= 0) {
    responderError(400, "Datos incompletos o peso inválido (debe ser mayor a 0).");
}

// Puntos por kg según la fracción
$puntos_por_tipo = ['PLASTICO' => 10, 'ORGANICO' => 15, 'ELECTRONICO' => 50, 'PAPEL_CARTON' => 5];
if (!isset($puntos_por_tipo[$tipo])) {
    responderError(400, "Tipo de residuo inválido. Use PLASTICO, ORGANICO, ELECTRONICO o PAPEL_CARTON.");
}
$puntos_ganados = intval(round($peso * $puntos_por_tipo[$tipo]));

// 1. Verificar usuario
if (!obtenerFila($conn, "SELECT id_usuario FROM usuario WHERE id_usuario = ?", "i", $id_usuario)) {
    responderError(404, "El usuario no existe.");
}

mysqli_begin_transaction($conn);
try {
    // 2. Bloquear la fila de la papelera para evitar depósitos simultáneos que superen la capacidad
    $bin = obtenerFila(
        $conn,
        "SELECT id_bin, ubicacion, nivel_actual, capacidad_maxima, estado FROM smartbin WHERE id_bin = ? FOR UPDATE",
        "i", $id_bin
    );
    if (!$bin) {
        mysqli_rollback($conn);
        responderError(404, "La papelera seleccionada no existe.");
    }

    $cap_max = floatval($bin['capacidad_maxima']);
    $nivel_actual = floatval($bin['nivel_actual']);
    $cap_restante = $cap_max - $nivel_actual;

    if ($bin['estado'] === 'Bloqueado' || $nivel_actual >= $cap_max) {
        mysqli_rollback($conn);
        responderError(409, "Esta papelera está llena o bloqueada. Elige otra ubicación.");
    }
    if ($peso > $cap_restante) {
        mysqli_rollback($conn);
        responderError(409, "El peso ingresado (" . round($peso, 2) . " kg) excede la capacidad disponible (" . round($cap_restante, 2) . " kg).");
    }

    // 3. Registrar residuo
    ejecutar($conn, "INSERT INTO residuo (id_usuario, id_bin, tipo, peso, fecha) VALUES (?, ?, ?, ?, NOW())",
        "iisd", $id_usuario, $id_bin, $tipo, $peso);

    // 4. Obtener o crear la cuenta de puntos
    $cuenta = obtenerFila($conn, "SELECT id_cuenta, saldo_actual FROM cuenta_puntos WHERE id_usuario = ? FOR UPDATE", "i", $id_usuario);
    if (!$cuenta) {
        ejecutar($conn, "INSERT INTO cuenta_puntos (id_usuario, saldo_actual, fecha_actualizacion) VALUES (?, 0, NOW())", "i", $id_usuario);
        $id_cuenta = mysqli_insert_id($conn);
        $saldo_previo = 0;
    } else {
        $id_cuenta = intval($cuenta['id_cuenta']);
        $saldo_previo = intval($cuenta['saldo_actual']);
    }
    $nuevo_saldo = $saldo_previo + $puntos_ganados;

    // 5. Actualizar saldo y registrar movimiento
    ejecutar($conn, "UPDATE cuenta_puntos SET saldo_actual = ?, fecha_actualizacion = NOW() WHERE id_cuenta = ?",
        "ii", $nuevo_saldo, $id_cuenta);
    ejecutar($conn, "INSERT INTO transaccion_puntos (id_cuenta, tipo, cantidad, fecha) VALUES (?, ?, ?, NOW())",
        "isi", $id_cuenta, "DEPOSITO_" . $tipo, $puntos_ganados);

    // 6. Actualizar nivel de llenado y bloquear si se llena
    $nuevo_nivel = $nivel_actual + $peso;
    $esta_bloqueada = $nuevo_nivel >= $cap_max;
    ejecutar($conn, "UPDATE smartbin SET nivel_actual = ?, estado = IF(?, 'Bloqueado', estado) WHERE id_bin = ?",
        "dii", $nuevo_nivel, $esta_bloqueada ? 1 : 0, $id_bin);

    mysqli_commit($conn);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    throw $e;
}

responder(200, [
    "success" => true,
    "message" => "¡Depósito registrado con éxito!",
    "detalles" => [
        "tipo" => $tipo,
        "peso_kg" => round($peso, 2),
        "puntos_ganados" => $puntos_ganados,
        "nuevo_saldo_usuario" => $nuevo_saldo,
        "smartbin" => [
            "id_bin" => $id_bin,
            "ubicacion" => $bin['ubicacion'],
            "nuevo_nivel_kg" => round($nuevo_nivel, 2),
            "capacidad_maxima" => $cap_max,
            "porcentaje_llenado" => $cap_max > 0 ? round(($nuevo_nivel / $cap_max) * 100, 1) : 0,
            "bloqueada" => $esta_bloqueada
        ]
    ]
]);
