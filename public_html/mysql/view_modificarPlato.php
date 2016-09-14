<?php
/**
 * Permite modificar un plato de MisPlatos, el script se encarga de gestionar si es tuyo o prestado.
 * Si es tuyo se creará una copia a la que se asignarán todos los comedores a los que se ha prestado,
 * si es prestado se creará una copia para ti.
 * @author David Campos R.
 */

include dirname(__FILE__)."/../../includes/model/Platos.php";
include dirname(__FILE__)."/../../includes/model/MisPlatos.php";
include_once dirname(__FILE__).'/../../includes/functions.php';

// ESTO ES JSON!
header('Content-Type: application/json;charset=utf-8');

sec_session_start();

define("ID_PLATO_KEY", "_id");
define("NOMBRE_KEY", "nombre");
define("DESCRIPCION_KEY", "descripcion");
define("TIPO_KEY", "tipo");

if( login_check() ) {
    $plato = array(
        "nombre" => htmlspecialchars(obtener(NOMBRE_KEY, FILTER_SANITIZE_STRING)),
        "descripcion" => htmlspecialchars(obtener(DESCRIPCION_KEY, FILTER_SANITIZE_STRING)),
        "tipo"=>obtener(TIPO_KEY, FILTER_SANITIZE_STRING),
        "_id" => obtener(ID_PLATO_KEY, FILTER_SANITIZE_NUMBER_INT),
    );

    // Comprobacion CSRF
    if (seteada('auth_token')) {
        $token = obtener('auth_token');
        $comprobacion = comprobarFormToken('modificar_plato', $token);
        if (!$comprobacion) {
            die(errorJson('Ataque CSRF detectado.'));
        }
    } else {
        throw new Exception('Ataque CSRF detectado muy duramente.');
    }

    if(!($plato['nombre'] && $plato['descripcion'] && $plato['tipo'] && $plato['_id']))
        die(errorJson('Información de plato incompleta.'));

    if (preg_match("/[0-2].{4}/", $plato['tipo']) !== 1)
        die(errorJson('El tipo de plato no es válido, ¿posible ataque?'));

    try {
        // Empezamos
        $mysqli->autocommit(false);
        $mysqli->query("START TRANSACTION READ WRITE;");

        // Es prestado el plato?
        $prestado = esPrestadoMisPlatos($plato['_id'], $_SESSION['id_comedor']);

        if ($prestado === null) {
            $mysqli->rollback();
            die(errorJsonConRespuesta('El plato no pertenece al comedor', $plato));
        }

        if ($prestado) {
            // Es prestado, copiamos el plato y lo asociamos como propio
            desasociarMisPlatos($_SESSION['id_comedor'], $plato['_id']);
            $plato['_id'] = duplicarPlato($plato['_id']);
            asociarMisPlatos($_SESSION['id_comedor'], false, $plato['_id']);
        } else {
            // No es prestado, salvamos el plato
            salvarParaOtrosMiPlato($plato['_id'], $_SESSION['id_comedor']);
        }

        modificarPlato($plato);

        $mysqli->commit();
        die(exitoJson($plato));
    } catch(Exception $e) {
        $mysqli->rollback();
        die(errorJsonConRespuesta($e->getMessage(), $plato));
    }
} else
    die(errorJson('No logeado'));