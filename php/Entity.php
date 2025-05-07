<?php
/**
 * Base class for all entities
 */
abstract class Entity {
    protected $id;
    protected $baseDatos;
    
    public function __construct(BaseDatos $bd) {
        $this->baseDatos = $bd;
        $this->id = 0;
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function setId(int $id): void {
        $this->id = $id;
    }
    
    /**
     * Load entity data from database
     */
    abstract public function cargarPorId(int $id): bool;
    
    /**
     * Save entity data to database
     */
    abstract public function guardar(): bool;
    
    /**
     * Delete entity from database
     */
    abstract public function eliminar(): bool;
    
    /**
     * Convert entity to array
     */
    abstract public function toArray(): array;
} 