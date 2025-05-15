<?php
/**
 * Database connection and query management class
 */
class BaseDatos {
    private const SERVIDOR = "localhost";
    private const USUARIO = "DBUSER2025";
    private const CONTRASENA = "DBPWD2025";
    private const NOMBRE_BD = "BD_RESERVAS_PESOZ";
    
    private ?mysqli $conn = null;
    private array $errores = [];
    
    public function __construct() {
        try {
            $this->conn = new mysqli(
                self::SERVIDOR,
                self::USUARIO,
                self::CONTRASENA,
                self::NOMBRE_BD
            );
            
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            $this->errores[] = $e->getMessage();
            throw $e;
        }
    }

    public function ejecutarConsulta(string $sql, array $params = []): mysqli_result|bool {
        try {
            if (!$this->conn) {
                throw new Exception("No hay conexión activa");
            }
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
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
            return false;
        }
    }
    
    public function getUltimoId(): int {
        return $this->conn? $this->conn->insert_id : 0;
    }
}
?>
