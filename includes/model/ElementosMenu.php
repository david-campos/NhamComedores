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