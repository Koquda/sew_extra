<?php
/**
 * Sección de recursos turísticos
 * @author Alejandro Campa Martínez
 */

// Crear objeto de recursos turísticos
$recursoTuristico = new RecursoTuristico($bd);

// Obtener todos los tipos de recursos
$tiposRecurso = $recursoTuristico->getTiposRecursos();

// Filtrar por tipo de recurso si se ha seleccionado uno
$tipoSeleccionado = isset($_GET['tipo_id']) ? (int)$_GET['tipo_id'] : 0;
$recursos = $recursoTuristico->getAll($tipoSeleccionado);

// Verificar si hay un mensaje de error
$error = isset($_GET['error']) && $_GET['error'] === '1' ? 'Ha ocurrido un error al procesar la reserva. Inténtelo de nuevo.' : '';
?>

<section>
    <h2>Recursos Turísticos Disponibles</h2>
    
    <?php if (!empty($error)): ?>
        <aside role="alert">
            <?php echo $error; ?>
        </aside>
    <?php endif; ?>
    
    <form method="get" action="reservas.php">
        <input type="hidden" name="accion" value="recursos">
        <fieldset>
            <label for="tipo">Filtrar por tipo:</label>
            <select id="tipo" name="tipo_id" onchange="this.form.submit()">
                <option value="0">Todos los tipos</option>
                <?php foreach ($tiposRecurso as $tipo): ?>
                    <option value="<?php echo $tipo['id']; ?>" <?php echo $tipoSeleccionado == $tipo['id'] ? 'selected' : ''; ?>>
                        <?php echo $tipo['nombre']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </fieldset>
    </form>
    
    <?php if (count($recursos) > 0): ?>
        <?php foreach ($recursos as $recurso): ?>
            <article>
                <h3><?php echo $recurso['nombre']; ?></h3>
                <p><strong>Tipo:</strong> <?php echo $recurso['tipo_nombre']; ?></p>
                <p><?php echo $recurso['descripcion']; ?></p>
                <p><strong>Plazas disponibles:</strong> <?php echo $recurso['plazas']; ?></p>
                <p><strong>Precio:</strong> <?php echo number_format($recurso['precio'], 2); ?> €</p>
                <p><strong>Fecha de inicio:</strong> <?php echo date('d/m/Y H:i', strtotime($recurso['fecha_inicio'])); ?></p>
                <p><strong>Fecha de finalización:</strong> <?php echo date('d/m/Y H:i', strtotime($recurso['fecha_fin'])); ?></p>
                
                <form method="post" action="reservas.php">
                    <input type="hidden" name="recurso_id" value="<?php echo $recurso['id']; ?>">
                    <fieldset>
                        <label for="personas">Número de personas:</label>
                        <input type="number" id="personas" name="numero_personas" min="1" max="<?php echo $recurso['plazas']; ?>" value="1" required>
                    </fieldset>
                    <fieldset>
                        <input type="submit" name="crear_reserva" value="Reservar"/>
                    </fieldset>
                </form>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No se encontraron recursos turísticos disponibles.</p>
    <?php endif; ?>
    
    <nav>
        <input type="button" value="Volver al inicio" onclick="window.location.href='reservas.php'">
    </nav>
</section>
