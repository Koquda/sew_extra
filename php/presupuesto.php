<?php
/**
 * Sección de presupuesto
 * @author Alejandro Campa Martínez
 */

// Verificar si existe la reserva provisional
if (!isset($_SESSION['reserva_provisional'])) {
    // Redirigir al listado de recursos
    header('Location: reservas.php?accion=recursos');
    exit;
}

$reserva_provisional = $_SESSION['reserva_provisional'];

// Cargar información del recurso
$recursoTuristico = new RecursoTuristico($bd);
$recursoTuristico->getById($reserva_provisional['recurso_id']);

// Cargar información del usuario
$usuario = new Usuario($bd);
$usuario->getById($_SESSION['usuario_id']);
?>

<section>
    <h2>Presupuesto de Reserva</h2>
    
    <article>
        <h3>Datos del recurso turístico</h3>
        <p><strong>Nombre:</strong> <?php echo $recursoTuristico->getNombre(); ?></p>
        <p><strong>Tipo:</strong> <?php echo $recursoTuristico->getTipoNombre(); ?></p>
        <p><strong>Descripción:</strong> <?php echo $recursoTuristico->getDescripcion(); ?></p>
        <p><strong>Fecha de inicio:</strong> <?php echo date('d/m/Y H:i', strtotime($recursoTuristico->getFechaInicio())); ?></p>
        <p><strong>Fecha de finalización:</strong> <?php echo date('d/m/Y H:i', strtotime($recursoTuristico->getFechaFin())); ?></p>
    </article>
    
    <article>
        <h3>Datos de la reserva</h3>
        <p><strong>Número de personas:</strong> <?php echo $reserva_provisional['numero_personas']; ?></p>
        <p><strong>Precio por persona:</strong> <?php echo number_format($recursoTuristico->getPrecio(), 2); ?> €</p>
        <p><strong>Precio total:</strong> <?php echo number_format($reserva_provisional['precio_total'], 2); ?> €</p>
    </article>
    
    <article>
        <h3>Datos del usuario</h3>
        <p><strong>Nombre:</strong> <?php echo $usuario->getNombre() . ' ' . $usuario->getApellidos(); ?></p>
        <p><strong>Email:</strong> <?php echo $usuario->getEmail(); ?></p>
        <p><strong>Teléfono:</strong> <?php echo $usuario->getTelefono(); ?></p>
    </article>
    
    <form method="post" action="reservas.php">
        <input type="submit" name="confirmar_reserva" value="Confirmar Reserva">

        <input type="button" value="Cancelar" onclick="window.location.href='reservas.php?accion=recursos'">
    </form>
</section>
