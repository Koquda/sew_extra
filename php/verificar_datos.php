<?php
/**
 * Script para verificar y asegurar que los datos necesarios están cargados
 * Este script se llama automáticamente desde reservas.php
 * @author Alejandro Campa Martínez
 */

/**
 * Función para comprobar si la base de datos está inicializada
 * @param BaseDatos $bd Objeto de conexión a la base de datos
 * @return bool Verdadero si la base de datos está inicializada y tiene datos
 */
function comprobarBaseDatos($bd) {
    try {
        // Intentar seleccionar un registro de la tabla recursos_turisticos
        $sql = "SELECT COUNT(*) as total FROM recursos_turisticos";
        $resultado = $bd->ejecutarConsulta($sql);
        
        if (!$resultado) {
            return false;
        }
        
        $fila = $resultado->fetch_assoc();
        return ($fila['total'] > 0);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Función para inicializar la base de datos si no está inicializada
 * @param BaseDatos $bd Objeto de conexión a la base de datos
 * @return void
 */
function inicializarDatosNecesarios($bd) {
    if (!comprobarBaseDatos($bd)) {
        // Redirigir al script de inicialización
        header('Location: php/inicializar_bd.php');
        exit;
    }
}
?>
