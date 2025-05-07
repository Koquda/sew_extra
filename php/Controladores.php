<?php
/**
 * Controladores unificados para la aplicación
 * @author Alejandro Campa Martínez
 */

class Controlador {
    protected $vista;
    protected $bd;
    protected $usuario;
    protected $reserva;
    protected $recursoTuristico;

    public function __construct(BaseDatos $bd) {
        $this->bd = $bd;
        $this->vista = new Vista();
        $this->usuario = new Usuario($bd);
        $this->reserva = new Reserva($bd);
        $this->recursoTuristico = new RecursoTuristico($bd);
    }

    public function ejecutar(): void {
        $accion = $this->obtenerParametro('accion', 'inicio');
        
        switch ($accion) {
            case 'iniciar_sesion':
            case 'registrarse':
            case 'cerrar_sesion':
                $this->manejarAutenticacion($accion);
                break;
            
            case 'mis_reservas':
            case 'detalles_reserva':
                $this->manejarReservas($accion);
                break;
            
            case 'recursos':
                $this->manejarRecursos();
                break;
            
            default:
                $this->vista->renderizar('inicio_reservas');
        }
    }

    private function manejarAutenticacion(string $accion): void {
        if ($this->esPost()) {
            if ($accion === 'iniciar_sesion') {
                $this->iniciarSesion();
            } elseif ($accion === 'registrarse') {
                $this->registrar();
            } elseif ($accion === 'cerrar_sesion') {
                $this->cerrarSesion();
            }
        } else {
            $this->vista->renderizar($accion);
        }
    }

    private function manejarReservas(string $accion): void {
        $this->requerirSesion();
        
        if ($accion === 'mis_reservas') {
            $reservasUsuario = $this->reserva->obtenerReservasUsuario($_SESSION['usuario_id']);
            
            if ($this->obtenerParametro('cancelacion') === 'ok') {
                $this->vista->asignarMensaje('La reserva ha sido cancelada correctamente.');
            }
            
            if ($this->obtenerParametro('error') === '1') {
                $this->vista->asignarError('Ha ocurrido un error al procesar la solicitud.');
            }

            $this->vista->asignarDatos(['reservasUsuario' => $reservasUsuario]);
            $this->vista->renderizar('mis_reservas');
        } else {
            $reservaId = (int)$this->obtenerParametro('id');
            if ($reservaId > 0) {
                $reserva = $this->reserva->cargarPorId($reservaId);
                $this->vista->asignarDatos(['reserva' => $reserva]);
                $this->vista->renderizar('detalles_reserva');
            }
        }
    }

    private function manejarRecursos(): void {
        $tipoSeleccionado = (int)$this->obtenerParametro('tipo_id', 0);
        
        $datos = [
            'tiposRecurso' => $this->recursoTuristico->obtenerTiposRecursos(),
            'recursos' => $this->recursoTuristico->obtenerTodos($tipoSeleccionado),
            'tipoSeleccionado' => $tipoSeleccionado
        ];

        if ($this->obtenerParametro('error') === '1') {
            $this->vista->asignarError('Ha ocurrido un error al procesar la reserva.');
        }

        $this->vista->asignarDatos($datos);
        $this->vista->renderizar('recursos');
    }

    private function iniciarSesion(): void {
        $email = $this->obtenerParametro('email');
        $contrasena = $this->obtenerParametro('contrasena');

        if ($this->usuario->iniciarSesion($email, $contrasena)) {
            $this->redirigir('reservas.php');
        } else {
            $this->vista->asignarError('Credenciales inválidas');
            $this->vista->renderizar('iniciar_sesion');
        }
    }

    private function registrar(): void {
        $datos = [
            'nombre' => $this->obtenerParametro('nombre'),
            'apellidos' => $this->obtenerParametro('apellidos'),
            'email' => $this->obtenerParametro('email'),
            'contrasena' => $this->obtenerParametro('contrasena')
        ];

        if ($this->usuario->registrar($datos)) {
            $this->redirigir('reservas.php?accion=iniciar_sesion');
        } else {
            $this->vista->asignarError('Error al registrar el usuario');
            $this->vista->asignarDatos($datos);
            $this->vista->renderizar('registrarse');
        }
    }

    private function cerrarSesion(): void {
        session_destroy();
        $this->redirigir('reservas.php');
    }

    protected function redirigir(string $url): void {
        header("Location: $url");
        exit;
    }

    protected function obtenerParametro(string $nombre, $valorPorDefecto = null) {
        return $_REQUEST[$nombre] ?? $valorPorDefecto;
    }

    protected function esPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function esGet(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function validarSesion(): bool {
        return isset($_SESSION['usuario_id']);
    }

    protected function requerirSesion(): void {
        if (!$this->validarSesion()) {
            $this->redirigir('reservas.php?accion=iniciar_sesion');
        }
    }
} 