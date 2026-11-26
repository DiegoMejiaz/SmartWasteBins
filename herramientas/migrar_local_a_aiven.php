<?php
// herramientas/migrar_local_a_aiven.php - Copia las cuentas del MySQL local (XAMPP) a la base compartida (Aiven).
//
// Se ejecuta desde tu PC con las mismas variables de entorno que importar_bd.php (DB_HOST, DB_PORT,
// DB_USER, DB_PASSWORD, DB_NAME, DB_CA_CERT). XAMPP debe estar encendido.
//
//   C:\xampp\php\php.exe herramientas\migrar_local_a_aiven.php             -> solo muestra qué haría
//   C:\xampp\php\php.exe herramientas\migrar_local_a_aiven.php --ejecutar  -> copia de verdad
//
// Por cada usuario local:
//   - No existe en Aiven (ni su documento, ni su correo, ni su alias) -> se copia completo:
//     cuenta, saldo, historial de puntos, depósitos y canjes (con su contraseña).
//   - Mismo documento y mismo correo en Aiven -> se FUSIONA: se suma el saldo local y se agrega su historial.
//   - Cualquier otro choque (alias o correo de otra persona) -> se omite y se informa.
// Las sesiones (tokens) no se copian: en la app solo hay que volver a iniciar sesión.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

require_once __DIR__ . '/../includes/conexion_bd.php';

/** Fila única o null. */
function fila(mysqli $c, string $sql, string $tipos = '', ...$p): ?array {
    $st = mysqli_prepare($c, $sql);
    if ($tipos !== '') mysqli_stmt_bind_param($st, $tipos, ...$p);
    mysqli_stmt_execute($st);
    $r = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    return $r ?: null;
}

function filas(mysqli $c, string $sql, string $tipos = '', ...$p): array {
    $st = mysqli_prepare($c, $sql);
    if ($tipos !== '') mysqli_stmt_bind_param($st, $tipos, ...$p);
    mysqli_stmt_execute($st);
    $r = mysqli_fetch_all(mysqli_stmt_get_result($st), MYSQLI_ASSOC);
    mysqli_stmt_close($st);
    return $r;
}

/** Ejecuta un INSERT/UPDATE y devuelve el id insertado. */
function escribir(mysqli $c, string $sql, string $tipos, ...$p): int {
    $st = mysqli_prepare($c, $sql);
    mysqli_stmt_bind_param($st, $tipos, ...$p);
    mysqli_stmt_execute($st);
    $id = (int) mysqli_insert_id($c);
    mysqli_stmt_close($st);
    return $id;
}

