<?php
/**
 * Sección de inicio de reservas
 * @author Alejandro Campa Martínez
 */
?>

<section data-page="inicio-reservas">
    <h2>Sistema de Reservas Turísticas de Pesoz</h2>
    
    <article data-type="descripcion">
        <p>Bienvenido al sistema de reservas turísticas de Pesoz. Aquí podrá reservar diferentes recursos turísticos de la zona:</p>
        <ul>
            <li>Alojamientos (hoteles, casas rurales, apartamentos)</li>
            <li>Restaurantes</li>
            <li>Museos</li>
            <li>Rutas guiadas</li>
            <li>Actividades deportivas</li>
        </ul>
    </article>
    
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Usuario logueado -->
        <section data-type="acciones-usuario">
            <h3>¿Qué desea hacer?</h3>
            <nav data-type="acciones">
                <a href="reservas.php?accion=recursos" role="button">Ver recursos turísticos</a>
                <a href="reservas.php?accion=mis_reservas" role="button">Mis reservas</a>
                <a href="reservas.php?accion=cerrar_sesion" role="button">Cerrar sesión</a>
            </nav>
        </section>
    <?php else: ?>
        <!-- Usuario no logueado -->
        <section data-type="acciones-usuario">
            <h3>Para realizar reservas debe iniciar sesión</h3>
            <nav data-type="acciones">
                <a href="reservas.php?accion=iniciar_sesion" role="button">Iniciar sesión</a>
                <a href="reservas.php?accion=registrarse" role="button">Registrarse</a>
            </nav>
        </section>
    <?php endif; ?>
</section>
