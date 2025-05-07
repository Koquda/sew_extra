<?php
/**
 * Clase para manejar las vistas
 * @author Alejandro Campa Martínez
 */
class Vista {
    private array $datos = [];
    private array $mensajes = [];
    private array $errores = [];

    public function asignarDatos(array $datos): void {
        $this->datos = array_merge($this->datos, $datos);
    }

    public function asignarMensaje(string $mensaje): void {
        $this->mensajes[] = $mensaje;
    }

    public function asignarError(string $error): void {
        $this->errores[] = $error;
    }

    public function renderizar(string $vista): void {
        extract($this->datos);
        
        if (!empty($this->mensajes)) {
            $mensajes = $this->mensajes;
        }
        
        if (!empty($this->errores)) {
            $errores = $this->errores;
        }
        
        require_once "vistas/{$vista}.php";
    }

    public function tieneMensajes(): bool {
        return !empty($this->mensajes);
    }

    public function tieneErrores(): bool {
        return !empty($this->errores);
    }

    public function obtenerMensajes(): array {
        return $this->mensajes;
    }

    public function obtenerErrores(): array {
        return $this->errores;
    }
} 