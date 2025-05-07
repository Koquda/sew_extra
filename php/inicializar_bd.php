<?php
/**
 * Script para inicializar la base de datos con los datos necesarios
 * @author Alejandro Campa Martínez
 */

// Credenciales de la base de datos
$servidor = "localhost";
$usuario = "DBUSER2025";
$contrasena = "DBPWD2025";

try {
    // Conectar a MySQL sin seleccionar una base de datos
    $conexion = new mysqli($servidor, $usuario, $contrasena);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }
    
    echo "<h1>Inicialización de la Base de Datos</h1>";
    
    // Verificar si la base de datos ya existe
    $checkDbExists = $conexion->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'BD_RESERVAS_PESOZ'");
    $dbExists = ($checkDbExists && $checkDbExists->num_rows > 0);
    
    if ($dbExists) {
        echo "<p>La base de datos BD_RESERVAS_PESOZ ya existe.</p>";
        
        // Comprobar si la tabla tipos_recursos existe y tiene datos
        try {
            $conexion->select_db("BD_RESERVAS_PESOZ");
            $checkTiposRecursos = $conexion->query("SELECT COUNT(*) as count FROM tipos_recursos");
            $tiposRecursosCount = 0;
            
            if ($checkTiposRecursos) {
                $row = $checkTiposRecursos->fetch_assoc();
                $tiposRecursosCount = $row['count'];
            }
            
            if ($tiposRecursosCount > 0) {
                echo "<p>La base de datos ya contiene datos. Se omitirá la inicialización de datos.</p>";
                
                echo "<h2>¡Base de datos verificada correctamente!</h2>";
                echo "<p>La base de datos BD_RESERVAS_PESOZ ya está configurada y contiene datos.</p>";
                echo "<p><a href='../reservas.php'>Ir al sistema de reservas</a></p>";
                
                // Cerrar conexión y salir
                $conexion->close();
                exit;
            } else {
                echo "<p>La base de datos existe pero no tiene datos. Se procederá a cargar los datos iniciales.</p>";
            }
        } catch (Exception $e) {
            echo "<p>La base de datos existe pero no tiene las tablas necesarias. Se procederá a crearlas.</p>";
        }
    }
    
    // Leer y ejecutar el script SQL para crear la base de datos
    echo "<h2>Creando la estructura de la base de datos...</h2>";
    $directorio = dirname(__FILE__); // Obtener el directorio actual del script
    $sql = file_get_contents($directorio . "/crear_bd_reservas.sql");
    
    // Dividir las consultas SQL por ;
    $queries = explode(";", $sql);
    
    // Ejecutar cada consulta SQL
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (!$conexion->query($query)) {
                throw new Exception("Error al ejecutar consulta: " . $conexion->error . "<br>Consulta: " . $query);
            }
        }
    }
    
    echo "<p>Estructura de base de datos creada con éxito.</p>";
    
    // Seleccionar la base de datos
    $conexion->select_db("BD_RESERVAS_PESOZ");
    
    // Cargar datos desde los archivos CSV separados
    echo "<h2>Cargando datos iniciales desde archivos CSV separados...</h2>";
    
    // Directorio de archivos CSV
    $datosDir = $directorio . "/datos";
    if (!is_dir($datosDir)) {
        throw new Exception("El directorio de datos no existe en la ruta: " . $datosDir);
    }
    
    // Lista de archivos CSV a importar (en orden de dependencias)
    $csvFiles = [
        "tipos_recursos.csv" => "tipos_recursos",
        "estados_reserva.csv" => "estados_reserva",
        "recursos_turisticos.csv" => "recursos_turisticos"
    ];
    
    // Asegurar que las claves foráneas no causen problemas durante la importación
    $conexion->query("SET FOREIGN_KEY_CHECKS=0");
    
    // Importar cada archivo CSV
    foreach ($csvFiles as $filename => $tableName) {
        $csvPath = $datosDir . "/" . $filename;
        if (!file_exists($csvPath)) {
            throw new Exception("El archivo CSV no existe en la ruta: " . $csvPath);
        }
        
        echo "<p>Procesando archivo: " . $filename . " para tabla: " . $tableName . "</p>";
        
        // Leer el archivo CSV
        $csvFile = file($csvPath);
        
        // Variables para procesar el CSV
        $headers = [];
        $data = [];
        $firstLine = true;
        
        // Procesar cada línea del CSV
        foreach ($csvFile as $line) {
            $line = trim($line);
            // Saltar líneas vacías o comentadas
            if (empty($line) || strpos($line, "//") === 0) continue;
            
            // Eliminar comillas y dividir por comas
            $row = str_getcsv($line);
            
            if ($firstLine) {
                // Primera línea contiene los encabezados
                $headers = $row;
                $firstLine = false;
            } else {
                // Las demás líneas son datos
                $data[] = $row;
            }
        }
        
        // Insertar los datos en la tabla
        if (!empty($data) && !empty($headers)) {
            importarDatos($conexion, $tableName, $headers, $data);
        }
    }
    
    // Reactivar la verificación de claves foráneas
    $conexion->query("SET FOREIGN_KEY_CHECKS=1");
    
    echo "<p>Datos iniciales cargados con éxito.</p>";
    
    echo "<h2>¡Base de datos inicializada correctamente!</h2>";
    echo "<p>La base de datos BD_RESERVAS_PESOZ ha sido creada y configurada correctamente.</p>";
    echo "<p><a href='../reservas.php'>Ir al sistema de reservas</a></p>";
    
    // Cerrar conexión
    $conexion->close();
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Se ha producido un error durante la inicialización de la base de datos:</p>";
    echo "<p>" . $e->getMessage() . "</p>";
}

