<?php
/**
 * Devuelve la información del comedor actual en JSON
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../../includes/functions.php';
require_once dirname(__FILE__) . '/../../includes/model/DAO.php';
require_once dirname(__FILE__) . '/../../includes/model/ComedorTO.php';

sec_session_start();

// ESTO ES JSON!
header('Content-Type: application/json;charset=utf-8');

try {
    $fabrica = obtenerDAOFactory();
    $dao = $fabrica->obtenerComedoresDAO();
    $comedor = $dao->obtenerComedorTO($_SESSION['id_comedor']);
    unset($fabrica);
    unset($dao);

    die(json_encode($comedor->toArray()));
} catch (Exception $e) {
    die(errorJson($e->getMessage()));
}