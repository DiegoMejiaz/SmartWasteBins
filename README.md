# Smart Waste Bin: web y API

```
index.html      Página de inicio (portada)
paginas/        Pantallas que ve el usuario: login, panel de puntos, depósitos, canjes, clasificación, mapa, admin
vistas/         Plantillas HTML de cada pantalla (paginas/X.php carga vistas/X.view.php)
acciones/       Procesan formularios y redirigen: login, registro, depositar, canjear, editar, eliminar, cerrar sesión
includes/       Código compartido: conexión a la base de datos, cuentas (registro e inicio de sesión), reglas de puntos y canjes, diseño común
api/            API JSON que usa la app móvil (la ruta /api/ no debe cambiar)
assets/         css/, js/ e img/ (estilos, scripts, íconos e imágenes)
database/       Script SQL de la base de datos (bd_smartbin.sql)
herramientas/   Scripts de consola: importar la base y migrar cuentas a Aiven
docker/         Configuración del contenedor (Apache, PHP, arranque)
docs/           Manuales, diagramas UML y guía de despliegue en Render + Aiven
Dockerfile, render.yaml   Despliegue en Render
```

Las carpetas `includes/`, `vistas/`, `database/`, `docs/` y `herramientas/` no se pueden abrir desde el
navegador (`.htaccess` en XAMPP y `docker/apache-seguridad.conf` en Render).

Guía de despliegue: [docs/DESPLIEGUE_RENDER_AIVEN.md](docs/DESPLIEGUE_RENDER_AIVEN.md)
