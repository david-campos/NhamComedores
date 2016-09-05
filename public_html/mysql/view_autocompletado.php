<?php
/*
 * view_autocompletado.php
 * Archivo php que se encarga de dar respuesta a las consultas del
 * autocompletado del formulario de nuevo plato. Para más información
 * ver js/formNuevoPlato.js
 *
 * David Campos Rodríguez
 * 03/08/2016
 */
include_once dirname(__FILE__).'/../../includes/functions.php';
include_once dirname(__FILE__).'/../../includes/model/MisPlatos.php';

sec_session_start();

// ESTO ES JSON!
header('Content-Type: application/json;charset=utf-8');

// Variable con el id del menú
define("NOMBRE_KEY", "nombre");
define("MP_KEY", "misPlatos"); // Incluir platos propios?

define("NUM_RESULTADOS", 20); // Número de resultados a devolver

if( login_check() ) {
    // Obtenemos el nombre
    if( seteada(NOMBRE_KEY) ) {
        $nombre = strtolower(obtener(NOMBRE_KEY, FILTER_SANITIZE_STRING))."%";
    }else{
        die(errorJson("No hay nombre."));
    }
    if( seteada_y_numerica(MP_KEY) )
        $sinMisPlatos = (obtener(MP_KEY)==0);
    else
        $sinMisPlatos = false;

    try {
        $platos = buscarFueraDeMisPlatos($nombre, $_SESSION['id_comedor'], 20);
        if (!$sinMisPlatos) {
            $misPlatos = buscarEnMisPlatos($nombre, $_SESSION['id_comedor'], 20);
            $platos = array_merge($misPlatos, $platos);
        }

        $array = array(
            'buscado' => $nombre,
            'status' => 'OK',
            'resultados' => $platos);

        die(json_encode($array));
    } catch(Exception $e) {
        die(errorJson($e->getMessage()));
    }
}else
    die(errorJson("No logeado"));