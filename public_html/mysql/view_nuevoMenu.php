<?php
/**
 * Maneja la inserción de nuevos menús
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../../includes/db_connect.php';
require_once dirname(__FILE__) . '/../../includes/functions.php';
require_once dirname(__FILE__) . '/../../includes/model/DAO.php';
require_once dirname(__FILE__) . '/../../includes/model/TipoMenuTO.php';
require_once dirname(__FILE__) . '/../../includes/model/ElementosMenu.php';

sec_session_start();

try {
    if (!login_check()) throw new Exception('No logeado');

    if (!(seteada("name") && seteada("precio") && seteada("elementos"))) {
        throw new Exception('Parámetros insuficientes');
    }

    $mysqli->autocommit(false);
    $mysqli->query("BEGIN TRANSACTION;");

    $nombre = htmlspecialchars(obtener("name"));
    $precio = obtener("precio");
    $elementos = json_decode(obtener("elementos"));
    if (empty($nombre) || !is_numeric($precio)) {
        throw new Exception('Nombre o precio no válidos.');
    }
    foreach ($elementos as $elemento) {
        if (!is_numeric($elemento) || $elemento <= 0) {
            throw new Exception('Algún elemento no es válido.');
        }
    }

    $fabrica = obtenerDAOFactory();
    $dao = $fabrica->obtenerTiposMenuDAO();
    unset($fabrica);

    $menu = $dao->nuevoTipoMenu($_SESSION['id_comedor'], $nombre, $precio);

    asociarElementosMenu($elementos, $menu->getId());

    $mysqli->commit();
    $mysqli->close();
    header("Location: /panel/");
} catch (Exception $e) {
    $mysqli->rollback();
    $mysqli->close();
    header("Location: /panel/?error=" . urlencode($e->getMessage()));
}