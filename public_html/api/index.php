<?php
/*
 * Script en php básico para dar una respuesta de ejemplo para la App de
 * comedores de la USC
 *
 * Autor: David Campos R.
 * Fecha: 04/02/2016
 */

include_once dirname(__FILE__).'/../../includes/api-core.php';
header('Content-Type: application/json;charset=utf-8');
 
// Encontramos la consulta
$tipoConsulta = isset($_GET['tipo'])?$_GET['tipo']:COMEDORES;

// Obtenemos las lineas de la base
$lineas = obtenerLineas($tipoConsulta,$_GET);

//La imprimimos
$json = json_encode($lineas);
if ($json === false ) {
	// Evita el echo de una cadena vacía (que es inválido como JSON)
	$json = json_encode( array("jsonError", json_last_error_msg()) );
	if( $json === false ) {
		$json = "{'jsonError': 'unknown'}"; // Como caso extremo
	}
	http_response_code(500);
}
echo $json."<!--FIN-->\n\n";

//Cerramos la conexión
$mysqli->close();
