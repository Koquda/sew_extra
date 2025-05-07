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

<section data-page="mis-reservas">
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
        <section data-type="tabla-reservas">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recurso</th>
                        <th>Tipo</th>
                        <th>Fecha de reserva</th>
                        <th>Personas</th>
                        <th>Precio total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservasUsuario as $res): ?>
                        <tr>
                            <td><?php echo $res['id']; ?></td>
                            <td><?php echo $res['nombre_recurso']; ?></td>
                            <td><?php echo $res['tipo_recurso']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($res['fecha_reserva'])); ?></td>
                            <td><?php echo $res['numero_personas']; ?></td>
                            <td><?php echo number_format($res['precio_total'], 2); ?> €</td>
                            <td><?php echo $res['estado_nombre']; ?></td>
                            <td>
                                <?php if ($res['estado_id'] == 1 || $res['estado_id'] == 2): // Pendiente o Confirmada ?>
                                    <form method="post" action="reservas.php" onsubmit="return confirm('¿Está seguro de que desea cancelar esta reserva?');">
                                        <input type="hidden" name="reserva_id" value="<?php echo $res['id']; ?>">
                                        <button type="submit" name="cancelar_reserva">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                                <a href="reservas.php?accion=detalles_reserva&id=<?php echo $res['id']; ?>" role="button" data-action="detalles">Detalles</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php else: ?>
        <p>No tiene reservas registradas.</p>
    <?php endif; ?>
    
    <nav data-type="links">
        <a href="reservas.php?accion=recursos" role="button">Realizar una reserva</a>
        <a href="reservas.php" role="button">Volver al inicio</a>
    </nav>
</section>
