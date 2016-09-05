<?php
/**
 * Modelo de Mis Platos
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../db_connect.php';

/**
 * Realiza en la base de datos una asociación entre el plato indicado (mediante su id) y el comedor indicado (mediante
 * su id) en la tabla MisPlatos
 *
 * @param $idComedor integer Atributo '_id' del comedor
 * @param $prestado bool Indica si el plato es prestado o no (si es propiedad de otro comedor)
 * @param $id_plato int Atributo '_id' del plato
 *
 * @throws Exception En caso de no poder realizarse la asociación
 */
function asociarMisPlatos($idComedor, $prestado, $id_plato) {
    global $mysqli;

    if( $stmt = $mysqli->prepare('INSERT INTO MisPlatos(comedor, prestado, plato) VALUES (?,?,?)') ) {
        $stmt->bind_param('iii', $idComedor, $prestado, $id_plato);

        if(! ($stmt->execute() && $stmt->affected_rows == 1) ) {
            $stmt->close();
            throw new Exception('No se pudo asociar en MisPlatos('.$idComedor.', '.$prestado.', '.$id_plato.'): '.$mysqli->error);
        }
        $stmt->close();
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}

/**
 * Desasocia un plato de un comedor
 * @param $idComedor int Id del comedor que desea desasociar el plato
 * @param $id_plato int Id del plato que desea ser desasociado (en lo más profundo de su ser, sí, él lo desea)
 * @throws Exception En caso de que no se pueda preparar o ejecutar la consulta
 */
function desasociarMisPlatos($idComedor, $id_plato) {
    global $mysqli;

    if( $stmt = $mysqli->prepare('DELETE FROM MisPlatos WHERE plato=? AND comedor=?') ) {
        $stmt->bind_param('ii', $id_plato, $idComedor);

        if(! ($stmt->execute() && $stmt->affected_rows == 1) ) {
            $stmt->close();
            throw new Exception('No se pudo desasociar en MisPlatos');
        }
        $stmt->close();
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}

/**
 * Determina si un plato se encuentra entre los platos de un comedor o no
 * @param $id_plato int Atributo '_id' del plato
 * @param $idComedor int Atributo '_id' del comedor
 * @return bool
 * @throws Exception si no puede hacer la comprobación
 */
function enMisPlatos($id_plato, $idComedor) {
    global $mysqli;
    if( $stmt = $mysqli->prepare('SELECT prestado FROM MisPlatos WHERE comedor=? AND plato=? LIMIT 1') ) {
        $stmt->bind_param('ii', $idComedor, $id_plato);
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
 * Determina si un plato es prestado o es propio del comedor
 * @param $id_plato int Atributo '_id' del plato
 * @param $idComedor int Atributo '_id' del comedor
 * @return bool|null TRUE si es prestado, FALSE si no lo es, null si el plato no es del comedor
 * @throws Exception si no puede hacer la comprobación
 */
function esPrestadoMisPlatos($id_plato, $idComedor) {
    global $mysqli;
    if( $stmt = $mysqli->prepare('SELECT prestado FROM MisPlatos WHERE comedor=? AND plato=? LIMIT 1') ) {
        $stmt->bind_param('ii', $idComedor, $id_plato);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0) {
            $stmt->bind_result($prestado);
            $stmt->fetch();
            $stmt->close();
            return ($prestado==1);
        } else {
            return null;
        }
    } else {
        throw new Exception('No se pudo preparar: '.$mysqli->error);
    }
}

/**
 * Obtiene los platos de un comedor
 * @param $id_comedor integer Id del comedor
 * @param $limite integer Limite de resultados (tamaño de página)
 * @param $page integer Pagina de inicio de los resultados
 * @return array Platos del comedor que coinciden con el patrón
 *
 * @throws Exception si no se puede realizar la consulta
 */
function obtenerMisPlatos($id_comedor, $page, $limite)
{
    global $mysqli;

    $page *= $limite; // Convertimos pagina a numero de platos

    if ($stmt = $mysqli->prepare(
        "SELECT p._id,p.nombre,p.descripcion,p.tipo,mp.prestado
        FROM Platos p JOIN MisPlatos mp ON (mp.plato = p._id)
        WHERE mp.comedor = ?
        ORDER BY p.tipo,p.nombre
        LIMIT ?,?")) {

        $stmt->bind_param('iii', $id_comedor, $page, $limite);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $descripcion, $tipo, $prestado);
        $platos = array();
        while ($stmt->fetch()) {
            $platos[] = array(
                "_id" => $id,
                "nombre" => $nombre,
                "descripcion" => $descripcion,
                "prestado" => $prestado,
                "tipo" => $tipo);
        }
        $stmt->close();
        return $platos;
    } else {
        throw new Exception('No se pudo preparar: ' . $mysqli->error);
    }
}

/**
 * Busca en mis platos el plato cuyo nombre coincide con $like (mediante un LIKE de MySQL)
 * @param $like string Patrón para el nombre
 * @param $id_comedor integer Id del comedor
 * @param $limite integer Limite de resultados
 * @return array Platos del comedor que coinciden con el patrón
 *
 * @throws Exception si no se puede realizar la consulta
 */
function buscarEnMisPlatos($like, $id_comedor, $limite)
{
    global $mysqli;
    if ($stmt = $mysqli->prepare(
        "SELECT p._id,p.nombre,p.descripcion,p.tipo,mp.prestado
        FROM (Platos p JOIN MisPlatos mp ON (mp.plato = p._id)) LEFT JOIN Tener t ON (t.id_plato = p._id)
        WHERE LOWER( p.nombre ) LIKE ? AND mp.comedor = ?
        GROUP BY p._id
        ORDER BY COUNT(p._id) DESC
        LIMIT ?")) {

        $stmt->bind_param('sii', $like, $id_comedor, $limite);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $nombre, $descripcion, $tipo, $prestado);
        $platos = array();
        while ($stmt->fetch()) {
                $platos[] = array(
                    "_id" => $id,
                    "nombre" => $nombre,
                    "descripcion" => $descripcion,
                    "prestado" => $prestado,
                    "tipo" => $tipo,
                    "mio" => true);
        }
        $stmt->close();
        return $platos;
    } else {
        throw new Exception('No se pudo preparar: ' . $mysqli->error);
    }
}

/**
 * Busca entre los platos que no pertenecen al comedor
 * @param $like string Patrón para el nombre
 * @param $id_comedor integer Id del comedor
 * @param $limite integer Limite de resultados
 * @return array Platos del comedor que coinciden con el patrón
 *
 * @throws Exception si no se puede realizar la consulta
 */
function buscarFueraDeMisPlatos($like, $id_comedor, $limite) {
    global $mysqli;
    if ($stmt = $mysqli->prepare(
        "SELECT p._id,p.nombre,p.descripcion,p.tipo
        FROM Platos p LEFT JOIN MisPlatos mp ON (mp.plato = p._id) LEFT JOIN Tener t ON (t.id_plato = p._id)
        WHERE LOWER( p.nombre ) LIKE ? AND (mp.comedor IS NULL OR mp.comedor <> ?)
        GROUP BY p._id
        ORDER BY COUNT(p._id) DESC
        LIMIT ?")) {

        $stmt->bind_param('sii', $like, $id_comedor, $limite);
        $stmt->execute();
        $stmt->bind_result($id, $nombre, $descripcion, $tipo);
        $platos = array();
        while ($stmt->fetch()) {
            $platos[] = array(
                "_id" => $id,
                "nombre" => $nombre,
                "descripcion" => $descripcion,
                "tipo" => $tipo,
                "mio" => false);
        }
        $stmt->close();
        return $platos;
    } else {
        throw new Exception('No se pudo preparar: ' . $mysqli->error);
    }
}

/**
 * Realiza una copia del plato y asigna a ella todos los comedores que tenian el plato como prestado.
 * A continuación, hace dueño del plato a un comedor aleatoriamente (alguien tiene que tener prestado = 0).
 * @param $id_plato int Id del plato
 * @param $id_comedor int Id del comedor
 * @throws Exception si no se puede llevar a cabo la actualización
 */
function salvarParaOtrosMiPlato($id_plato, $id_comedor) {
    global $mysqli;
    $nuevoId = duplicarPlato($id_plato);
    if( $stmt = $mysqli->prepare("UPDATE MisPlatos SET plato=? WHERE comedor<>? AND plato=? AND prestado=1")) {
        $stmt->bind_param('iii', $nuevoId, $id_comedor, $id_plato);
        $exito = $stmt->execute();
        $stmt->close();
        if(!$exito)
            throw new Exception('Error al ejecutar: '.$mysqli->error);

        if( $stmt = $mysqli->prepare("UPDATE MisPlatos SET prestado=0 WHERE plato=? AND prestado=1 LIMIT 1")) {
            $stmt->bind_param('i', $nuevoId);
            $exito = $stmt->execute();
            $stmt->close();
            if(!$exito)
                throw new Exception('Error al ejecutar: '.$mysqli->error);

        } else
            throw new Exception('No se pudo preparar: '.$mysqli->error);
    } else
        throw new Exception('No se pudo preparar: '.$mysqli->error);
}

/**
 * Obtiene el número total de platos que posee el comedor en sus platos
 * @param $id_comedor int Id del comedor
 *
 * @return int Número de platos que tiene este comedor asociados
 * @throws Exception si no puede preparar la sentencia
 */
function misPlatosCount($id_comedor) {
    global $mysqli;
    if( $stmt = $mysqli->prepare(
        "SELECT COUNT(p._id) FROM Platos p JOIN MisPlatos mp ON (mp.plato = p._id) WHERE mp.comedor = ?") ) {
        $stmt->bind_param('i', $id_comedor);
        $stmt->execute();
        $stmt->bind_result($x);
        $stmt->fetch();
        $stmt->close();
        return $x;
    } else
        throw new Exception('No se pudo preparar: '.$mysqli->error);
}