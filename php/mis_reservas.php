<?php
/**
 * Sección de mis reservas
 * @author Alejandro Campa Martínez
 */

// Cargar las reservas del usuario
$reserva = new Reserva($bd);
$reservasUsuario = $reserva->obtenerReservasUsuario($_SESSION['usuario_id']);

// Verificar si hay mensajes
$mensaje_cancelacion = isset($_GET['cancelacion']) && $_GET['cancelacion'] === 'ok' ? 'La reserva ha sido cancelada correctamente.' : '';
$error = isset($_GET['error']) && $_GET['error'] === '1' ? 'Ha ocurrido un error al procesar la solicitud. Inténtelo de nuevo.' : '';
?>

<section>
    <h2>Mis Reservas</h2>
    
    <?php if (!empty($mensaje_cancelacion)): ?>
        <aside role="status">
            <?php echo $mensaje_cancelacion; ?>
        </aside>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <aside role="alert">
            <?php echo $error; ?>
        </aside>
    <?php endif; ?>
    
    <?php if (count($reservasUsuario) > 0): ?>
        <?php foreach ($reservasUsuario as $res): ?>
            <article>
                <h3>Reserva #<?php echo $res['id']; ?></h3>
                <p>Estado: <?php echo $res['estado_nombre']; ?></p>
                <p>Recurso: <?php echo $res['nombre_recurso']; ?></p>
                <p>Tipo: <?php echo $res['tipo_recurso']; ?></p>
                <p>Fecha de reserva: <?php echo date('d/m/Y H:i', strtotime($res['fecha_reserva'])); ?></p>
                <p>Número de personas: <?php echo $res['numero_personas']; ?></p>
                <p>Precio total: <?php echo number_format($res['precio_total'], 2); ?> €</p>
                <?php if ($res['estado_id'] == 1 || $res['estado_id'] == 2): // Pendiente o Confirmada ?>
                    <form method="post" action="reservas.php" onsubmit="return confirm('¿Está seguro de que desea cancelar esta reserva?');">
                        <input type="hidden" name="reserva_id" value="<?php echo $res['id']; ?>">
                        <input type="submit" name="cancelar_reserva" value="Cancelar">
                    </form>
                <?php endif; ?>
                <input type="button" value="Ver detalles" onclick="window.location.href='reservas.php?accion=detalles_reserva&id=<?php echo $res['id']; ?>'">
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No tiene reservas registradas.</p>
    <?php endif; ?>
    
    <nav>
        <input type="button" value="Realizar una reserva" onclick="window.location.href='reservas.php?accion=recursos'">
        <input type="button" value="Volver al inicio" onclick="window.location.href='reservas.php'">
    </nav>
</section>
