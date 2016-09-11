<?php
/*
 * Inserta un nuevo plato en la base de datos, asociado al comedor logeado como su plato.
 * Permite también indicar que el plato se servirá un día dado, en cuyo caso se realiza la
 * asociación correspondiente empleando los atributos fecha y agotado.
 * Si paraServir no es indicado su valor por defecto es 1 (true).
 *
 * Recibe: nombre, descripcion, tipo, [paraServir, agotado=no, fecha*=hoy]
 *     *fecha - el formato de la fecha debe ser 'Y-m-d' (p.e. 2016-02-25)
 * También puede recibir el id de un plato a asociar ('idPlato'), realizando únicamente
 * la asociación y ahorrándose la inserción.
 *
 * David Campos Rodríguez
 */
include_once dirname(__FILE__).'/../../includes/model/Platos.php';
include_once dirname(__FILE__).'/../../includes/model/Tener.php';
include_once dirname(__FILE__) . '/../../includes/model/MisPlatos.php';
include_once dirname(__FILE__).'/../../includes/functions.php';

// ESTO ES JSON!
header('Content-Type: application/json;charset=utf-8');

sec_session_start();

define('SERVIR_KEY', 'paraServir');
define('AGOT_KEY', 'agotado');
define('FECHA_KEY', 'fecha');

define('ID_PLATO_KEY', 'idPlato');

define('NAME_KEY', 'nombre');
define('DESC_KEY', 'descripcion');
define('TIPO_KEY', 'tipo');


// Comprobamos si estás logeado
if( login_check() ) {
	$idComedor = $_SESSION['id_comedor'];
	$plato = array();

	// Agotado o fecha son opcionales, su valor por defecto es 'no' y 'hoy'
    if(seteada_y_numerica(SERVIR_KEY))
        $paraServir = obtener(SERVIR_KEY, FILTER_SANITIZE_NUMBER_INT);
    else
        $paraServir = true;
    $agotado = obtener(AGOT_KEY, FILTER_SANITIZE_NUMBER_INT) or $agotado = 0;
    $fecha = obtener(FECHA_KEY, FILTER_SANITIZE_STRING) or $fecha = date('Y-m-d');

    $plato['_id'] = obtener(ID_PLATO_KEY, FILTER_SANITIZE_NUMBER_INT);
    $plato['nombre'] = htmlspecialchars(obtener(NAME_KEY, FILTER_SANITIZE_STRING));
    $plato['descripcion'] = htmlspecialchars(obtener(DESC_KEY, FILTER_SANITIZE_STRING));
    $plato['tipo'] = obtener(TIPO_KEY, FILTER_SANITIZE_STRING);
    $plato['prestado'] = 1;

    // Empezamos
    $mysqli->autocommit(false);
    $mysqli->query("START TRANSACTION READ WRITE;");

    try {
        if (!$plato['_id'] || $plato['_id'] < 0) {
            // Si no se ha especificado id, tratamos de insertarlo
            if (preg_match("/[0-2].{4}/", $plato['tipo']) !== 1)
                die(errorJson('El tipo de plato no es válido, ¿posible ataque?'));

            $plato = insertarPlato($plato['nombre'], $plato['descripcion'], $plato['tipo']);
        } else {
            // Si no, necesitamos obtenerlo para devolverlo
            $aux = $plato['prestado'];
            $plato = obtenerPlato($plato['_id']);
            if( $plato === null ) {
                die(errorJson('El plato no existe.'));
            }
            $plato['prestado'] = $aux;
            unset($aux);

        }
        if ( !$plato['prestado'] || !enMisPlatos($plato['_id'], $idComedor) ) {
            // Si no tenemos la seguridad de que si es prestado (lo cual indica que ya está en nuestros platos),
            // o no se encuentra en nuestros platos, lo añadimos a MisPlatos
            asociarMisPlatos($idComedor, $plato['prestado'], $plato['_id']);
        }
        if( $paraServir ) {
            // Si es para servir y no está asociado lo asociamos, si está repetido lo advertimos
            if(!tienePlato($idComedor, $fecha, $plato['_id'])) {
                asociarTener($idComedor, $fecha, $agotado, $plato['_id']);
            } else {
                $mysqli->rollback();
                die(errorJson('Repetido'));
            }
        }

        $plato['fecha'] = $fecha;
        $mysqli->commit();
        die(exitoJson($plato));

    } catch (Exception $e) {
        $mysqli->rollback();
        die(errorJson($e->getMessage()));
    }
} else {
	// No se ha hecho login
	die(errorJson('No logeado'));
}
