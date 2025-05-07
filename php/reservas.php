<?php
/**
 * Archivo principal de la aplicación
 * @author Alejandro Campa Martínez
 */

require_once 'BaseDatos.php';
require_once 'Entity.php';
require_once 'Usuario.php';
require_once 'Reserva.php';
require_once 'RecursoTuristico.php';
require_once 'Vista.php';
require_once 'Controladores.php';

session_start();

$bd = new BaseDatos();
$controlador = new Controlador($bd);
$controlador->ejecutar(); 