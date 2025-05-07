<?php
/**
 * Sección de registro de usuario
 * @author Alejandro Campa Martínez
 */
?>

<section data-page="registro">
    <h2>Registro de usuario</h2>
    
    <?php if (!empty($errores)): ?>
        <aside role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </aside>
    <?php endif; ?>
    
    <form method="post" action="reservas.php">
        <fieldset>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo isset($nombre) ? $nombre : ''; ?>" required>
        </fieldset>
        <fieldset>
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" value="<?php echo isset($apellidos) ? $apellidos : ''; ?>" required>
        </fieldset>
        <fieldset>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
        </fieldset>
        <fieldset>
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" value="<?php echo isset($telefono) ? $telefono : ''; ?>" required>
        </fieldset>
        <fieldset>
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </fieldset>
        <fieldset>
            <label for="confirmar_contrasena">Confirmar contraseña:</label>
            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
        </fieldset>
        <fieldset>
            <button type="submit" name="registrar_usuario">Registrarse</button>
        </fieldset>
    </form>
    
    <footer data-type="links">
        <p>¿Ya tiene cuenta? <a href="reservas.php?accion=iniciar_sesion">Inicie sesión aquí</a></p>
    </footer>
</section>
