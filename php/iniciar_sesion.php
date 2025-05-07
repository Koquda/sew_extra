<?php
/**
 * Sección de inicio de sesión
 * @author Alejandro Campa Martínez
 */

// Verificar si hay un mensaje de registro exitoso
$mensaje_registro = isset($_GET['registro']) && $_GET['registro'] === 'ok' ? 'Registro completado con éxito. Ahora puede iniciar sesión.' : '';
?>

<section data-page="login">
    <h2>Iniciar sesión</h2>
    
    <?php if (!empty($mensaje_registro)): ?>
        <aside role="status">
            <?php echo $mensaje_registro; ?>
        </aside>
    <?php endif; ?>
    
    <?php if (!empty($error_login)): ?>
        <aside role="alert">
            <?php echo $error_login; ?>
        </aside>
    <?php endif; ?>
    
    <form method="post" action="reservas.php">
        <fieldset>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </fieldset>
        <fieldset>
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </fieldset>
        <fieldset>
            <button type="submit" name="iniciar_sesion">Iniciar sesión</button>
        </fieldset>
    </form>
    
    <footer data-type="links">
        <p>¿No tiene cuenta? <a href="reservas.php?accion=registrarse">Regístrese aquí</a></p>
    </footer>
</section>
