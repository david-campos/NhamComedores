<?php
/**
 * Modelo para la tabla ElementosMenu ( y su relación con los menús )
 * Se ha evitado crear un modelo para la tabla "Tienen" en especial,
 * pues resulta confusa esta relación con la relación "Tener" existente
 * entre platos y comedores.
 *
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../db_connect.php'; // Define $mysqli

/**
 * Obtiene los elementos del menú indicado de la base
 * @param $id_menu int Id del comedor del que se desean obtener los menús
 * @return array(_id:int,nombre:string,tipo:string) Array de elementos encontrados
 * @throws Exception Si no se puede realizar la consulta
 */
function obtenerElementos($id_menu) {
    global $mysqli;
    if( $stmt = $mysqli->prepare("SELECT _id,nombre,tipo
                                  FROM ElementosMenu em JOIN Tienen t ON (t.id_elemMen = em._id)
                                  WHERE t.id_tipoMen = ?") ) {
        $stmt->bind_param("i", $id_menu);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $tipo);
        $elementos = array();
        while( $stmt->fetch()) {
            $elementos[] = array(
                "_id" => $id,
                "nombre" => $nombre,
                "tipo" => $tipo
            );
        }
        $stmt->close();
        return $elementos;
    } else
        throw new Exception('prepare: '.$mysqli->error);
}

/**
 * Obtiene todos los elementos de la base de datos
 */
function obtenerTodosLosElementos() {
    global $mysqli;
    if ($stmt = $mysqli->prepare("SELECT _id,nombre,tipo FROM ElementosMenu em")) {
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $tipo);
        $elementos = array();
        while ($stmt->fetch()) {
            $elementos[] = array(
                "_id" => $id,
                "nombre" => $nombre,
                "tipo" => $tipo
            );
        }
        $stmt->close();
        return $elementos;
    } else
        throw new Exception('prepare: ', $mysqli->error);
}

/**
 * Asocia los elementos indicados al menú dado
 * @param $elementos int[] array de id's de los elementos a asociar
 * @param $id_menu int id del menú al que asociar los elementos
 * @throws Exception si no se puede realizar la inserción
 */
function asociarElementosMenu($elementos, $id_menu) {
    global $mysqli;
    if ($stmt = $mysqli->prepare("INSERT INTO Tienen(id_tipoMen,id_elemMen) VALUES(?,?)")) {
        $elemento = $elementos[0];
        $stmt->bind_param('ii', $id_menu, $elemento);
        for ($i = 0; $i < count($elementos); $i++) {
            $elemento = $elementos[$i];
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('execute: ' . $mysqli->error);
            }
        }
        $stmt->close();
    } else
        throw new Exception('prepare: ', $mysqli->error);
}

/**
 * Desasocia los elementos asociados al menú indicado
 * @param $id_menu int Id del menú cuyos elementos se quieren desasociar
 * @return int número de elementos desasociados
 * @throws Exception si no se pueden desasociar los elementos
 */
function desasociarElementosMenu($id_menu) {
    global $mysqli;
    if ($stmt = $mysqli->prepare("DELETE FROM Tienen WHERE id_tipoMen=?")) {
        $stmt->bind_param('i', $id_menu);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception('execute: ' . $mysqli->error);
        }
        $afectados = $stmt->affected_rows;
        $stmt->close();
        return $afectados;
    } else
        throw new Exception('prepare: ', $mysqli->error);

}