<?php
/**
 * Modelo para la tabla TiposMenu
 * @author David Campos Rodríguez
 */

require_once dirname(__FILE__) . '/../db_connect.php'; // Define $mysqli

/**
 * Obtiene los menús del comedor indicado de la base
 * @param $id_comedor int Id del comedor del que se desean obtener los menús
 * @return array(_id:int,nombre:string,precio:string) Array de menús encontrados
 * @throws Exception Si no se puede realizar la consulta
 */
function obtenerMenus($id_comedor) {
    global $mysqli;
    if( $stmt = $mysqli->prepare("SELECT _id,nombre,precio FROM TiposMenu WHERE id_comedor = ?") ) {
        $stmt->bind_param("i", $id_comedor);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $precio);
        $menus = array();
        while( $stmt->fetch()) {
            $menus[] = array(
                "_id" => $id,
                "nombre" => $nombre,
                "precio" => $precio
            );
        }
        $stmt->close();
        return $menus;
    } else
        throw new Exception('prepare: '.$mysqli->error);
}

/**
 * Obtiene el número de menús del comedor indicado
 * @param $id_comedor int Id del comedor del que se desean obtener el número de menús
 * @return int número de menús del comedor
 * @throws Exception Si no se puede realizar la consulta
 */
function countMenus($id_comedor) {
    global $mysqli;
    if( $stmt = $mysqli->prepare("SELECT COUNT(_id) FROM TiposMenu WHERE id_comedor = ?") ) {
        $stmt->bind_param("i", $id_comedor);
        $stmt->execute();
        $stmt->store_result();
        if(!$stmt->num_rows == 1) {
            $stmt->close();
            throw new Exception('countMenus: Increíblemente, la consulta no devolvió ninguna fila.');
        }
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count;
    } else
        throw new Exception('prepare: '.$mysqli->error);
}