<?php
require_once 'Entity.php';

/**
 * Clase para gestionar los recursos turísticos
 * @author Alejandro Campa Martínez
 */
class RecursoTuristico extends Entity {
    // Propiedades
    private string $nombre = '';
    private string $descripcion = '';
    private int $plazas = 0;
    private float $precio = 0.0;
    private string $fechaInicio = '';
    private string $fechaFin = '';
    private int $tipoId = 0;
    private string $tipoNombre = '';
    
    /**
     * Constructor de la clase
     * @param BaseDatos $bd Objeto de conexión a la base de datos
     */
    public function __construct($bd) {
        parent::__construct($bd);
    }
    
    // Métodos getter y setter
    public function getNombre(): string {
        return $this->nombre;
    }
    
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }
    
    public function getDescripcion(): string {
        return $this->descripcion;
    }
    
    public function setDescripcion(string $descripcion): void {
        $this->descripcion = $descripcion;
    }
    
    public function getPlazas(): int {
        return $this->plazas;
    }
    
    public function setPlazas(int $plazas): void {
        $this->plazas = $plazas;
    }
    
    public function getPrecio(): float {
        return $this->precio;
    }
    
    public function setPrecio(float $precio): void {
        $this->precio = $precio;
    }
    
    public function getFechaInicio(): string {
        return $this->fechaInicio;
    }
    
    public function setFechaInicio(string $fechaInicio): void {
        $this->fechaInicio = $fechaInicio;
    }
    
    public function getFechaFin(): string {
        return $this->fechaFin;
    }
    
    public function setFechaFin(string $fechaFin): void {
        $this->fechaFin = $fechaFin;
    }
    
    public function getTipoId(): int {
        return $this->tipoId;
    }
    
    public function setTipoId(int $tipoId): void {
        $this->tipoId = $tipoId;
    }
    
    public function getTipoNombre(): string {
        return $this->tipoNombre;
    }
    
    /**
     * Método para cargar los datos de un recurso turístico por su ID
     * @param int $id ID del recurso turístico
     * @return bool Verdadero si se encontró el recurso, falso en caso contrario
     */
    public function cargarPorId(int $id): bool {
        $sql = "SELECT r.*, t.nombre as tipo_nombre 
                FROM recursos_turisticos r
                INNER JOIN tipos_recursos t ON r.tipo_id = t.id
                WHERE r.id = ?";
        $resultado = $this->baseDatos->ejecutarConsulta($sql, [$id]);
        
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
    public function obtenerTodos(int $tipoId = 0): array {
        $sql = "SELECT r.*, t.nombre as tipo_nombre 
                FROM recursos_turisticos r
                INNER JOIN tipos_recursos t ON r.tipo_id = t.id";
        $params = [];
        
        if ($tipoId > 0) {
            $sql .= " WHERE r.tipo_id = ?";
            $params[] = $tipoId;
        }
        
        $sql .= " ORDER BY r.nombre ASC";
        $resultado = $this->baseDatos->ejecutarConsulta($sql, $params);
        
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
    public function obtenerTiposRecursos(): array {
        $sql = "SELECT * FROM tipos_recursos ORDER BY nombre ASC";
        $resultado = $this->baseDatos->ejecutarConsulta($sql);
        
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
        $resultado = $this->baseDatos->ejecutarConsulta($sql, [$recursoId]);
        
        if (!$resultado || !($recurso = $resultado->fetch_assoc())) {
            return 0;
        }
        
        $plazasTotales = $recurso['plazas'];
        
        $sql = "SELECT COALESCE(SUM(numero_personas), 0) as plazas_ocupadas 
                FROM reservas 
                WHERE recurso_id = ? AND estado_id IN (1, 2)";
        $resultado = $this->baseDatos->ejecutarConsulta($sql, [$recursoId]);
        
        $plazasOcupadas = 0;
        if ($resultado && ($reservas = $resultado->fetch_assoc())) {
            $plazasOcupadas = $reservas['plazas_ocupadas'];
        }
        
        $plazasDisponibles = $plazasTotales - $plazasOcupadas;
        return $plazasDisponibles >= $plazasSolicitadas ? $plazasDisponibles : 0;
    }
    
    public function guardar(): bool {
        if ($this->id > 0) {
            return $this->actualizar();
        }
        return $this->registrar();
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
        
        if ($this->baseDatos->ejecutarConsulta($sql, $params)) {
            $this->id = $this->baseDatos->getUltimoId();
            return true;
        }
        return false;
    }
    
    private function actualizar(): bool {
        $sql = "UPDATE recursos_turisticos 
                SET nombre = ?, descripcion = ?, plazas = ?, precio = ?, 
                    fecha_inicio = ?, fecha_fin = ?, tipo_id = ? 
                WHERE id = ?";
        $params = [
            $this->nombre,
            $this->descripcion,
            $this->plazas,
            $this->precio,
            $this->fechaInicio,
            $this->fechaFin,
            $this->tipoId,
            $this->id
        ];
        
        return $this->baseDatos->ejecutarConsulta($sql, $params) !== null;
    }
    
    public function eliminar(): bool {
        if ($this->id <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM recursos_turisticos WHERE id = ?";
        return $this->baseDatos->ejecutarConsulta($sql, [$this->id]) !== null;
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'plazas' => $this->plazas,
            'precio' => $this->precio,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'tipo_id' => $this->tipoId,
            'tipo_nombre' => $this->tipoNombre
        ];
    }
}
?>
