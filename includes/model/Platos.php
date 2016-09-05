<?php
/**
 * Modelo de Platos
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../db_connect.php'; // Define $mysqli

/**
 * Inserta un plato en la base de datos
 * @param $nombre string Nombre del plato
 * @param $descripcion string Descripcion del plato
 * @param $tipo string Valor tipo del dato
 * @return array Un array asociativo con los atributos del plato insertado, incluído el _id
 *
 * @throws Exception si no se puede llevar a cabo la inserción
 */
function _insertarPlato($nombre, $descripcion, $tipo) {
    global $mysqli;

    if(! ($stmt_pl = $mysqli->prepare('INSERT INTO Platos(nombre, descripcion, tipo) VALUES (?,?,?)')) ) {
        throw new Exception('No se pudo preparar la inserción en platos: '.$mysqli->error);
    }

    // Bindeamos parametros
    $stmt_pl->bind_param('sss', $nombre, $descripcion, $tipo);

    // Ejecutamos
    if( !$stmt_pl->execute() ) { // Insercion en platos
        $stmt_pl->close();
        throw new Exception('Error en la ejecución de la inserción en platos.');
    }

    $idPlato = $stmt_pl->insert_id;
    $stmt_pl->close();

    return array("nombre"=>$nombre, "descripcion"=>$descripcion, "tipo"=>$tipo, "_id"=>$idPlato);
}

/**
 * Obtiene el plato indicado de la base de datos
 * @param $idPlato int Id del plato a obtener
 * @return array|null El plato, si se encuentra, o null si no se pudo encontrar.
 * @throws Exception Si no se puede preparar la consulta
 */
function obtenerPlato($idPlato) {
    global $mysqli;
    if( $stmt = $mysqli->prepare('SELECT _id, nombre, descripcion, tipo FROM Platos WHERE _id=? LIMIT 1')) {
        $stmt->bind_param('i', $idPlato);
        $stmt->execute();
        $stmt->store_result();
        if( $stmt->num_rows == 1) {
            $stmt->bind_result($id, $nombre, $descripcion, $tipo);
            $stmt->fetch();
            $plato = array("_id"=>$id, "nombre"=>$nombre, "descripcion"=>$descripcion, "tipo"=>$tipo);
            $stmt->close();
            return $plato;
        } else {
            $stmt->close();
            return null;
        }
    } else
        throw new Exception('No se pudo preparar la sentencia:'.$mysqli->error);
}

/**
 * Duplica un plato en la base de datos, esto es, crea un nuevo plato con otro id pero los mismos valores
 * @param $plato_id
 * @return array
 * @throws Exception
 */
function duplicarPlato($plato_id) {
    $plato = obtenerPlato($plato_id);
    if($plato != null) {
        $plato = _insertarPlato($plato['nombre'], $plato['descripcion'], $plato['tipo']);
        return $plato['_id'];
    } else
        throw new Exception('El plato no existe');
}

/**
 * Modifica el plato indicado
 * @param $plato array('_id'=>int,'nombre'=>strig,'descripcion'=>string,'tipo'=>string) Los datos del plato a modificar,
 *      el atributo _id identifica al plato, el resto de atributos serán los nuevos valores.
 * @throws Exception si no se puede ejecutar o preparar la sentencia
 */
function modificarPlato($plato) {
    global $mysqli;
    if( $stmt = $mysqli->prepare("UPDATE Platos SET nombre=?,descripcion=?,tipo=? WHERE _id=? LIMIT 1") ) {
        $stmt->bind_param('sssi', $plato['nombre'], $plato['descripcion'], $plato['tipo'], $plato['_id']);
        if( $stmt->execute() ) {
            $stmt->close();
        } else {
            $stmt->close();
            throw new Exception('No se pudo ejecutar: '.$mysqli->error);
        }
    } else
        throw new Exception('No se pudo preparar: '.$mysqli->error);
}

/**
 * Elimina el plato, su existencia se convierte en lo que todos somos al fin y al cabo: un puntero a NULL.
 * @param $idPlato int Id del plato a eliminar
 *
 * @throws Exception En caso de que no se pueda realizar la eliminación
 */
function eliminarPlato($idPlato) {
    global $mysqli;
    if( $stmt = $mysqli->prepare("DELETE FROM Platos WHERE _id=?") ) {
        $stmt->bind_param('i', $idPlato);
        if( !$stmt->execute() ) {
            $stmt->close();
            throw new Exception('Execute: '.$mysqli->error);
        }
        if( !$stmt->affected_rows == 1) {
            $stmt->close();
            throw new Exception('No existe el plato indicado');
        }
        $stmt->close();
    } else
        throw new Exception('Prepare: '.$mysqli->error);
}

/**
 * Determina la existencia de un plato en la base de datos
 * @param $nombre string Nombre del plato
 * @param $descripcion string Descripción del plato
 * @param $tipo string Tipo del plato
 * @return array|null Un array asociativo con los atributos del plato si se encuentra, o null en caso de fallo
 *
 * @throws Exception Si no se puede preparar la sentencia de comprobación
 */
function buscaPlato($nombre, $descripcion, $tipo) {
    global $mysqli;

    $nombre = trim($nombre);
    $descripcion = trim($descripcion);
    $descripcion_min = strtolower($descripcion);

    // Vamos a comprobar si existe ya el plato
    if( $stmt = $mysqli->prepare(
            'SELECT _id, nombre, descripcion, tipo FROM Platos '.
            'WHERE nombre=? AND LOWER(CONVERT(descripcion USING latin1))=? AND tipo=? '.
            'LIMIT 1') ) {
        $stmt->bind_param('sss', $nombre, $descripcion_min, $tipo);
        $stmt->execute();
        $stmt->bind_result($res_id, $res_nombre, $res_descripcion, $res_tipo);
        if( $stmt->fetch() ) {
            $array = array("_id"=>$res_id, "nombre"=>$res_nombre, "descripcion"=>$res_descripcion, "tipo"=>$res_tipo);
            $stmt->close();
            return $array;
        } else {
            $stmt->close();
            return null;
        }
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}

/**
 * Inserta un plato y lo devuelve, en caso de que el plato ya exista simplemente lo devuelve
 * @param $nombre string Nombre
 * @param $descripcion string Descripción
 * @param $tipo string Tipo de plato
 *
 * @return array Un array asociativo conteniendo los atributos del plato insertado, incluído su _id y un atributo 'prestado'
 *  que indica si ya existía.
 *
 * @throws Exception si los parámetros son nulos o hay algún problema en la inserción
 */
function insertarPlato($nombre, $descripcion, $tipo) {
    if ($nombre && $descripcion && $tipo) {
        if ( !($plato = buscaPlato($nombre, $descripcion, $tipo)) ) {
            $plato = _insertarPlato($nombre, $descripcion, $tipo);
            $plato['prestado'] = 0;
        }
        if(! isset($plato['prestado']) ) {
            $plato['prestado'] = 1;
        }
        return $plato;
    } else {
        // No se han pasado los parámetros necesarios
        throw new Exception('Parámetros insuficientes');
    }
}