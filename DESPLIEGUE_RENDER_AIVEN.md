# Despliegue de Smart Waste Bin en Render + Aiven

```
App móvil ─┐
           ├─ HTTPS ─► Render (PHP 8.3 + Apache, Docker) ─► MySQL en Aiven (SSL)
Navegador ─┘
```

Todos los usuarios, desde cualquier dispositivo, comparten la misma base de datos
(registro, puntos, canjes y clasificación).

> Reemplaza a `GUIA_DESPLIEGUE_INFINITYFREE.md`: el plan gratuito de InfinityFree bloquea
> las peticiones de apps móviles, por eso la app no funcionaría allí.

## 1. Base de datos en Aiven

1. En <https://console.aiven.io> crea un servicio **MySQL** (plan *Free*). Si puedes elegir
   región, usa una de EE. UU. Este (cerca de la región `virginia` de Render).
2. Cuando el servicio esté *Running*, en **Overview → Connection information** anota
   `Host`, `Port`, `User` (`avnadmin`) y `Password`.
3. Descarga el **CA certificate** y guárdalo como `ca.pem` en la carpeta del proyecto
   (está en `.gitignore`, no se sube a GitHub).

### Crear las tablas (desde tu PC, una sola vez)

En PowerShell, dentro de la carpeta `Smart_waste_bin_6to_semestre`:

```powershell
$env:DB_HOST="TU-HOST.aivencloud.com"; $env:DB_PORT="12345"; $env:DB_USER="avnadmin"
$env:DB_PASSWORD = Read-Host "Contraseña de Aiven"
$env:DB_NAME="bd_smartbin"; $env:DB_CA_CERT = Get-Content .\ca.pem -Raw
C:\xampp\php\php.exe herramientas\importar_bd.php
```

Debe terminar con `Papeleras cargadas: 8`. El importador no modifica una base que ya
tenga tablas (para reemplazarla y **borrar todos los datos**: `--forzar`).

## 2. Subir el código a GitHub

Crea un repositorio **vacío** en GitHub (sin README) y luego:

```powershell
git remote add origin https://github.com/TU-USUARIO/smart-waste-bin.git
git push -u origin main
```

## 3. Servicio web en Render

1. En <https://dashboard.render.com>: **New → Blueprint** y elige el repositorio.
   Render lee `render.yaml` y crea el servicio `smart-waste-bin` (Docker, plan *Free*).
2. Te pedirá los valores marcados como secretos; copia los de Aiven:

   | Variable      | Valor                                              |
   |---------------|----------------------------------------------------|
   | `DB_HOST`     | Host de Aiven                                      |
   | `DB_PORT`     | Puerto de Aiven                                    |
   | `DB_USER`     | `avnadmin`                                         |
   | `DB_PASSWORD` | Contraseña de Aiven                                |
   | `DB_CA_CERT`  | Contenido completo de `ca.pem` (desde `-----BEGIN`) |

3. Espera a que el despliegue termine (*Live*). Tu web queda en
   `https://smart-waste-bin.onrender.com` (o el nombre que Render asigne).
4. Comprueba la API: `https://TU-SERVICIO.onrender.com/api/smartbins.php` debe responder
   un JSON con `"success":true` y 8 papeleras.

Cada `git push` a `main` vuelve a desplegar automáticamente.

## 4. App móvil

- **Rápido (para probar):** en la pantalla de acceso toca **Servidor** y escribe
  `https://TU-SERVICIO.onrender.com/api/`.
- **Permanente:** en `Smart_waste_bin_mobile/gradle.properties` agrega
  `SMARTBIN_API_URL=https://TU-SERVICIO.onrender.com/api/` y vuelve a compilar.

## Notas

- **Plan gratuito de Render:** tras ~15 min sin uso el servidor se duerme y la siguiente
  visita tarda cerca de un minuto. La app espera hasta 75 s y avisa "Despertando el
  servidor…". Un plan pago de Render evita esta espera.
- **Sesiones web:** se guardan en el contenedor; cada despliegue nuevo cierra las sesiones
  abiertas de la web (la app no se ve afectada: usa tokens en la base de datos).
- **Desarrollo local:** sin `DB_HOST` definido, todo sigue usando XAMPP (`root@localhost`).
- **Pasarela de canje:** es de demostración; no mueve dinero real.