/**
 * Función para importar datos en una tabla desde un archivo CSV
 * @param mysqli $conexion Objeto de conexión a la base de datos
 * @param string $tableName Nombre de la tabla
 * @param array $headers Encabezados (nombres de columnas)
 * @param array $data Datos a insertar
 */
function importarDatos($conexion, $tableName, $headers, $data) {
    // Limpiar los nombres de columnas (eliminar el prefijo tabla_)
    $columns = [];
    foreach ($headers as $header) {
        // Buscar el prefijo de la tabla actual en el encabezado
        $prefix = $tableName . "_";
        if (strpos($header, $prefix) === 0) {
            // Eliminar el prefijo de la tabla
            $columns[] = substr($header, strlen($prefix));
        } else {
            // Si no tiene prefijo, usar el encabezado tal cual
            $columns[] = $header;
        }
    }
    
    // Verificar si hay datos para insertar
    if (count($data) === 0) {
        echo "<p>No hay datos para insertar en la tabla $tableName.</p>";
        return;
    }
    
    // Mostrar columnas para depuración
    echo "<p><small>Columnas en $tableName: " . implode(", ", $columns) . "</small></p>";
    
    // En lugar de truncar, eliminar registros existentes y verificar si la tabla ya tiene datos
    $checkQuery = "SELECT COUNT(*) as count FROM $tableName";
    $result = $conexion->query($checkQuery);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<p>La tabla $tableName ya contiene datos. No se realizarán cambios.</p>";
        return;
    }
    
    // Contador de inserciones exitosas
    $insertCount = 0;
    
    // Para cada fila de datos
    foreach ($data as $row) {
        // Preparar valores para la inserción
        $values = [];
        foreach ($row as $value) {
            // Escapar y encerrar en comillas si es necesario
            if ($value === "" || $value === NULL) {
                $values[] = "NULL";
            } else {
                $value = $conexion->real_escape_string($value);
                $values[] = "'$value'";
            }
        }
        
        // Crear la consulta SQL - usar INSERT IGNORE para evitar duplicados
        $sql = "INSERT IGNORE INTO $tableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
        
        // Mostrar consulta SQL para depuración (solo para la primera fila)
        if ($insertCount === 0) {
            echo "<p><small>Consulta SQL ejemplo: " . htmlspecialchars($sql) . "</small></p>";
        }
        
        // Ejecutar la consulta
        if ($conexion->query($sql)) {
            $insertCount++;
        } else {
            echo "<p>Error al insertar fila en $tableName: " . $conexion->error . "</p>";
            echo "<p>Consulta: " . htmlspecialchars($sql) . "</p>";
        }
    }
    
    echo "<p>Se insertaron $insertCount registros en la tabla $tableName.</p>";
}
?>
