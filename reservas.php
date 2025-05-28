<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<!--Metadatos del documento-->
	<meta name="author" content="Alejandro Campa Martínez">
	<meta name="description" content="Sistema de reservas turísticas de Pesoz">
	<meta name="keywords" content="turismo, reservas, Pesoz, alojamiento">
	<!--Definición de la ventana gráfica - ADAPTABILIDAD-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!--Añadir el nombre del documento (o del sitio web) - Pestañas e historial de navegación-->
	<title>Reservas - Pesoz</title>
	<!-- enlaces a las hojas de estilo -->
	<link rel="stylesheet" type="text/css" href="estilo/estilo.css">
	<link rel="stylesheet" type="text/css" href="estilo/layout.css">
</head>
<body>
	<header>
		<h1>Reservas - Turismo en Pesoz</h1>
	</header>
	<nav>
		<ul>
			<li><a href="index.html">Página principal</a></li>
			<li><a href="gastronomia.html">Gastronomía</a></li>
			<li><a href="rutas.html">Rutas</a></li>
			<li><a href="meteorologia.html">Meteorología</a></li>
			<li><a href="juego.html">Juego</a></li>
			<li><a href="reservas.php" class="active">Reservas</a></li>
			<li><a href="ayuda.html">Ayuda</a></li>
		</ul>
	</nav>
	<main>
		<?php
		// Enable error reporting
		error_reporting(error_level: E_ALL);
		ini_set(option: 'display_errors', value: 1);
		
		// Incluir las clases necesarias
		require_once 'php/BaseDatos.php';
		require_once 'php/Usuario.php';
		require_once 'php/RecursoTuristico.php';
		require_once 'php/Reserva.php';
		require_once 'php/verificar_datos.php';
		
		// Iniciar sesión
		session_start();
		
		// Crear objeto de conexión a la base de datos
		$bd = new BaseDatos();
		
		// Verificar que la base de datos tiene los datos necesarios
		inicializarDatosNecesarios($bd);
		
		// Definir la acción por defecto
		$accion = isset($_GET['accion']) ? $_GET['accion'] : 'inicio';
		
		// Procesar cierre de sesión
		if ($accion === 'cerrar_sesion') {
			// Destruir la sesión
			session_destroy();
			// Redirigir al inicio
			header('Location: reservas.php');
			exit;
		}
		
		// Procesar inicio de sesión
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar_sesion'])) {
			// TODO: Show error if email or password is incorrect
			$email = isset($_POST['email']) ? $_POST['email'] : '';
			$contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
			
			$usuario = new Usuario($bd);
			if ($usuario->iniciarSesion($email, $contrasena)) {
				// Guardar datos del usuario en la sesión
				$_SESSION['usuario_id'] = $usuario->getId();
				$_SESSION['usuario_nombre'] = $usuario->getNombre();
				$_SESSION['usuario_email'] = $usuario->getEmail();
				
				// Redirigir al inicio
				header('Location: reservas.php');
				exit;
			} else {
				$error_login = 'Email o contraseña incorrectos';
			}
		}
		
		// Procesar registro de usuario
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_usuario'])) {
			$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
			$apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
			$email = isset($_POST['email']) ? $_POST['email'] : '';
			$telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
			$contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';
			$confirmar_contrasena = isset($_POST['confirmar_contrasena']) ? $_POST['confirmar_contrasena'] : '';
			
			// Validar datos
			$errores = array();
			if (empty($nombre)) {
				$errores[] = 'El nombre es obligatorio';
			}
			if (empty($apellidos)) {
				$errores[] = 'Los apellidos son obligatorios';
			}
			if (empty($email)) {
				$errores[] = 'El email es obligatorio';
			} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errores[] = 'El email no es válido';
			}
			if (empty($telefono)) {
				$errores[] = 'El teléfono es obligatorio';
			}
			if (empty($contrasena)) {
				$errores[] = 'La contraseña es obligatoria';
			}
			if ($contrasena !== $confirmar_contrasena) {
				$errores[] = 'Las contraseñas no coinciden';
			}
			
			if (empty($errores)) {
				$usuario = new Usuario($bd);
				$usuario->setNombre($nombre);
				$usuario->setApellidos($apellidos);
				$usuario->setEmail($email);
				$usuario->setTelefono($telefono);
				$usuario->setContrasena($contrasena);
				
				if ($usuario->registrar()) {
					$mensaje_registro = 'Registro completado con éxito. Ahora puede iniciar sesión.';
					// Redireccionar a la página de inicio de sesión
					header('Location: reservas.php?accion=iniciar_sesion&registro=ok');
					exit;
				} else {
					$errores[] = 'Error al registrar el usuario. Es posible que el email ya esté registrado.';
				}
			}
		}
		
		// Procesar creación de reserva
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_reserva'])) {
			// Verificar si el usuario está logueado
			if (!isset($_SESSION['usuario_id'])) {
				header('Location: reservas.php?accion=iniciar_sesion');
				exit;
			}
			
			$recurso_id = isset($_POST['recurso_id']) ? (int)$_POST['recurso_id'] : 0;
			$numero_personas = isset($_POST['numero_personas']) ? (int)$_POST['numero_personas'] : 0;
			
			// Validar datos
			$errores = array();
			if ($recurso_id <= 0) {
				$errores[] = 'Debe seleccionar un recurso turístico';
			}
			if ($numero_personas <= 0) {
				$errores[] = 'El número de personas debe ser mayor que 0';
			}
			
			if (empty($errores)) {
				// Verificar disponibilidad
				$recursoTuristico = new RecursoTuristico($bd);
				$disponibilidad = $recursoTuristico->verificarDisponibilidad($recurso_id, $numero_personas);
				
				if ($disponibilidad <= 0) {
					$errores[] = 'No hay suficientes plazas disponibles para este recurso turístico';
				} else {
					// Calcular precio total
					$reserva = new Reserva($bd);
					$precio_total = $reserva->calcularPrecioTotal($recurso_id, $numero_personas);
					
					// TODO: I don't know if I like this, maybe save in db?
					// Guardar la reserva provisional en la sesión para el presupuesto
					$_SESSION['reserva_provisional'] = array(
						'recurso_id' => $recurso_id,
						'numero_personas' => $numero_personas,
						'precio_total' => $precio_total
					);
					
					// Redirigir a la página de presupuesto
					header('Location: reservas.php?accion=presupuesto');
					exit;
				}
			}
		}
		
		// Procesar confirmación de reserva
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_reserva'])) {
			// Verificar si el usuario está logueado
			if (!isset($_SESSION['usuario_id'])) {
				header('Location: reservas.php?accion=iniciar_sesion');
				exit;
			}
			
			// Verificar si existe la reserva provisional
			if (!isset($_SESSION['reserva_provisional'])) {
				header('Location: reservas.php?accion=recursos');
				exit;
			}
			
			$reserva_provisional = $_SESSION['reserva_provisional'];
			
			// Crear la reserva
			$reserva = new Reserva($bd);
			$reserva->setUsuarioId($_SESSION['usuario_id']);
			$reserva->setRecursoId($reserva_provisional['recurso_id']);
			$reserva->setNumeroPersonas($reserva_provisional['numero_personas']);
			$reserva->setPrecioTotal($reserva_provisional['precio_total']);
			$reserva->setEstadoId(1); // Estado: Pendiente
			
			if ($reserva->guardar()) {
				// Eliminar la reserva provisional de la sesión
				unset($_SESSION['reserva_provisional']);
				
				// Redirigir a la página de confirmación
				header('Location: reservas.php?accion=confirmacion');
				exit;
			} else {
				$error_reserva = 'Error al crear la reserva';
				// Redirigir al listado de recursos
				header('Location: reservas.php?accion=recursos&error=1');
				exit;
			}
		}
		
		// Procesar cancelación de reserva
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_reserva'])) {
			// Verificar si el usuario está logueado
			if (!isset($_SESSION['usuario_id'])) {
				header('Location: reservas.php?accion=iniciar_sesion');
				exit;
			}
			
			$reserva_id = isset($_POST['reserva_id']) ? (int)$_POST['reserva_id'] : 0;
			
			if ($reserva_id > 0) {
				$reserva = new Reserva($bd);
				if ($reserva->cancelar($reserva_id, $_SESSION['usuario_id'])) {
					// Redirigir al listado de reservas
					header('Location: reservas.php?accion=mis_reservas&cancelacion=ok');
					exit;
				} else {
					// Redirigir al listado de reservas con error
					header('Location: reservas.php?accion=mis_reservas&error=1');
					exit;
				}
			}
		}
		
		// Mostrar el contenido según la acción
		switch ($accion) {
			case 'iniciar_sesion':
				// Formulario de inicio de sesión
				include 'php/iniciar_sesion.php';
				break;
				
			case 'registrarse':
				// Formulario de registro
				include 'php/registrarse.php';
				break;
				
			case 'recursos':
				// Listado de recursos turísticos
				if (isset($_SESSION['usuario_id'])) {
					include 'php/recursos_turisticos.php';
				} else {
					// Redirigir al inicio de sesión
					header('Location: reservas.php?accion=iniciar_sesion');
					exit;
				}
				break;
				
			case 'presupuesto':
				// Mostrar presupuesto
				if (isset($_SESSION['usuario_id']) && isset($_SESSION['reserva_provisional'])) {
					include 'php/presupuesto.php';
				} else {
					// Redirigir al listado de recursos
					header('Location: reservas.php?accion=recursos');
					exit;
				}
				break;
				
			case 'confirmacion':
				// Confirmar reserva
				if (isset($_SESSION['usuario_id'])) {
					include 'php/confirmacion.php';
				} else {
					// Redirigir al inicio de sesión
					header('Location: reservas.php?accion=iniciar_sesion');
					exit;
				}
				break;
				
			case 'mis_reservas':
				// Mostrar reservas del usuario
				if (isset($_SESSION['usuario_id'])) {
					include 'php/mis_reservas.php';
				} else {
					// Redirigir al inicio de sesión
					header('Location: reservas.php?accion=iniciar_sesion');
					exit;
				}
				break;
				
			case 'detalles_reserva':
				// Mostrar detalles de una reserva
				if (isset($_SESSION['usuario_id'])) {
					include 'php/detalles_reserva.php';
				} else {
					// Redirigir al inicio de sesión
					header('Location: reservas.php?accion=iniciar_sesion');
					exit;
				}
				break;
				
			default:
				// Pantalla de inicio
				include 'php/inicio_reservas.php';
				break;
		}
		?>
	</main>
	<footer>
		<p>Sistema de reservas - Turismo en Pesoz</p>
	</footer>
</body>
</html>