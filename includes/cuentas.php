<?php
// includes/cuentas.php - Registro e inicio de sesión compartidos por la web (acciones/) y la app (api/).
// Una sola implementación: las dos piden documento o alias + contraseña y validan con las mismas reglas.

/** Error de cuenta con su código HTTP (400 datos inválidos, 401 credenciales, 403 sin contraseña, 409 duplicado). */
class CuentaException extends Exception {}

const CONTRASENA_MIN = 6;
const CONTRASENA_MAX = 72; // límite de bcrypt

function cuentaFila(mysqli $conn, string $sql, string $tipos, ...$params): ?array {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $tipos, ...$params);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $fila ?: null;
}

function cuentaEjecutar(mysqli $conn, string $sql, string $tipos, ...$params): void {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $tipos, ...$params);
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException(mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
}

/**
 * Verifica documento (o alias) y contraseña. Devuelve id_usuario, nombre, correo, usuario y rol.
 * @throws CuentaException
 */
function autenticarUsuario(mysqli $conn, string $documentoOAlias, string $contrasena): array {
    $documentoOAlias = trim($documentoOAlias);
    if ($documentoOAlias === '' || $contrasena === '') {
        throw new CuentaException("Ingresa tu documento o alias y tu contraseña.", 400);
    }

    $sql = "SELECT id_usuario, nombre, correo, usuario, contrasena, rol FROM usuario WHERE ";
    $fila = ctype_digit($documentoOAlias)
        ? cuentaFila($conn, $sql . "id_usuario = ? LIMIT 1", "i", intval($documentoOAlias))
        : cuentaFila($conn, $sql . "usuario = ? LIMIT 1", "s", $documentoOAlias);

    if ($fila && empty($fila['contrasena'])) {
        throw new CuentaException("Tu cuenta aún no tiene contraseña. Ve a \"Crear cuenta\" e ingresa tu mismo documento, correo y alias para asignarle una.", 403);
    }
    // Mismo mensaje si no existe o si la contraseña es incorrecta (no revela qué cuentas existen)
    if (!$fila || !password_verify($contrasena, $fila['contrasena'])) {
        throw new CuentaException("Usuario o contraseña incorrectos.", 401);
    }

    // Actualiza el hash si PHP recomienda un algoritmo más reciente
    if (password_needs_rehash($fila['contrasena'], PASSWORD_DEFAULT)) {
        cuentaEjecutar($conn, "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?", "si",
            password_hash($contrasena, PASSWORD_DEFAULT), intval($fila['id_usuario']));
    }
    unset($fila['contrasena']);
    return $fila;
}

/**
 * Crea la cuenta y su monedero de puntos. Si ya existe una cuenta antigua SIN contraseña con el mismo
 * documento, correo y alias, le asigna la contraseña (la "reclama").
 * Devuelve true si se reclamó una cuenta existente, false si se creó una nueva.
 * @throws CuentaException
 */
function registrarUsuario(mysqli $conn, int $idUsuario, string $nombre, string $correo, string $alias, string $contrasena): bool {
    $nombre = trim($nombre);
    $correo = trim($correo);
    $alias = trim($alias);

    if ($idUsuario <= 0 || $nombre === '' || $correo === '' || $alias === '' || $contrasena === '') {
        throw new CuentaException("Todos los campos son obligatorios.", 400);
    }
    if ($idUsuario > 2147483647) { // límite de la columna INT
        throw new CuentaException("El documento de identidad no es válido.", 400);
    }
    if (mb_strlen($nombre) > 100) {
        throw new CuentaException("El nombre no puede superar 100 caracteres.", 400);
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 100) {
        throw new CuentaException("El correo electrónico no es válido.", 400);
    }
    if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $alias)) {
        throw new CuentaException("El alias debe tener entre 3 y 30 caracteres (letras, números, punto o guion bajo).", 400);
    }
    if (ctype_digit($alias)) {
        throw new CuentaException("El alias no puede ser solo números.", 400);
    }
    if (strlen($contrasena) < CONTRASENA_MIN || strlen($contrasena) > CONTRASENA_MAX) {
        throw new CuentaException("La contraseña debe tener entre " . CONTRASENA_MIN . " y " . CONTRASENA_MAX . " caracteres.", 400);
    }

    $existente = cuentaFila($conn,
        "SELECT id_usuario, correo, usuario, contrasena FROM usuario WHERE id_usuario = ? OR correo = ? OR usuario = ? LIMIT 1",
        "iss", $idUsuario, $correo, $alias);

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Cuenta antigua sin contraseña: se reclama si documento, correo y alias coinciden
    $reclamar = $existente && empty($existente['contrasena'])
        && intval($existente['id_usuario']) === $idUsuario
        && strcasecmp($existente['correo'] ?? '', $correo) === 0
        && strcasecmp($existente['usuario'] ?? '', $alias) === 0;

    if ($existente && !$reclamar) {
        if (intval($existente['id_usuario']) === $idUsuario) {
            throw new CuentaException("Ya existe una cuenta con ese documento de identidad.", 409);
        }
        if (strcasecmp($existente['correo'] ?? '', $correo) === 0) {
            throw new CuentaException("Ese correo electrónico ya está registrado.", 409);
        }
        throw new CuentaException("Ese alias ya está en uso. Elige otro.", 409);
    }

    // Usuario + cuenta de puntos en una sola transacción (todo o nada)
    mysqli_begin_transaction($conn);
    try {
        if ($reclamar) {
            cuentaEjecutar($conn, "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?", "si", $hash, $idUsuario);
        } else {
            cuentaEjecutar($conn,
                "INSERT INTO usuario (id_usuario, nombre, correo, usuario, contrasena, rol, qr_code, fecha_registro)
                 VALUES (?, ?, ?, ?, ?, 'user', ?, NOW())",
                "isssss", $idUsuario, $nombre, $correo, $alias, $hash, 'SWB_USER_' . $idUsuario);
        }
        if (!cuentaFila($conn, "SELECT id_cuenta FROM cuenta_puntos WHERE id_usuario = ?", "i", $idUsuario)) {
            cuentaEjecutar($conn, "INSERT INTO cuenta_puntos (id_usuario, saldo_actual, fecha_actualizacion) VALUES (?, 0, NOW())",
                "i", $idUsuario);
        }
        mysqli_commit($conn);
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        throw $e;
    }
    return $reclamar;
}