/** ¿Algún movimiento local de este usuario ya está en la cuenta compartida? (evita sumar dos veces) */
function yaMigrada(mysqli $origen, mysqli $destino, int $idUsuario, int $idCuentaDestino): bool {
    $movs = filas($origen, "SELECT t.tipo, t.cantidad, t.fecha FROM transaccion_puntos t
                            JOIN cuenta_puntos cp ON cp.id_cuenta = t.id_cuenta WHERE cp.id_usuario = ?", "i", $idUsuario);
    foreach ($movs as $t) {
        if (fila($destino, "SELECT 1 FROM transaccion_puntos WHERE id_cuenta = ? AND tipo = ? AND cantidad = ? AND fecha = ?",
                 "isis", $idCuentaDestino, $t['tipo'], $t['cantidad'], $t['fecha'])) {
            return true;
        }
    }
    return false;
}

/** Decide qué hacer con un usuario local: ['copiar'|'fusionar'|'omitir', motivo, id_cuenta_destino]. */
function decidir(mysqli $origen, mysqli $destino, array $u): array {
    $mismoId = fila($destino, "SELECT u.id_usuario, u.correo, cp.id_cuenta FROM usuario u
                               LEFT JOIN cuenta_puntos cp ON cp.id_usuario = u.id_usuario
                               WHERE u.id_usuario = ?", "i", $u['id_usuario']);
    if ($mismoId) {
        if (strcasecmp((string) $mismoId['correo'], (string) $u['correo']) === 0) {
            if ($mismoId['id_cuenta'] !== null && yaMigrada($origen, $destino, (int) $u['id_usuario'], (int) $mismoId['id_cuenta'])) {
                return ['omitir', 'ya se migró antes (su historial ya está en la base compartida)', null];
            }
            return ['fusionar', 'ya existe con el mismo documento y correo', $mismoId['id_cuenta']];
        }
        return ['omitir', 'el documento ya está registrado en la base compartida con otro correo', null];
    }
    if ($u['correo'] !== null && $u['correo'] !== ''
        && fila($destino, "SELECT 1 FROM usuario WHERE correo = ?", "s", $u['correo'])) {
        return ['omitir', 'su correo ya lo usa otra cuenta', null];
    }
    if ($u['usuario'] !== null && $u['usuario'] !== ''
        && fila($destino, "SELECT 1 FROM usuario WHERE usuario = ?", "s", $u['usuario'])) {
        return ['omitir', 'su alias ya lo usa otra cuenta', null];
    }
    return ['copiar', 'no existe en la base compartida', null];
}

/** Copia (o fusiona) un usuario con su historial dentro de una transacción. */
function migrarUsuario(mysqli $origen, mysqli $destino, array $u, string $accion, ?int $idCuentaDestino): void {
    $id = (int) $u['id_usuario'];
    $cuenta = fila($origen, "SELECT id_cuenta, saldo_actual FROM cuenta_puntos WHERE id_usuario = ?", "i", $id);
    $saldo = (int) ($cuenta['saldo_actual'] ?? 0);

    mysqli_begin_transaction($destino);
    try {
        if ($accion === 'copiar') {
            escribir($destino,
                "INSERT INTO usuario (id_usuario, nombre, correo, usuario, contrasena, rol, qr_code, fecha_registro)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                "isssssss", $id, $u['nombre'], $u['correo'], $u['usuario'], $u['contrasena'],
                $u['rol'], $u['qr_code'], $u['fecha_registro']);
            $idCuentaDestino = escribir($destino,
                "INSERT INTO cuenta_puntos (id_usuario, saldo_actual) VALUES (?, ?)", "ii", $id, $saldo);
        } else {
            if ($idCuentaDestino === null) {
                $idCuentaDestino = escribir($destino,
                    "INSERT INTO cuenta_puntos (id_usuario, saldo_actual) VALUES (?, ?)", "ii", $id, $saldo);
            } else {
                escribir($destino,
                    "UPDATE cuenta_puntos SET saldo_actual = saldo_actual + ?, fecha_actualizacion = NOW() WHERE id_cuenta = ?",
                    "ii", $saldo, $idCuentaDestino);
            }
            // Si la cuenta compartida no tiene contraseña y la local sí, se conserva la local
            if (!empty($u['contrasena'])) {
                escribir($destino,
                    "UPDATE usuario SET contrasena = ? WHERE id_usuario = ? AND (contrasena IS NULL OR contrasena = '')",
                    "si", $u['contrasena'], $id);
            }
        }

        // Historial de puntos (se guarda la equivalencia de ids para enlazar los canjes)
        $mapaTransacciones = [];
        if ($cuenta) {
            foreach (filas($origen, "SELECT * FROM transaccion_puntos WHERE id_cuenta = ? ORDER BY id_transaccion",
                           "i", $cuenta['id_cuenta']) as $t) {
                $mapaTransacciones[(int) $t['id_transaccion']] = escribir($destino,
                    "INSERT INTO transaccion_puntos (id_cuenta, tipo, cantidad, fecha) VALUES (?, ?, ?, ?)",
                    "isis", $idCuentaDestino, $t['tipo'], $t['cantidad'], $t['fecha']);
            }
        }

        // Depósitos (las papeleras tienen los mismos ids en ambas bases; si no existe, queda sin papelera)
        foreach (filas($origen, "SELECT * FROM residuo WHERE id_usuario = ? ORDER BY id_residuo", "i", $id) as $r) {
            $bin = $r['id_bin'] !== null && fila($destino, "SELECT 1 FROM smartbin WHERE id_bin = ?", "i", $r['id_bin'])
                ? (int) $r['id_bin'] : null;
            escribir($destino,
                "INSERT INTO residuo (id_usuario, id_bin, tipo, peso, fecha) VALUES (?, ?, ?, ?, ?)",
                "iisds", $id, $bin, $r['tipo'], $r['peso'], $r['fecha']);
        }

        // Canjes (la referencia es única; si por casualidad ya existe se marca como migrada)
        foreach (filas($origen, "SELECT * FROM canje WHERE id_usuario = ? ORDER BY id_canje", "i", $id) as $c) {
            $ref = $c['referencia'];
            if (fila($destino, "SELECT 1 FROM canje WHERE referencia = ?", "s", $ref)) {
                $ref = substr($ref, 0, 21) . '-M';
            }
            $idTrans = $c['id_transaccion'] !== null ? ($mapaTransacciones[(int) $c['id_transaccion']] ?? null) : null;
            escribir($destino,
                "INSERT INTO canje (referencia, id_usuario, id_transaccion, metodo, telefono, puntos, valor_cop, estado, fecha)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                "siissiiss", $ref, $id, $idTrans, $c['metodo'], $c['telefono'], $c['puntos'],
                $c['valor_cop'], $c['estado'], $c['fecha']);
        }

        mysqli_commit($destino);
    } catch (Throwable $e) {
        mysqli_rollback($destino);
        throw $e;
    }
}

function principal(mysqli $origen, mysqli $destino, bool $ejecutar): int {
    $usuarios = filas($origen, "SELECT * FROM usuario ORDER BY id_usuario");
    echo count($usuarios) . " cuenta(s) en tu MySQL local.\n\n";

    $copiadas = 0;
    $errores = 0;
    foreach ($usuarios as $u) {
        [$accion, $motivo, $idCuenta] = decidir($origen, $destino, $u);
        $saldo = (int) (fila($origen, "SELECT saldo_actual FROM cuenta_puntos WHERE id_usuario = ?", "i",
                             $u['id_usuario'])['saldo_actual'] ?? 0);
        $etiqueta = sprintf("@%s (%s, %s pts)", $u['usuario'] ?? '-', $u['id_usuario'], $saldo);
        $verbo = ['copiar' => 'COPIAR  ', 'fusionar' => 'FUSIONAR', 'omitir' => 'OMITIR  '][$accion];
        echo "  $verbo $etiqueta: $motivo\n";

        if (!$ejecutar || $accion === 'omitir') continue;
        try {
            migrarUsuario($origen, $destino, $u, $accion, $idCuenta === null ? null : (int) $idCuenta);
            $copiadas++;
        } catch (Throwable $e) {
            $errores++;
            echo "     ERROR, no se modificó esta cuenta: " . $e->getMessage() . "\n";
        }
    }

    if (!$ejecutar) {
        echo "\nModo simulación: no se cambió nada. Si está bien, vuelve a ejecutar con --ejecutar\n";
    } else {
        echo "\nListo: $copiadas cuenta(s) migradas" . ($errores ? ", $errores con error" : "") . ".\n";
    }
    return $errores ? 1 : 0;
}

// ---- Ejecución desde la línea de comandos
if (realpath($argv[0] ?? '') === __FILE__) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    if (!getenv('DB_HOST')) {
        fwrite(STDERR, "Falta DB_HOST. Define primero las variables de Aiven (igual que con importar_bd.php).\n");
        exit(1);
    }
    try {
        $origen = mysqli_connect("localhost", "root", "", "bd_smartbin");
        mysqli_set_charset($origen, "utf8mb4");
    } catch (Throwable $e) {
        fwrite(STDERR, "No se pudo abrir el MySQL local (¿XAMPP encendido?): " . $e->getMessage() . "\n");
        exit(1);
    }
    try {
        $destino = abrirConexionBD();
    } catch (Throwable $e) {
        fwrite(STDERR, "No se pudo conectar a Aiven: " . $e->getMessage() . "\n");
        exit(1);
    }
    echo "Origen: MySQL local (bd_smartbin)  ->  Destino: " . getenv('DB_HOST') . " / " . (getenv('DB_NAME') ?: 'bd_smartbin') . "\n";
    exit(principal($origen, $destino, in_array('--ejecutar', $argv, true)));
}
