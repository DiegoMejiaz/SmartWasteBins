<?php
// logica_puntos.php - Reglas de negocio compartidas por la web y la API móvil:
// rangos de reciclador y pasarela de canje de puntos (Nequi / Daviplata).

const CANJE_COP_POR_PUNTO = 10;      // 1 punto = $10 COP
const CANJE_MINIMO_PUNTOS = 100;     // canje mínimo: 100 pts = $1.000 COP
const CANJE_METODOS = ['NEQUI' => 'Nequi', 'DAVIPLATA' => 'Daviplata'];

/** Niveles de reconocimiento: [puntos mínimos, nombre], de menor a mayor. */
const RANGOS = [
    [0, 'Novato'],
    [5000, 'Aprendiz'],
    [10000, 'Reciclador Activo'],
    [100000, 'Reciclador Conciente'],
    [250000, 'Maestro Eco'],
    [500000, 'Salvando vidas'],
];

function rangoPorPuntos(int $puntos): string {
    $nombre = RANGOS[0][1];
    foreach (RANGOS as [$minimo, $rango]) {
        if ($puntos >= $minimo) $nombre = $rango;
    }
    return $nombre;
}

/** Progreso hacia el siguiente rango: ['nombre', 'faltan', 'progreso' (0-100)] o null si ya es el máximo. */
function siguienteRango(int $puntos): ?array {
    for ($i = 1; $i < count(RANGOS); $i++) {
        [$minimo, $nombre] = RANGOS[$i];
        if ($puntos < $minimo) {
            $anterior = RANGOS[$i - 1][0];
            return [
                'nombre' => $nombre,
                'faltan' => $minimo - $puntos,
                'progreso' => (int) round(100 * ($puntos - $anterior) / ($minimo - $anterior)),
            ];
        }
    }
    return null;
}

/**
 * Expresión SQL de los puntos acumulados (histórico): saldo actual + todo lo canjeado.
 * Rango, barra de progreso y clasificación usan este valor, así canjear no hace bajar de rango
 * ni de puesto; solo descuenta del saldo disponible. $cp es el alias de la tabla cuenta_puntos.
 */
function sqlPuntosAcumulados(string $cp = 'cp'): string {
    return "(IFNULL($cp.saldo_actual, 0) + IFNULL((SELECT SUM(ABS(tc.cantidad)) FROM transaccion_puntos tc
             WHERE tc.id_cuenta = $cp.id_cuenta AND (tc.tipo LIKE 'CANJE%' OR tc.tipo LIKE '%RETIRO%')), 0))";
}

/** Error de validación o de negocio del canje (mensaje apto para mostrar al usuario). */
class CanjeException extends Exception {
    public int $codigoHttp;
    public function __construct(string $mensaje, int $codigoHttp = 400) {
        parent::__construct($mensaje);
        $this->codigoHttp = $codigoHttp;
    }
}

function enmascararTelefono(string $telefono): string {
    return substr($telefono, 0, 3) . ' *** ' . substr($telefono, -4);
}

/**
 * Pasarela de canje: descuenta los puntos y registra la transferencia en una única transacción.
 * Bloquea la cuenta (FOR UPDATE) para que dos canjes simultáneos no gasten el mismo saldo.
 * Devuelve el comprobante. Lanza CanjeException ante datos inválidos o saldo insuficiente.
 *
 * Nota: es una pasarela de demostración; no se conecta con Nequi/Daviplata ni mueve dinero real.
 */
function canjearPuntos(mysqli $conn, int $id_usuario, string $metodo, string $telefono, int $puntos): array {
    $metodo = strtoupper(trim($metodo));
    $telefono = preg_replace('/\D/', '', $telefono);

    if (!isset(CANJE_METODOS[$metodo])) {
        throw new CanjeException("Elige un método de pago válido (Nequi o Daviplata).");
    }
    if (!preg_match('/^3\d{9}$/', $telefono)) {
        throw new CanjeException("El celular debe tener 10 dígitos y empezar por 3.");
    }
    if ($puntos < CANJE_MINIMO_PUNTOS) {
        throw new CanjeException("El canje mínimo es de " . number_format(CANJE_MINIMO_PUNTOS, 0, ',', '.') . " puntos.");
    }

    mysqli_begin_transaction($conn);
    try {
        $stmt = mysqli_prepare($conn, "SELECT id_cuenta, saldo_actual FROM cuenta_puntos WHERE id_usuario = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $id_usuario);
        mysqli_stmt_execute($stmt);
        $cuenta = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$cuenta) {
            throw new CanjeException("Tu cuenta de puntos no existe.", 404);
        }
        $saldo = (int) $cuenta['saldo_actual'];
        if ($puntos > $saldo) {
            throw new CanjeException("Saldo insuficiente: quieres canjear " . number_format($puntos, 0, ',', '.')
                . " pts y tienes " . number_format($saldo, 0, ',', '.') . " pts.", 409);
        }

        $id_cuenta = (int) $cuenta['id_cuenta'];
        $nuevo_saldo = $saldo - $puntos;
        $valor_cop = $puntos * CANJE_COP_POR_PUNTO;
        $referencia = 'SWB-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $concepto = 'CANJE_' . $metodo;

        $stmt = mysqli_prepare($conn, "UPDATE cuenta_puntos SET saldo_actual = ?, fecha_actualizacion = NOW() WHERE id_cuenta = ?");
        mysqli_stmt_bind_param($stmt, "ii", $nuevo_saldo, $id_cuenta);
        mysqli_stmt_execute($stmt);

        $stmt = mysqli_prepare($conn, "INSERT INTO transaccion_puntos (id_cuenta, tipo, cantidad, fecha) VALUES (?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "isi", $id_cuenta, $concepto, $puntos);
        mysqli_stmt_execute($stmt);
        $id_transaccion = mysqli_insert_id($conn);

        $stmt = mysqli_prepare($conn,
            "INSERT INTO canje (referencia, id_usuario, id_transaccion, metodo, telefono, puntos, valor_cop, estado, fecha)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'APROBADO', NOW())");
        mysqli_stmt_bind_param($stmt, "siissii", $referencia, $id_usuario, $id_transaccion, $metodo, $telefono, $puntos, $valor_cop);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    $comprobante = obtenerCanje($conn, $referencia, $id_usuario);
    $comprobante['nuevo_saldo'] = $nuevo_saldo;
    return $comprobante;
}

/** Comprobante de un canje del usuario (null si no existe o pertenece a otro usuario). */
function obtenerCanje(mysqli $conn, string $referencia, int $id_usuario): ?array {
    $stmt = mysqli_prepare($conn,
        "SELECT referencia, metodo, telefono, puntos, valor_cop, estado, fecha
         FROM canje WHERE referencia = ? AND id_usuario = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "si", $referencia, $id_usuario);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$fila) return null;
    return [
        'referencia' => $fila['referencia'],
        'metodo' => $fila['metodo'],
        'metodo_nombre' => CANJE_METODOS[$fila['metodo']] ?? $fila['metodo'],
        'telefono_enmascarado' => enmascararTelefono($fila['telefono']),
        'puntos' => (int) $fila['puntos'],
        'valor_cop' => (int) $fila['valor_cop'],
        'estado' => $fila['estado'],
        'fecha' => $fila['fecha'],
    ];
}
