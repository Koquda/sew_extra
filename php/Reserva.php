<?php

/**
 * Clase para gestionar las reservas
 * @author Alejandro Campa Martínez
 */
class Reserva {
    // Propiedades
    private int $id = 0;
    private int $usuarioId = 0;
    private int $recursoId = 0;
    private string $fechaReserva = '';
    private int $numeroPersonas = 0;
    private float $precioTotal = 0.0;
    private int $estadoId = 0;
    
    // Propiedades adicionales para mostrar información completa
    private string $nombreRecurso = '';
    private string $tipoRecurso = '';
    private string $fechaInicioRecurso = '';
    private string $fechaFinRecurso = '';
    private string $estadoNombre = '';
    
    private BaseDatos $bd;

    /**
     * Constructor de la clase
     * @param BaseDatos $bd Objeto de conexión a la base de datos
     */
    public function __construct($bd) {
        $this->bd = $bd;
        $this->nombreRecurso = "";
        $this->tipoRecurso = "";
        $this->fechaInicioRecurso = "";
        $this->fechaFinRecurso = "";
        $this->estadoNombre = "";
    }
    
    // Métodos getter y setter

    public function getId(): int {
        return $this->id;
    }

    public function getUsuarioId(): int {
        return $this->usuarioId;
    }
    
    public function setUsuarioId(int $usuarioId): void {
        $this->usuarioId = $usuarioId;
    }
    
    public function getRecursoId(): int {
        return $this->recursoId;
    }
    
    public function setRecursoId(int $recursoId): void {
        $this->recursoId = $recursoId;
    }
    
    public function getFechaReserva(): string {
        return $this->fechaReserva;
    }
    
    public function getNumeroPersonas(): int {
        return $this->numeroPersonas;
    }
    
    public function setNumeroPersonas(int $numeroPersonas): void {
        $this->numeroPersonas = $numeroPersonas;
    }
    
    public function getPrecioTotal(): float {
        return $this->precioTotal;
    }
    
    public function setPrecioTotal(float $precioTotal): void {
        $this->precioTotal = $precioTotal;
    }
    
    public function getEstadoId(): int {
        return $this->estadoId;
    }
    
    public function setEstadoId(int $estadoId): void {
        $this->estadoId = $estadoId;
    }
    
    public function getNombreRecurso(): string {
        return $this->nombreRecurso;
    }
    
    public function getTipoRecurso(): string {
        return $this->tipoRecurso;
    }
    
    public function getFechaInicioRecurso(): string {
        return $this->fechaInicioRecurso;
    }
    
    public function getFechaFinRecurso(): string {
        return $this->fechaFinRecurso;
    }
    
    public function getEstadoNombre(): string {
        return $this->estadoNombre;
    }
    
    /**
     * Método para crear una nueva reserva
     * @return bool Verdadero si la reserva fue exitosa, falso en caso contrario
     */
    public function guardar(): bool {
        if ($this->id > 0) {
            return $this->actualizar();
        }
        return $this->crear();
    }
    
    private function crear(): bool {
        if ($this->usuarioId <= 0 || $this->recursoId <= 0 || $this->numeroPersonas <= 0 || $this->precioTotal <= 0) {
            return false;
        }
        
        $sql = "INSERT INTO reservas (usuario_id, recurso_id, numero_personas, precio_total, estado_id) 
                VALUES (?, ?, ?, ?, ?)";
        $params = [
            $this->usuarioId,
            $this->recursoId,
            $this->numeroPersonas,
            $this->precioTotal,
            $this->estadoId
        ];
        
        if ($this->bd->ejecutarConsulta($sql, $params)) {
            $this->id = $this->bd->getUltimoId();
            return true;
        }
        return false;
    }
    
    private function actualizar(): bool {
        $sql = "UPDATE reservas 
                SET usuario_id = ?, recurso_id = ?, numero_personas = ?, 
                    precio_total = ?, estado_id = ? 
                WHERE id = ?";
        $params = [
            $this->usuarioId,
            $this->recursoId,
            $this->numeroPersonas,
            $this->precioTotal,
            $this->estadoId,
            $this->id
        ];
        
        return $this->bd->ejecutarConsulta($sql, $params) !== null;
    }
    
