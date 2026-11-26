# GUÍA RÁPIDA DE DESPLIEGUE EN INFINITYFREE (HOSTING GRATUITO)
**Proyecto de Aula: Smart Waste Bin (SWB) — 6to Semestre UNICOLOMBO**

Esta guía describe los 4 pasos para publicar la plataforma web y la API REST en Internet utilizando el servicio gratuito de **InfinityFree** (soporta PHP 8.2 y MariaDB/MySQL).

---

## PASO 1: Crear Cuenta en InfinityFree
1. Ingresa a [https://www.infinityfree.com](https://www.infinityfree.com) y regístrate con tu correo universitario o personal.
2. En el panel principal, haz clic en **"Create Account"**.
3. Selecciona un subdominio gratuito (por ejemplo: `smartwastebin.rf.gd` o `swb-unicolombo.free.nf`).
4. Haz clic en **"Create Account"** y espera que se active (tarda 1 minuto).

---

## PASO 2: Crear la Base de Datos e Importar `bd_smartbin.sql`
1. En tu panel de InfinityFree, abre el **Control Panel (vPanel)**.
2. Ve a la sección **Databases** y selecciona **"MySQL Databases"**.
3. En *Create New Database*, escribe: `bd_smartbin` y dale clic a **Create Database**.
4. Anota los datos de conexión que te muestra InfinityFree:
   - **MySQL Hostname:** (ej: `sql123.infinityfree.com`)
   - **MySQL Username:** (ej: `epiz_38291024`)
   - **MySQL Password:** (tu contraseña de cuenta)
   - **MySQL Database Name:** (ej: `epiz_38291024_bd_smartbin`)
5. Haz clic en **"phpMyAdmin"** al lado de tu base de datos creada.
6. En phpMyAdmin, ve a la pestaña **Importar** (Import), selecciona el archivo `bd_smartbin.sql` que está en tu proyecto y haz clic en **Continuar / Importar**.

---

## PASO 3: Subir los Archivos Web al Servidor
1. En el vPanel o en tu panel de InfinityFree, abre el **Online File Manager** (o conéctate por FileZilla con las credenciales FTP).
2. Abre la carpeta obligatoria: **`htdocs/`** (elimina cualquier archivo de bienvenida `default.php` o `index2.html` que esté ahí).
3. Sube todos los archivos y carpetas del proyecto:
   - `index.html`
   - `login.html`, `login.php`
   - `insertar_usuario.html`, `insertar_usuario.php`
   - `CuentaPuntos.php`, `CuentaPuntos.view.php`
   - `RegistrarResiduos.php`, `RegistrarResiduos.view.php`, `procesar_deposito.php`
   - `RetirarPuntos.php`, `RetirarPuntos.view.php`, `procesar_retiro.php`
   - `MapaSmartBins.php`, `MapaSmartBins.view.php`
   - `Clasificacion.php`, `Clasificacion.view.php`
   - `Transacciones.php`, `Transacciones.view.php`
   - `AdminPanel.php`, `AdminPanel.view.php`, `vaciar_bin.php`
   - `background.jpg`, `mapa_cartagena.jpg`
   - `eco_widgets.css`, `eco_widgets.js`
   - Carpeta `api/` con todos sus scripts REST.

---

## PASO 4: Configurar `db.php`
En el archivo `db.php` subido en `htdocs/`, asegúrate de colocar las credenciales de tu vPanel anotadas en el Paso 2:

```php
<?php
// Detección automática localhost vs servidor remoto
$is_localhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || ($_SERVER['SERVER_NAME'] ?? '') === 'localhost';

if ($is_localhost) {
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "bd_smartbin";
} else {
    // Coloca aquí los datos exactos que te dio InfinityFree en MySQL Databases:
    $db_host = "sql123.infinityfree.com";        // Tu MySQL Hostname
    $db_user = "epiz_38291024";                 // Tu MySQL Username
    $db_pass = "tu_contraseña_de_infinityfree"; // Tu MySQL Password
    $db_name = "epiz_38291024_bd_smartbin";     // Tu Database Name
}

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>
```

---

## ¡LISTO!
Ingresa a tu dominio gratuito (ej: `http://smartwastebin.rf.gd`) desde cualquier celular, computadora o dispositivo móvil para verificar la plataforma operando 100% en vivo en Internet.
