<?php
/**
 * Modifica ciertos datos del comedor
 * Las imágenes fueron eliminadas por ahora: son una fuente de inseguridades muy alta y no dispongo de tiempo ahora mismo
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../../includes/functions.php';
require_once dirname(__FILE__) . '/../../includes/model/DAO.php';
require_once dirname(__FILE__) . '/../../includes/model/ComedorTO.php'; // ComedorTO.php
require_once dirname(__FILE__) . '/../../includes/ImageReceiver.php';

sec_session_start();

try {
    // Comprobacion CSRF
    if (seteada('auth_token')) {
        $token = obtener('auth_token');
        // 30*60 = 30 minutos desde la carga de la página como máximo
        $comprobacion = comprobarFormToken('editar_info', $token, 30 * 60);
        if ($comprobacion === 0) {
            throw new Exception('Tiempo de token del formulario expirado, inténtelo ahora de nuevo');
        } else if (!$comprobacion) {
            throw new Exception('Ataque CSRF detectado.');
        }
    } else {
        throw new Exception('Ataque CSRF detectado muy duramente.');
    }

    if (!seteada("name")) throw new Exception('No se indicó nombre.');
    $nombre = obtener("name");
    if (!seteada("direccion")) throw new Exception('No se indicó dirección.');
    $direccion = obtener("direccion");

    if (!seteada("selDesde")) throw new Exception('No se indicó inicio de apertura.');
    $ap_ini = obtener("selDesde");
    if (!seteada("selHasta")) throw new Exception('No se indicó fin de apertura.');
    $ap_fin = obtener("selHasta");
    if (!seteada("apertura_horas")) throw new Exception('No se indicó horario de apertura.');
    $ap_horas = obtener("apertura_horas");

    if (!seteada("contact_name")) throw new Exception('No se indicó nombre de contacto.');
    $contacto = obtener("contact_name");
    if (!seteada("tlfn")) throw new Exception('No se indicó teléfono.');
    $tlfn = obtener("tlfn");
    if (!seteada("horario")) throw new Exception('No se indicó horario de comedor.');
    $horario = obtener("horario");

    if (!seteada("promocion")) throw new Exception('No se indicó promoción.');
    $promocion = obtener("promocion");

//    $imgReceiver = new ImageReceiver("imagen");
//    $minReceiver = new ImageReceiver("miniatura");

    if (!login_check()) throw new Exception('No logeado');

    // Obtenemos el Comedor (abstract factory)
    $fabrica = obtenerDAOFactory();
    $dao = $fabrica->obtenerComedoresDAO();
    $comedor = $dao->obtenerComedorTO($_SESSION['id_comedor']);
    unset($fabrica);
    if ($comedor === null) die('El comedor no existe?');

    // Array de variables definidas
    $fields = array();
    if (strcmp($nombre, "") !== 0)
        $comedor->setNombre(htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'));
    if (strcmp($direccion, "") !== 0)
        $comedor->setDireccion(htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8'));

    $apertura = $comedor->getApertura();
    if (preg_match("/lunes|martes|miercoles|jueves|viernes|sabado|domingo/", $ap_ini) === 1) {
        $apertura['dias'][0] = $ap_ini;
    }
    echo $ap_ini;
    if (preg_match("/lunes|martes|miercoles|jueves|viernes|sabado|domingo/", $ap_fin) === 1) {
        $apertura['dias'][1] = $ap_fin;
    }
    echo $ap_fin;
    if (strcmp($ap_horas, "") !== 0) {
        preg_match_all("/(\d{1,2}:\d{1,2}) - (\d{1,2}:\d{1,2})/", $ap_horas, $horis, PREG_PATTERN_ORDER);
        if (count($horis[1]) === 1 && count($horis[2]) === 1) {
            $apertura['horas'][0] = $horis[1][0];
            $apertura['horas'][1] = $horis[2][0];
        }
        unset($horis);
    }
    $comedor->setApertura($apertura);

    if (strcmp($contacto, "") !== 0)
        $comedor->setNombreContacto(htmlspecialchars($contacto, ENT_QUOTES, 'UTF-8'));
    if (preg_match("/\d{3}\s\d{2}\s\d{2}\s\d{2}/", $tlfn) === 1)
        $comedor->setTlfn(str_replace(" ", "", $tlfn));

    $horarioOriginal = $comedor->getHorarioComedor();
    if (strcmp($horario, "") !== 0) {
        preg_match_all("/(\d{1,2}:\d{1,2}) - (\d{1,2}:\d{1,2})/", $horario, $horis, PREG_PATTERN_ORDER);
        if (count($horis[1]) === 1 && count($horis[2]) === 1) {
            $horarioOriginal[0] = $horis[1][0];
            $horarioOriginal[1] = $horis[2][0];
        }
        unset($horis);
    }
    $comedor->setHorarioComedor($horarioOriginal);

    if (strcmp($promocion, "") !== 0)
        $comedor->setPromocion(htmlspecialchars($promocion, ENT_QUOTES, 'UTF-8'));

    // Listo, procedemos a update
    $dao->actualizarComedorTO($comedor);

//    if($imgReceiver->imagenDisponible()) {
//        if($imgReceiver->tipoImagen() === IMAGETYPE_PNG && $imgReceiver->imageSize() < )
//            $imgReceiver->guardarEn(ImageReceiver::IMAGENES, $_SESSION['id']);
//    }
//
//    if($minReceiver->imagenDisponible()) {
//        if($minReceiver->tipoImagen() === IMAGETYPE_PNG && $minReceiver->imageSize() < )
//            $minReceiver->guardarEn(ImageReceiver::MINIATURAS, $_SESSION['id']);
//    }

    header('Location: ../../panel/');
    exit();
} catch (Exception $e) {
    header('Location: ../../panel/?error=' . urlencode($e->getMessage()));
    exit();
}