<?php

/**
 * Clase para gestionar los recursos turísticos
 * @author Alejandro Campa Martínez
 */
class RecursoTuristico {
    // Propiedades
    private int $id = 0;
    private string $nombre = '';
    private string $descripcion = '';
    private int $plazas = 0;
    private float $precio = 0.0;
    private string $fechaInicio = '';
    private string $fechaFin = '';
    private int $tipoId = 0;
    private string $tipoNombre = '';
    private BaseDatos $bd;
    
    /**
     * Constructor de la clase
     * @param BaseDatos $bd Objeto de conexión a la base de datos
     */
    public function __construct($bd) {
        $this->bd = $bd;
    }
    
    // Métodos getter y setter
    public function getNombre(): string {
        return $this->nombre;
    }
    
    public function getDescripcion(): string {
        return $this->descripcion;
    }
    
    public function getPrecio(): float {
        return $this->precio;
    }
    
    public function getFechaInicio(): string {
        return $this->fechaInicio;
    }
    
    public function getFechaFin(): string {
        return $this->fechaFin;
    }
    
    public function getTipoNombre(): string {
        return $this->tipoNombre;
    }
    
    /**
     * Método para cargar los datos de un recurso turístico por su ID
     * @param int $id ID del recurso turístico
     * @return bool Verdadero si se encontró el recurso, falso en caso contrario
     */
    public function getById(int $id): bool {
        $sql = "SELECT r.*, t.nombre as tipo_nombre 
                FROM recursos_turisticos r
                INNER JOIN tipos_recursos t ON r.tipo_id = t.id
                WHERE r.id = ?";
        $resultado = $this->bd->ejecutarConsulta($sql, [$id]);
        
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $this->id = $fila['id'];
            $this->nombre = $fila['nombre'];
            $this->descripcion = $fila['descripcion'];
            $this->plazas = $fila['plazas'];
            $this->precio = $fila['precio'];
            $this->fechaInicio = $fila['fecha_inicio'];
            $this->fechaFin = $fila['fecha_fin'];
            $this->tipoId = $fila['tipo_id'];
            $this->tipoNombre = $fila['tipo_nombre'];
            return true;
        }
        return false;
    }
    
    /**
     * Método para obtener todos los recursos turísticos
     * @param int $tipoId ID del tipo de recurso (opcional)
     * @return array Array con los recursos turísticos
     */
    public function getAll(int $tipoId = 0): array {
        $sql = "SELECT r.*, t.nombre as tipo_nombre 
                FROM recursos_turisticos r
                INNER JOIN tipos_recursos t ON r.tipo_id = t.id";
        $params = [];
        
        if ($tipoId > 0) {
            $sql .= " WHERE r.tipo_id = ?";
            $params[] = $tipoId;
        }
        
        $sql .= " ORDER BY r.nombre ASC";
        $resultado = $this->bd->ejecutarConsulta($sql, $params);
        
        $recursos = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $recursos[] = $fila;
            }
        }
        
        return $recursos;
    }
    
    /**
     * Método para obtener todos los tipos de recursos turísticos
     * @return array Array con los tipos de recursos turísticos
     */
    public function getTiposRecursos(): array {
        $sql = "SELECT * FROM tipos_recursos ORDER BY nombre ASC";
        $resultado = $this->bd->ejecutarConsulta($sql);
        
        $tipos = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $tipos[] = $fila;
            }
        }
        
        return $tipos;
    }
    
    /**
     * Método para verificar disponibilidad de plazas
     * @param int $recursoId ID del recurso turístico
     * @param int $plazasSolicitadas Número de plazas solicitadas
     * @return int Número de plazas disponibles (0 si no hay disponibles)
     */
    public function verificarDisponibilidad(int $recursoId, int $plazasSolicitadas): int {
        $sql = "SELECT plazas FROM recursos_turisticos WHERE id = ?";
        $resultado = $this->bd->ejecutarConsulta($sql, [$recursoId]);
        
        if (!$resultado || !($recurso = $resultado->fetch_assoc())) {
            return 0;
        }
        
        $plazasTotales = $recurso['plazas'];
        
        $sql = "SELECT COALESCE(SUM(numero_personas), 0) as plazas_ocupadas 
                FROM reservas 
                WHERE recurso_id = ? AND estado_id IN (1, 2)";
        $resultado = $this->bd->ejecutarConsulta($sql, [$recursoId]);
        
        $plazasOcupadas = 0;
        if ($resultado && ($reservas = $resultado->fetch_assoc())) {
            $plazasOcupadas = $reservas['plazas_ocupadas'];
        }
        
        $plazasDisponibles = $plazasTotales - $plazasOcupadas;
        return $plazasDisponibles >= $plazasSolicitadas ? $plazasDisponibles : 0;
    }
    
    private function registrar(): bool {
        $sql = "INSERT INTO recursos_turisticos (nombre, descripcion, plazas, precio, fecha_inicio, fecha_fin, tipo_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $this->nombre,
            $this->descripcion,
            $this->plazas,
            $this->precio,
            $this->fechaInicio,
            $this->fechaFin,
            $this->tipoId
        ];
        
        if ($this->bd->ejecutarConsulta($sql, $params)) {
            $this->id = $this->bd->getUltimoId();
            return true;
        }
        return false;
    }
}
?>
