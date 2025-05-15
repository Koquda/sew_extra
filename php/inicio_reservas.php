<?php
/**
 * Sección de inicio de reservas
 * @author Alejandro Campa Martínez
 */
?>

<section>
    <h2>Sistema de Reservas Turísticas de Pesoz</h2>
    
    <p>Bienvenido al sistema de reservas turísticas de Pesoz. Aquí podrá reservar diferentes recursos turísticos de la zona:</p>
    <ul>
        <li>Alojamientos (hoteles, casas rurales, apartamentos)</li>
        <li>Restaurantes</li>
        <li>Museos</li>
        <li>Rutas guiadas</li>
        <li>Actividades deportivas</li>
    </ul>
    
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <!-- Usuario logueado -->
        <h3>¿Qué desea hacer?</h3>
        <input type="button" value="Ver recursos turísticos" onclick="window.location.href='reservas.php?accion=recursos'">
        <input type="button" value="Mis reservas" onclick="window.location.href='reservas.php?accion=mis_reservas'">
        <input type="button" value="Cerrar sesión" onclick="window.location.href='reservas.php?accion=cerrar_sesion'">
    <?php else: ?>
        <!-- Usuario no logueado -->
        <h3>Para realizar reservas debe iniciar sesión</h3>
        <input type="button" value="Iniciar sesión" onclick="window.location.href='reservas.php?accion=iniciar_sesion'">
        <input type="button" value="Registrarse" onclick="window.location.href='reservas.php?accion=registrarse'">
    <?php endif; ?>
</section>
