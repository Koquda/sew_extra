<?php
/**
 * Sección de detalles de reserva
 * @author Alejandro Campa Martínez
 */

// Obtener el ID de la reserva
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verificar que la reserva exista y pertenezca al usuario
$reserva = new Reserva($bd);
if (!$reserva->cargarPorId($reserva_id) || $reserva->getUsuarioId() != $_SESSION['usuario_id']) {
    // Redirigir al listado de reservas
    header('Location: reservas.php?accion=mis_reservas');
    exit;
}

// Cargar el recurso turístico
$recursoTuristico = new RecursoTuristico($bd);
$recursoTuristico->cargarPorId($reserva->getRecursoId());
?>

<section data-page="detalles-reserva">
    <h2>Detalles de la Reserva</h2>
    
    <article data-type="detalles">
        <section data-info="reserva">
            <h3>Información de la Reserva</h3>
            <p><strong>ID de Reserva:</strong> <?php echo $reserva->getId(); ?></p>
            <p><strong>Fecha de Reserva:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva->getFechaReserva())); ?></p>
            <p><strong>Estado:</strong> <?php echo $reserva->getEstadoNombre(); ?></p>
            <p><strong>Número de Personas:</strong> <?php echo $reserva->getNumeroPersonas(); ?></p>
            <p><strong>Precio Total:</strong> <?php echo number_format($reserva->getPrecioTotal(), 2); ?> €</p>
        </section>
        
        <section data-info="recurso">
            <h3>Información del Recurso Turístico</h3>
            <p><strong>Nombre:</strong> <?php echo $reserva->getNombreRecurso(); ?></p>
            <p><strong>Tipo:</strong> <?php echo $reserva->getTipoRecurso(); ?></p>
            <p><strong>Descripción:</strong> <?php echo $recursoTuristico->getDescripcion(); ?></p>
            <p><strong>Fecha de Inicio:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva->getFechaInicioRecurso())); ?></p>
            <p><strong>Fecha de Finalización:</strong> <?php echo date('d/m/Y H:i', strtotime($reserva->getFechaFinRecurso())); ?></p>
        </section>
    </article>
    
    <footer data-type="acciones">
        <?php if ($reserva->getEstadoId() == 1 || $reserva->getEstadoId() == 2): // Pendiente o Confirmada ?>
            <form method="post" action="reservas.php" onsubmit="return confirm('¿Está seguro de que desea cancelar esta reserva?');">
                <input type="hidden" name="reserva_id" value="<?php echo $reserva->getId(); ?>">
                <button type="submit" name="cancelar_reserva">Cancelar Reserva</button>
            </form>
        <?php endif; ?>
        
        <a href="reservas.php?accion=mis_reservas" role="button">Volver a Mis Reservas</a>
    </footer>
</section>
