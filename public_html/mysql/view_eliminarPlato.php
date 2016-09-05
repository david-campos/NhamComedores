<?php
/**
 * Elimina un plato, lo desasocia de la asociación indicada, si no es necesario nunca más, lo borra.
 * @author David Campos R.
 */

include_once dirname(__FILE__).'/../../includes/model/Platos.php';
include_once dirname(__FILE__).'/../../includes/model/Tener.php';
include_once dirname(__FILE__).'/../../includes/model/Misplatos.php';
include_once dirname(__FILE__).'/../../includes/functions.php';

// ESTO ES JSON!
header('Content-Type: application/json;charset=utf-8');

sec_session_start();

define('ID_PLATO_KEY', 'idPlato');
define('ASOCIACION_KEY', 'asoc');
define('FECHA_KEY', 'fecha');

// Comprobamos si estás logeado
if( login_check() ) {
    if( seteada_y_numerica(ID_PLATO_KEY) ) {
        $id_plato = obtener(ID_PLATO_KEY, FILTER_SANITIZE_NUMBER_INT);
    } else {
        die(errorJson('No se ha indicado un id de plato.'));
    }

    $asociacion = obtener(ASOCIACION_KEY) or $asociacion = 'MisPlatos'; // Por defecto, MisPlatos
    try {
        switch (strtolower($asociacion)) {
            case 'misplatos':
                desasociarMisPlatos($_SESSION['id_comedor'], $id_plato);
                die(exitoJson(array("id_plato"=>$id_plato)));
                break;
            case 'tener':
                $fecha = obtener(FECHA_KEY, FILTER_SANITIZE_STRING) or die(errorJson('Fecha no indicada.'));
                desasociarTener($_SESSION['id_comedor'], $fecha, $id_plato);
                die(exitoJson(array("id_plato"=>$id_plato,"fecha"=>$fecha)));
                break;
            default:
                die(errorJson(ASOCIACION_KEY . ' no válido'));
        }
    } catch (Exception $e) {
        die(errorJson($e->getMessage()));
    }
} else
    die(errorJson('No logeado'));