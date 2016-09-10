<?php
/**
 * Agota y desagota platos en la fecha actual para el comedor que ha iniciado sesión
 */
include_once dirname(__FILE__) . '/../../includes/model/Tener.php';
include_once dirname(__FILE__) . '/../../includes/functions.php';

// ESTO ES JSON!
header('Content-Type: application/json;charset=utf-8');

sec_session_start();

define('AGOT_KEY', 'agotado');
define('ID_PLATO_KEY', 'idPlato');

// Comprobamos si estás logeado
if (!login_check()) die(errorJson('No logeado'));
if (!(seteada_y_numerica(ID_PLATO_KEY) && seteada(AGOT_KEY)))
    die(errorJson('Parámetros insuficientes'));

$idComedor = $_SESSION['id_comedor'];
$idPlato = obtener(ID_PLATO_KEY);
$agotado = obtener(AGOT_KEY);
$hoy = date('Y-m-d');

try {
    setAgotadoPlato($idComedor, $idPlato, $hoy, $agotado);
    die(exitoJson(array("_id" => $idPlato, "agotado" => $agotado, "fecha" => $hoy)));
} catch (Exception $e) {
    die(errorJsonConRespuesta($e->getMessage(), array("_id" => $idPlato, "agotado" => $agotado, "fecha" => $hoy)));
}