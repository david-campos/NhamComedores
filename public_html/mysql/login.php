<?php
/*
 * Realiza el login en la web
 *
 * David Campos Rodríguez
 */
include_once dirname(__FILE__).'/../../includes/db_connect.php';
include_once dirname(__FILE__).'/../../includes/functions.php';
 
sec_session_start(); // Nuestra manera personalizada segura de iniciar sesión PHP.
 
if (isset($_POST['loginName'], $_POST['p'])) {
    $login_name = $_POST['loginName'];
    $codigo = $_POST['p']; // La contraseña con hash
 
    if (login($login_name, $codigo, $mysqli) == true) {
        if (isset($_GET['c'])) {
			$c = filter_var($_GET['c'], FILTER_SANITIZE_STRING);
			if($c != 'login')
				header('Location: ../../'.urlencode($c).'/');
			else
				header('Location: ../../');
		} else
			header('Location: ../../');
    } else {
        // Inicio de sesión fail
        $error = urlencode("Login incorrecto");
        header("Location: ../../?error=$error");
    }
} else {
    header("Location: ../../");
}
