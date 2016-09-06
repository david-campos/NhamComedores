<?php
/**
 * Este script es el núcleo de funcionamiento de la api, la función obtenerLineas
 * realiza las consultas a la base de datos y lo guarda en un array.
 *
 * @author David Campos Rodríguez
 */

require_once(dirname(__FILE__) . '/db_connect.php');

/* CONSTANTES */

/**
 * Activa el modo sin fechas, que se salta la comprobación de la fecha en el listado
 * de platos. ¡SOLO PARA DEBUG!
 */
define("NO_DATE_MODE", false);

// Tipos de consultas, con su número
/** Consulta de comedores de la base */
define("COMEDORES", 0);
/** Consulta de elementos de un menú */
define("MENU", 1);
/** Consulta de menús de un comedor */
define("MENUS", 2);
/** Consulta de platos de un comedor */
define("COMIDA", 3);
/** Consulta de elementos de menú y sus menús asociados (para un comedor) */
define("ELEMENTOS", 4);
/** Consulta de días con platos para un comedor y mes dados */
define("CALENDARIO", 5);

/** Consulta que lista todos los comedores de la base */
define(
	"CONSULTA_COMEDORES",
	"SELECT _id, nombre, horaInicio, horaFin, coordLat, coordLon, telefono, nombreContacto,
			direccion, hAperturaIni, hAperturaFin, diaInicioApertura, diaFinApertura, promocion
	 FROM Comedores ");

/** Consulta que lista todos los elementos que conforman un menú dado */
define(
	"CONSULTA_MENU",
	"SELECT _id, nombre, tipo
	 FROM ElementosMenu
	 WHERE _id IN (
		SELECT id_elemMen
		FROM Tienen t
		WHERE t.id_tipoMen=?)");

/**
 * Consulta que lista los elementos que se encuentran en algún menú de un comedor
 * dado.
 */
define(
	"CONSULTA_ELEMENTOS",
	"SELECT e._id, e.nombre, e.tipo
	 FROM ElementosMenu e, TiposMenu m
	 WHERE e._id IN (
		SELECT id_elemMen
		FROM Tienen t
		WHERE t.id_tipoMen=m._id
		AND m.id_comedor = ? )
	 GROUP BY e._id");
/**
 * Consulta que lista los id's de los menús e id's de los elementos que los conforman
 * para un comedor dado.
 */
define(
	"CONSULTA_ELEMENTOS_2",
	"SELECT id_tipoMen AS menu, id_elemMen AS elemento
	 FROM Tienen t INNER JOIN TiposMenu m ON (t.id_tipoMen = m._id)
	 WHERE m.id_comedor = ?");
/**
 * Consulta que lista los menús para un comedor dado
 */
define(
	"CONSULTA_MENUS",
	"SELECT _id, nombre, precio
	 FROM TiposMenu
	 WHERE id_comedor = ?");
/** Lista los platos servidos para un comedor y una fecha dados */
define(
	"CONSULTA_COMIDA",
	"SELECT p._id, nombre, descripcion, p.tipo, t.agotado
	FROM Platos p INNER JOIN Tener t ON (p._id = t.id_plato)
	WHERE id_comedor = ? AND fecha = ?");
/** Lista los platos servidos para un comedor dado sin importar la fecha */
define(
	"CONSULTA_COMIDA_NO_DATE",
	"SELECT p._id, nombre, descripcion, p.tipo, t.agotado
	FROM Platos p INNER JOIN Tener t ON (p._id = t.id_plato)
	WHERE id_comedor = ?");
/**
 * Consulta que lista los dias de un mes que un comedor tiene platos, pero no los
 * platos
 */
define(
	"CONSULTA_CALENDARIO",
	"SELECT DAY(t.fecha) AS dia
	FROM Platos p INNER JOIN Tener t ON (p._id = t.id_plato)
	WHERE id_comedor = ? AND YEAR(t.fecha) = ? AND MONTH(t.fecha) = ?
	GROUP BY DAY(t.fecha)");

/* FIN CONSTANTES */

/**
 * Obtiene un array asociativo con la consulta requerida.
 *
 * Esta función constituye el núcleo de la API, realiza la consulta solicitada
 * con los parámetros indicados. Si la consulta se realiza con éxito, se devolverá
 * un array que contendrá un campo 'status' con el valor 'OK' y un campo 'respuesta'
 * con la respuesta (que depende de la consulta).
 * Si hay algún error el array contendrá un campo 'status' con el valor 'ERROR' y
 * un campo 'error' con un mensaje descriptivo del error, en conjunto con un número
 * identificativo de error.
 *
 * Los valores requeridos en el argumento $valores para cada consulta son los siguientes:
 * - MENU: 'id' con el _id del menu
 * - ELEMENTOS: 'id' con el _id del comedor
 * - MENUS: 'id' con el _id del comedor
 * - COMIDA: 'id' con el _id del comedor, 'fecha' con la fecha. En NO_DATE_MODE la
 *     fecha no es necesaria, si lo es y no se indica se tomará la fecha actual.
 * - CALENDARIO: 'id' con el _id del comedor, 'year' con el año, 'month' con el mes
 * - COMEDORES: no requiere ningún valor en $valores
 *
 * Todas las consultas devuelven en el campo 'respuesta' un array, excepto ELEMENTOS,
 * que devolverá un array asociativo con los campos 'elementos', que contendrá el
 * array de elementos, y 'asociaciones', que contendrá los id de los menús como claves
 * y los arrays de id's de los elementos correspondientes. Un ejemplo de resultado
 * de la consulta ELEMENTOS sería el siguiente:
 * <code>
 * $lineas = array(
 *     'status' => 'OK',
 *     'respuesta' => array(
 *         'elementos' => array(
 *             array('_id'=>'0', 'nombre'=>'elemento0', 'tipo'=>'tipo')
 *         ),
 *         'asociaciones' => array(
 *             '1' => array(0) // La clave es el id del menú, el array contiene los id's de sus elementos
 *         )
 *     )
 * );
 * </code>
 *
 * @param integer $tipoConsulta Tipo de consulta a realizar, facilitada por las constantes
 *     en este archivo descritas.
 * @param array[string]string $valores Conjunto de parametros requeridos por la consulta
 * @return array[] El array descrito en la descripción de la función
 */
function obtenerLineas($tipoConsulta,$valores) {
	global $mysqli;
	
	// Iniciamos $lineas
	$lineas = array('status'=>'OK','respuesta'=>array());
	
	switch($tipoConsulta) {
	case MENU:
		if( isset($valores['id']) ) { 
			$stmt = $mysqli->prepare(CONSULTA_MENU);
			$stmt->bind_param('i', $valores['id']);
			$stmt->execute();
			$stmt->bind_result($id, $nombre, $tipo);
			while( $stmt->fetch() ) {
				$lineas['respuesta'][] = array('_id'=>$id,'nombre'=>$nombre,'tipo'=>$tipo);
			}
			$stmt->close();
		} else {
			$lineas['status'] = 'ERROR';
			$lineas['error'] = 'La consulta tipo '.MENU.'(menu) requiere un '.
			'parámetro "id" con el id del menú';
		}
		break;
	case ELEMENTOS:
		if( isset($valores['id']) ) {
			$stmt1 = $mysqli->prepare(CONSULTA_ELEMENTOS);
			$stmt2 = $mysqli->prepare(CONSULTA_ELEMENTOS_2);
			$stmt1->bind_param('i', $valores['id']);
			$stmt2->bind_param('i', $valores['id']);

			//La guardamos en el array $lineas
			//Se genera un array con dos elementos, "elementos", donde se guardan
			//las líneas de los elementos que se pasan; y "relaciones", donde se
			//indica con qué menús se relacionan estos elementos.
			$stmt1->execute();
			$stmt1->bind_result($idElm, $nombre, $tipo);
			$lineas['respuesta']['elementos'] = array();
			while($stmt1->fetch()){
				$lineas['respuesta']['elementos'][] = array('_id'=>$idElm,'nombre'=>$nombre,'tipo'=>$tipo);
			}
			$stmt1->close();

			$stmt2->execute();
			$stmt2->bind_result($menu, $elemento);
			$lineas['respuesta']['relaciones'] = array();
			while($stmt2->fetch()){
				$lineas['respuesta']['relaciones'][$menu][] = $elemento;
			}
			$stmt2->close();
		} else {
			$lineas['status'] = 'ERROR';
			$lineas['error'] = 'La consulta tipo '.ELEMENTOS.'(elementos) requiere un '.
			'parámetro "id" con el id del comedor';
		}
		break;
	case MENUS:
		if( isset($valores['id']) ) {
			$stmt = $mysqli->prepare(CONSULTA_MENUS);
			$stmt->bind_param('i', $valores['id']);
			$stmt->execute();
			$stmt->bind_result($id, $nombre, $precio);
			while( $stmt->fetch() ) {
				$lineas['respuesta'][] = array('_id'=>$id,'nombre'=>$nombre,'precio'=>$precio);
			}
			$stmt->close();
		} else {
			$lineas['status'] = 'ERROR';
			$lineas['error'] = 'La consulta tipo '.MENUS.'(menus) requiere un '.
			'parámetro "id" con el id del comedor';
		}
		break;
	case COMIDA:
		if( isset($valores['id']) ) {
			if(NO_DATE_MODE) {
				$stmt = $mysqli->prepare(CONSULTA_COMIDA_NO_DATE);
				$stmt->bind_param('i', $valores['id']);
			} else {
				if( isset($valores['fecha']) )
					$fecha = $valores['fecha'];
				else
					$fecha = date("Y-m-d");
				$stmt = $mysqli->prepare(CONSULTA_COMIDA);
				$stmt->bind_param('is', $valores['id'], $valores['fecha']);
			}
			$stmt->execute();
			$stmt->bind_result($id, $nombre, $descripcion, $tipo, $agotado);
			while( $stmt->fetch() ) {
				$lineas['respuesta'][] = array('_id'=>$id,'nombre'=>$nombre,
					'descripcion'=>$descripcion, 'tipo'=>$tipo,
					'agotado'=>$agotado);
			}
			$stmt->close();
		} else {
			$lineas['status'] = 'ERROR';
			$lineas['error'] = 'La consulta tipo '.COMIDA.'(platos) requiere un '.
			'parámetro "id" con el id del comedor.';
		}
		break;
	case CALENDARIO:
		if( isset($valores['id']) && isset($valores['month']) && isset($valores['year']) ) {
			$stmt = $mysqli->prepare(CONSULTA_CALENDARIO);
			$stmt->bind_param('iii', $valores['id'], $valores['year'], $valores['month']);
			$stmt->execute();
			$stmt->bind_result($dia);
			while( $stmt->fetch() ) {
				$lineas['respuesta'][] = intval($dia);
			}
			$stmt->close();
		} else {
			$lineas['status'] = 'ERROR';
			$lineas['error'] = 'La consulta tipo '.CALENDARIO.'(calendario) requiere un '.
			'parámetro "id" con el id del comedor, un parámetro "month" con el mes y '.
			'un parámetro "year" con el año.';
		}
		break;
	case COMEDORES:
		$stmt = $mysqli->prepare(CONSULTA_COMEDORES); // por defecto, comedores
		$stmt->execute();
		$stmt->bind_result($id, $nombre, $horaInicio, $horaFin, $coordLat,
			$coordLon, $telefono, $nombreContacto, $direccion, $hAperturaIni,
			$hAperturaFin, $diaInicioApertura, $diaFinApertura, $promocion);
		while( $stmt->fetch() ) {
			$lineas['respuesta'][] = array('_id'=>$id, 'nombre'=>$nombre, 'horaInicio'=>$horaInicio,
				'horaFin'=>$horaFin, 'coordLat'=>$coordLat, 'coordLon'=>$coordLon,
				'telefono'=>$telefono, 'nombreContacto'=>$nombreContacto,
				'direccion'=>$direccion, 'hAperturaIni'=>$hAperturaIni,
				'hAperturaFin'=>$hAperturaFin, 'diaInicioApertura'=>$diaInicioApertura,
				'diaFinApertura'=>$diaFinApertura, 'promocion'=>$promocion);
		}
		$stmt->close();
		break;
	default:
		$lineas['status'] = 'ERROR';
		$lineas['error'] = 'No existe la consulta '.$tipoConsulta;
	}
	
	// Retornamos las líneas
	return $lineas;
}

/**
 * Imprime la imagen png indicada, redimensionada al ancho requerido si este está
 * presente y es menor que el ancho de la imagen original.
 *
 * Si la imagen original no existe no se enviará nada.
 * 
 * @param string $file Dirección del archivo de imagen original
 * @param int|null $ancho Nuevo ancho para la imagen
 * @return bool TRUE si se envía una imagen, FALSE si no se envía nada.
 */
function devolverImagen($file, $ancho) {
	if( file_exists($file) ) {
		$img = imagecreatefrompng($file);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		
		// Comprobamos si se ha pedido un ancho especial
        if ($ancho !== null) {
			$newWidth = filter_var($ancho, FILTER_SANITIZE_NUMBER_INT);
			list($width, $height) = getimagesize($file);

			// El ancho requerido es menor que la imagen?
			if( $newWidth < $width) {
				$newHeight = ($height/$width)*$newWidth;

                $tmp = imagecreatetruecolor($newWidth, $newHeight);
				imagealphablending($tmp, false);
				imagesavealpha($tmp, true);

                imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
				imagedestroy($img);

                $img = $tmp;
			}
		}

        header("Content-type: image/png;");
		imagepng($img);
		
		return true;
	}
	return false;
}