    /**
     * Método para calcular el precio total de una reserva
     * @param int $recursoId ID del recurso turístico
     * @param int $numeroPersonas Número de personas
     * @return float Precio total de la reserva
     */
    public function calcularPrecioTotal(int $recursoId, int $numeroPersonas): float {
        $sql = "SELECT precio FROM recursos_turisticos WHERE id = ?";
        $resultado = $this->bd->ejecutarConsulta($sql, [$recursoId]);
        
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            return $fila['precio'] * $numeroPersonas;
        }
        return 0.0;
    }
    
    /**
     * Método para obtener todas las reservas de un usuario
     * @param int $usuarioId ID del usuario
     * @return array Array con las reservas del usuario
     */
    public function obtenerReservasUsuario(int $usuarioId): array {
        $sql = "SELECT r.*, rt.nombre as nombre_recurso, 
                tr.nombre as tipo_recurso, 
                rt.fecha_inicio as fecha_inicio_recurso, 
                rt.fecha_fin as fecha_fin_recurso,
                er.nombre as estado_nombre
                FROM reservas r
                INNER JOIN recursos_turisticos rt ON r.recurso_id = rt.id
                INNER JOIN tipos_recursos tr ON rt.tipo_id = tr.id
                INNER JOIN estados_reserva er ON r.estado_id = er.id
                WHERE r.usuario_id = ?
                ORDER BY r.fecha_reserva DESC";
        $resultado = $this->bd->ejecutarConsulta($sql, [$usuarioId]);
        
        $reservas = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $reservas[] = $fila;
            }
        }
        
        return $reservas;
    }
    
    /**
     * Método para cargar los datos de una reserva por su ID
     * @param int $id ID de la reserva
     * @return bool Verdadero si se encontró la reserva, falso en caso contrario
     */
    public function cargarPorId(int $id): bool {
        $sql = "SELECT r.*, rt.nombre as nombre_recurso, 
                tr.nombre as tipo_recurso, 
                rt.fecha_inicio as fecha_inicio_recurso, 
                rt.fecha_fin as fecha_fin_recurso,
                er.nombre as estado_nombre
                FROM reservas r
                INNER JOIN recursos_turisticos rt ON r.recurso_id = rt.id
                INNER JOIN tipos_recursos tr ON rt.tipo_id = tr.id
                INNER JOIN estados_reserva er ON r.estado_id = er.id
                WHERE r.id = ?";
        $resultado = $this->bd->ejecutarConsulta($sql, [$id]);
        
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $this->id = $fila['id'];
            $this->usuarioId = $fila['usuario_id'];
            $this->recursoId = $fila['recurso_id'];
            $this->fechaReserva = $fila['fecha_reserva'];
            $this->numeroPersonas = $fila['numero_personas'];
            $this->precioTotal = $fila['precio_total'];
            $this->estadoId = $fila['estado_id'];
            $this->nombreRecurso = $fila['nombre_recurso'];
            $this->tipoRecurso = $fila['tipo_recurso'];
            $this->fechaInicioRecurso = $fila['fecha_inicio_recurso'];
            $this->fechaFinRecurso = $fila['fecha_fin_recurso'];
            $this->estadoNombre = $fila['estado_nombre'];
            return true;
        }
        return false;
    }
    
    /**
     * Método para cancelar una reserva
     * @param int $id ID de la reserva
     * @param int $usuarioId ID del usuario (para verificar que sea el dueño de la reserva)
     * @return bool Verdadero si la cancelación fue exitosa, falso en caso contrario
     */
    public function cancelar(int $id, int $usuarioId): bool {
        $sql = "UPDATE reservas 
                SET estado_id = (SELECT id FROM estados_reserva WHERE nombre = 'Cancelada')
                WHERE id = ? AND usuario_id = ?";
        return $this->bd->ejecutarConsulta($sql, [$id, $usuarioId]) !== null;
    }
}
?>
