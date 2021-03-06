<?php
/**
 * Recibe el id de un comedor y devuelve su imagen, o un error 404 si no la posee.
 *
 * @author David Campos Rodríguez
 */

require_once(dirname(__FILE__).'/../../../includes/api-core.php');

if ( isset($_GET['id']) ) {
	$id = $_GET['id'];
	// Comprobamos que el id es numérico
	if( preg_match("/[0-9]+/", $id) ) {
		$file = "detail_$id.png";
        if (devolverImagen($file, isset($_GET['ancho']) ? $_GET['ancho'] : null))
			exit();
	}
}
header("Status: 404 Not Found");
header('HTTP/1.0 404 Not Found');