<?php
/*
 * Cierra duramente la conexión
 *
 * David Campos Rodríguez
 */
require_once dirname(__FILE__).'/../../includes/db_connect.php';
require_once dirname(__FILE__).'/../../includes/functions.php';
sec_session_start();

// En la base de datos marca la sesión como terminada
$ahora = date('Y-m-d H:i:s');
$id_comedor = $_SESSION['id_comedor'];
$timestamp = $_SESSION['session_timestamp'];

if( $stmt = $mysqli->prepare("UPDATE Conexiones SET `terminated`=1,lastActivity = ? WHERE comedor_id = ? AND initialTimestamp = ? LIMIT 1 ;") ) {
    $stmt->bind_param('sis', $ahora, $id_comedor, $timestamp);
    if ($stmt->execute()) {
        // Desconfigura todos los valores de sesión.
        $_SESSION = array();

        // Obtiene los parámetros de sesión.
        $params = session_get_cookie_params();

        // Borra el cookie actual.
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);

        // Destruye sesión.
        session_destroy();
        header('Location: ../../');
    } else {
        $error = urlencode("No se ha podido terminar la sesión.");
        header("Location: ../../?error=$error");
    }
} else {
    $error = urlencode($mysqli->error);
    header("Location: ../../?error=$error");
}