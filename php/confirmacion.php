<?php
/**
 * Sección de confirmación de reserva
 * @author Alejandro Campa Martínez
 */
?>

<section data-page="confirmacion">
    <h2>¡Reserva Confirmada!</h2>
    
    <article data-type="mensaje">
        <p>Su reserva se ha registrado correctamente en nuestro sistema.</p>
        <p>Puede consultar los detalles de su reserva en la sección "Mis Reservas".</p>
    </article>
    
    <nav data-type="links">
        <a href="reservas.php?accion=mis_reservas" role="button">Ver mis reservas</a>
        <a href="reservas.php?accion=recursos" role="button">Realizar otra reserva</a>
        <a href="reservas.php" role="button">Volver al inicio</a>
    </nav>
</section>
