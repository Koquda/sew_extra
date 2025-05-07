<?php
/**
 * Database connection and query management class
 */
class BaseDatos {
    private const SERVIDOR = "localhost";
    private const USUARIO = "DBUSER2025";
    private const CONTRASENA = "DBPWD2025";
    private const NOMBRE_BD = "BD_RESERVAS_PESOZ";
    
    private ?mysqli $conexion = null;
    private array $errores = [];
    
    public function __construct() {
        try {
            $this->conexion = new mysqli(
                self::SERVIDOR,
                self::USUARIO,
                self::CONTRASENA,
                self::NOMBRE_BD
            );
            
            if ($this->conexion->connect_error) {
                throw new Exception("Error de conexión: " . $this->conexion->connect_error);
            }
            
            $this->conexion->set_charset("utf8mb4");
        } catch (Exception $e) {
            $this->errores[] = $e->getMessage();
            throw $e;
        }
    }
    
    public function getConexion(): ?mysqli {
        return $this->conexion;
    }
    
    public function cerrarConexion(): void {
        if ($this->conexion) {
            $this->conexion->close();
            $this->conexion = null;
        }
    }
    
    public function ejecutarConsulta(string $sql, array $params = []): ?mysqli_result {
        try {
            if (!$this->conexion) {
                throw new Exception("No hay conexión activa");
            }
            
            $stmt = $this->conexion->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
            }
            
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            $this->errores[] = $e->getMessage();
            return null;
        }
    }
    
    public function ejecutarTransaccion(array $queries): bool {
        try {
            if (!$this->conexion) {
                throw new Exception("No hay conexión activa");
            }
            
            $this->conexion->begin_transaction();
            
            foreach ($queries as $query) {
                $stmt = $this->conexion->prepare($query['sql']);
                if (!$stmt) {
                    throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
                }
                
                if (!empty($query['params'])) {
                    $types = str_repeat('s', count($query['params']));
                    $stmt->bind_param($types, ...$query['params']);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
                }
                
                $stmt->close();
            }
            
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            $this->errores[] = $e->getMessage();
            return false;
        }
    }
    
    public function getUltimoId(): int {
        return $this->conexion ? $this->conexion->insert_id : 0;
    }
    
    public function getErrores(): array {
        return $this->errores;
    }
    
    public function __destruct() {
        $this->cerrarConexion();
    }
}
?>
