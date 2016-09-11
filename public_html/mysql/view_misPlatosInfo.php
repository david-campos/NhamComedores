<?php
/**
 * Devuelve la información de MisPlatos del comedor actual en JSON
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../../includes/functions.php';
require_once dirname(__FILE__) . '/../../includes/model/MisPlatos.php';

sec_session_start();

try {
    die(json_encode(obtenerMisPlatos($_SESSION['id_comedor'], 0, misPlatosCount($_SESSION['id_comedor']))));
} catch (Exception $e) {
    die(errorJson($e->getMessage()));
}