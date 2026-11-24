<?php
/** @var array $user */
paginaInicio('Editar cuenta', ['atras' => 'CuentaPuntos.php']);
?>
<main class="contenedor contenedor-angosto">
    <div class="encabezado">
        <h1>Editar cuenta</h1>
        <p>Actualiza tus datos personales y de acceso.</p>
    </div>

    <form class="tarjeta" method="POST" action="procesar_edicion.php">
        <div class="campo">
            <label for="nombre">Nombre completo</label>
            <div class="entrada"><?= icono('person') ?><input type="text" name="nombre" id="nombre" value="<?= e($user['nombre']) ?>" autocomplete="name" required></div>
        </div>
        <div class="campo">
            <label for="correo">Correo electrónico</label>
            <div class="entrada"><?= icono('email') ?><input type="email" name="correo" id="correo" value="<?= e($user['correo']) ?>" autocomplete="email" required></div>
        </div>
        <div class="campo">
            <label for="usuario">Nombre de usuario</label>
            <div class="entrada"><?= icono('person') ?><input type="text" name="usuario" id="usuario" value="<?= e($user['usuario']) ?>" autocomplete="username" required></div>
        </div>
        <div class="botones" style="flex-direction:row">
            <a class="boton contorno" href="CuentaPuntos.php" style="flex:1">Cancelar</a>
            <button type="submit" class="boton" style="flex:2">Guardar cambios</button>
        </div>
    </form>
</main>
<?php paginaFin(); ?>
