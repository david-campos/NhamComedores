<?php
/**
 * Modelo de Tener
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../db_connect.php';

/**
 * Asocia el plato indicado al comedor indicado para servirlo un día concreto
 * @param $id_comedor int Id del comedor
 * @param $fecha string Fecha en la que se servirá el plato
 * @param $agotado int 0 si no está agotado, 1 si lo está
 * @param $id_plato int Id del plato
 * @throws Exception Si no se pudo realizar la asociación
 */
function asociarTener($id_comedor, $fecha, $agotado, $id_plato) {
    global $mysqli;

    if( $stmt = $mysqli->prepare( 'INSERT INTO Tener(id_comedor, fecha, agotado, id_plato) VALUES (?,?,?,?)') ) {
        $stmt->bind_param('isii', $id_comedor, $fecha, $agotado, $id_plato);

        if(! ($stmt->execute() && $stmt->affected_rows == 1) ) {
            throw new Exception('No se pudo asociar en Tener: '.$mysqli->error);
        }
        $stmt->close();
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}

function setAgotadoPlato($id_comedor, $id_plato, $fecha, $agotado) {
    global $mysqli;

    $agotado = ($agotado && $agotado != 'false' ? 1 : 0);
    if ($stmt = $mysqli->prepare('UPDATE Tener SET agotado=? WHERE id_plato=? AND id_comedor=? AND fecha=? LIMIT 1')) {
        $stmt->bind_param('iiis', $agotado, $id_plato, $id_comedor, $fecha);

        if (!($stmt->execute() && $stmt->affected_rows == 1)) {
            $stmt->close();
            throw new Exception('setAgotadoPlato: no se pudo ejecutar o las filas afectadas son 0');
        }
        $stmt->close();
    } else
        throw new Exception('setAgotadoPlato-prepare: ' . $mysqli->error);
}

/**
 * Indica que un comedor ya no va a servir un plato en una fecha determinada, borrando de la base la asociación
 * correspondiente.
 * @param $id_comedor int Id del comedor
 * @param $fecha string Fecha en la que se encontraba asociado
 * @param $id_plato int Id del plato a eliminar
 *
 * @throws Exception Si no se puede preparar la consulta o no existe tal asociacion
 */
function desasociarTener($id_comedor, $fecha, $id_plato) {
    global $mysqli;

    if( $stmt = $mysqli->prepare('DELETE FROM Tener WHERE id_comedor=? AND fecha=? AND id_plato=?') ) {
        $stmt->bind_param('isi', $id_comedor, $fecha, $id_plato);

        if( $stmt->execute() ) {
            $num = $stmt->affected_rows;
            $stmt->close();
            if($num != 1) throw new Exception("No existe la asociación a eliminar: $id_comedor,$fecha,$id_plato");
        } else {
            $stmt->close();
            throw new Exception('No se pudo ejecutar la sentencia: '.$mysqli->error);
        }
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}

/**
 * Comprueba si un comedor sirve un plato en un día dado.
 * @param $id_comedor int Id del comedor
 * @param $fecha string Fecha en la que queremos comprobar si sirve el plato
 * @param $id_plato int Id del plato
 * @return bool
 * @throws Exception En caso de no poder hacer la comprobación
 */
function tienePlato($id_comedor, $fecha, $id_plato) {
    global $mysqli;
    if( $stmt = $mysqli->prepare('SELECT agotado FROM Tener WHERE id_comedor=? AND fecha=? AND id_plato=? LIMIT 1') ) {
        $stmt->bind_param('isi', $id_comedor, $fecha, $id_plato);
        $stmt->execute();
        $stmt->store_result();
        $res = ($stmt->num_rows > 0);
        $stmt->close();
        return $res;
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}

/**
 * Obtiene los platos servidos el día indicado por el comedor indicado, ordenados por tipo de plato
 * @param $id_comedor int Id del comedor
 * @param $fecha string fecha en formato Y-m-d
 * @return array Array con tres arrays, cada uno con los platos de cada tipo
 * @throws Exception si no se puede ejecutar la consulta
 */
function obtenerPlatosServidos($id_comedor, $fecha) {
    global $mysqli;
    if( $stmt = $mysqli->prepare(
        'SELECT p._id,p.nombre,p.descripcion,p.tipo,t.agotado
         FROM Platos p JOIN Tener t ON (p._id = t.id_plato)
         WHERE t.id_comedor=? AND t.fecha=?'
    ) ) {
        $stmt->bind_param('is', $id_comedor, $fecha);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $descripcion, $tipo, $agotado);
        $platos = array(array(), array(), array());
        while( $stmt->fetch() ) {
            $platos[substr($tipo,0,1)][] = array(
                "_id"=>$id,
                "nombre"=>$nombre,
                "descripcion"=>$descripcion,
                "tipo"=>$tipo,
                "agotado"=>$agotado
            );
        }
        $stmt->close();
        return $platos;
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}