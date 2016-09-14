<?php
/**
 * Elimina el menú de id dado si pertenece al comedor dado.
 * NOTA: Los menús se encuentran asociados A UN ÚNICO COMEDOR.
 * @author David Campos R.
 */
require_once dirname(__FILE__) . '/../../includes/db_connect.php';
require_once dirname(__FILE__) . '/../../includes/functions.php';
require_once dirname(__FILE__) . '/../../includes/model/DAO.php';
require_once dirname(__FILE__) . '/../../includes/model/TipoMenuTO.php';
require_once dirname(__FILE__) . '/../../includes/model/ElementosMenu.php';

define('ID_KEY', 'idMenu');

sec_session_start();

// ESTO ES JSON!
header('Content-Type: application/json;charset=utf-8');

$idMenu = obtener(ID_KEY, FILTER_SANITIZE_NUMBER_INT);
$mysqli->autocommit(false);
$mysqli->query("BEGIN TRANSACTION;");
try {
    if (!login_check()) throw new Exception('No logeado');

    // Comprobacion CSRF
    if (seteada('auth_token')) {
        $token = obtener('auth_token');
        $comprobacion = comprobarFormToken('eliminar_menu', $token);
        if (!$comprobacion) {
            throw new Exception('Ataque CSRF detectado.');
        }
    } else {
        throw new Exception('Ataque CSRF detectado muy duramente.');
    }

    if (!$idMenu)
        throw new Exception('Id del menú no válido o no indicado.');

    $fabrica = obtenerDAOFactory();
    $dao = $fabrica->obtenerTiposMenuDAO();
    unset($fabrica);
    $menuTo = $dao->obtenerTipoMenuTO($idMenu);

    if ($menuTo->getIdComedor() != $_SESSION['id_comedor']) {
        throw new Exception('El menú no es tuyo.');
    }

    desasociarElementosMenu($menuTo->getId());
    $dao->eliminarTipoMenu($menuTo->getId());
    unset($dao);

    $mysqli->commit();
    $mysqli->close();
    die(exitoJson($menuTo));
} catch (Exception $e) {
    $mysqli->rollback();
    $mysqli->close();
    die(errorJsonConRespuesta($e->getMessage(), $idMenu));
}