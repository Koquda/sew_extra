<?php
require_once 'Entity.php';

/**
 * Clase para gestionar los usuarios
 * @author Alejandro Campa Martínez
 */
class Usuario extends Entity {
    // Propiedades
    private string $nombre = '';
    private string $apellidos = '';
    private string $email = '';
    private string $telefono = '';
    private string $contrasena = '';
    private string $fechaRegistro = '';
    
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
    
    public function getApellidos(): string {
        return $this->apellidos;
    }
    
    public function setApellidos(string $apellidos): void {
        $this->apellidos = $apellidos;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function setEmail(string $email): void {
        $this->email = $email;
    }
    
    public function getTelefono(): string {
        return $this->telefono;
    }
    
    public function setTelefono(string $telefono): void {
        $this->telefono = $telefono;
    }
    
    public function setContrasena(string $contrasena): void {
        $this->contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
    }
    
    public function getFechaRegistro(): string {
        return $this->fechaRegistro;
    }
    
    /**
     * Método para registrar un nuevo usuario
     * @return bool Verdadero si el registro fue exitoso, falso en caso contrario
     */
    public function registrar(): bool {
        try {
            // Verificar si el email ya existe
            $sql = "SELECT id FROM usuarios WHERE email = ?";
            $resultado = $this->baseDatos->ejecutarConsulta($sql, [$this->email]);
            
            if ($resultado && $resultado->num_rows > 0) {
                return false; // El email ya existe
            }
            
            // Insertar el usuario
            $sql = "INSERT INTO usuarios (nombre, apellidos, email, telefono, contrasena) 
                    VALUES (?, ?, ?, ?, ?)";
            $params = [
                $this->nombre,
                $this->apellidos,
                $this->email,
                $this->telefono,
                $this->contrasena
            ];
            
            if ($this->baseDatos->ejecutarConsulta($sql, $params)) {
                $this->id = $this->baseDatos->getUltimoId();
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Método para iniciar sesión
     * @param string $email Email del usuario
     * @param string $contrasena Contraseña del usuario
     * @return bool Verdadero si las credenciales son correctas, falso en caso contrario
     */
    public function iniciarSesion(string $email, string $contrasena): bool {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $resultado = $this->baseDatos->ejecutarConsulta($sql, [$email]);
        
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            if (password_verify($contrasena, $fila['contrasena'])) {
                $this->id = $fila['id'];
                $this->nombre = $fila['nombre'];
                $this->apellidos = $fila['apellidos'];
                $this->email = $fila['email'];
                $this->telefono = $fila['telefono'];
                $this->contrasena = $fila['contrasena'];
                $this->fechaRegistro = $fila['fecha_registro'];
                return true;
            }
        }
        return false;
    }
    
    /**
     * Método para cargar los datos de un usuario por su ID
     * @param int $id ID del usuario
     * @return bool Verdadero si se encontró el usuario, falso en caso contrario
     */
    public function cargarPorId(int $id): bool {
        try {
            $sql = "SELECT * FROM usuarios WHERE id = ?";
            $resultado = $this->baseDatos->ejecutarConsulta($sql, [$id]);
            
            if ($resultado && $fila = $resultado->fetch_assoc()) {
                $this->id = $fila['id'];
                $this->nombre = $fila['nombre'];
                $this->apellidos = $fila['apellidos'];
                $this->email = $fila['email'];
                $this->telefono = $fila['telefono'];
                $this->contrasena = $fila['contrasena'];
                $this->fechaRegistro = $fila['fecha_registro'];
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Método para actualizar los datos del usuario
     * @return bool Verdadero si la actualización fue exitosa, falso en caso contrario
     */
    private function actualizar(): bool {
        if ($this->id <= 0) {
            return false;
        }
        
        $sql = "UPDATE usuarios 
                SET nombre = ?, apellidos = ?, email = ?, telefono = ? 
                WHERE id = ?";
        $params = [
            $this->nombre,
            $this->apellidos,
            $this->email,
            $this->telefono,
            $this->id
        ];
        
        return $this->baseDatos->ejecutarConsulta($sql, $params) !== null;
    }
    
    public function guardar(): bool {
        if ($this->id > 0) {
            return $this->actualizar();
        }
        return $this->registrar();
    }
    
    public function eliminar(): bool {
        if ($this->id <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM usuarios WHERE id = ?";
        return $this->baseDatos->ejecutarConsulta($sql, [$this->id]) !== null;
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'fecha_registro' => $this->fechaRegistro
        ];
    }
    
    public static function buscarPorEmail(BaseDatos $bd, string $email): ?Usuario {
        $usuario = new Usuario($bd);
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $resultado = $bd->ejecutarConsulta($sql, [$email]);
        
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $usuario->cargarPorId($fila['id']);
            return $usuario;
        }
        return null;
    }
}
?>
