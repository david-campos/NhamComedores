<?php
/**
 * Archivo de utilidades varias para la generación del panel de administración
 * de los comedores fundamentalmente.
 *
 * @author David Campos Rodríguez
 */

/**
 * Formatea la hora, retirando los segundos.
 *
 * @param string $hora La hora en formato 'hh:mm:ss'
 * @return string La hora en formato 'hh:mm'
 */
function formatearHora($hora) {
    return substr($hora, 0, strrpos($hora, ':'));
}

/**
 * Obtiene el html de los días de apertura formateados.
 *
 * A partir de los días de apertura y cierre entregados por la API esta
 * función obtiene los siete días de la semana ('L Ma Mi J V S D') en spans
 * asignados con clases 'abierto' o 'cerrado' según el comedor esté abierto
 * ese día o no.
 *
 * @see enPlazo Para las cadenas de entrada admitidas
 *
 * @param string $diaApertura Día de inicio de la apertura del comedor
 * @param string $diaCierre Día de fin de la apertura del comedor
 * @return string Cadena en formato HTML dispuesta para mostrarse
 */
function apertura($diaApertura, $diaCierre) {
    $ap = aNumero($diaApertura);
    $ci = aNumero($diaCierre);
    $dias = array('L','Ma','Mi','J','V','S','D');
    $ret = "";
    for( $i = 0; $i < 7; $i++ ) {
        $ret .= "<span class='dia ";
        if( enPlazo($i, $ap, $ci) ) {
            $ret .= "abierto";
        } else {
            $ret .= "cerrado";
        }
        $ret .= "'>".$dias[$i]."</span>";
    }
    return $ret;
}

/**
 * Comprueba si el día de la semana indicado se encuentra entre dos días
 * dados, de forma circular.
 *
 * Dados tres días de la semana, de forma numérica o en cadena, comprueba
 * si el primero se encuentra entre los otros dos.
 *
 * @see enPlazo Para las cadenas de entrada admitidas
 *
 * @param integer|string $dia Día de la semana a comparar con los otros dos
 * @param integer|string $numAp Día de la semana que inicia el intervalo de comparación
 * @param integer|string $numCi Día de la semana que finaliza el intervalo de comparación
 * @return bool TRUE si el día se encuentra entre los dos días dados, FALSE en caso contrario
 */
function enPlazo($dia, $numAp, $numCi) {
    if( !is_numeric($dia) ) $dia = aNumero($dia);
    if( !is_numeric($numAp) ) $numAp = aNumero($numCi);
    if( !is_numeric($numCi) ) $numCi = aNumero($numCi);

    if($numAp < $numCi) {
        return $numAp <= $dia && $dia <= $numCi;
    } else {
        return $numAp <= $dia || $dia <= $numCi;
    }
}

/**
 * Convierte el día de la semana devuelto por la API a su número.
 *
 * Convierte una cadena de carácteres válida al número de día de la semana
 * correspondiente, comenzando en cero.
 * Las cadenas aceptadas son:
 * - lunes (0)
 * - martes (1)
 * - miercoles (2)
 * - jueves (3)
 * - viernes (4)
 * - sabado (5)
 * - domingo (6)
 * En caso de que la cadena no sea válida devolverá -1.
 *
 * @param string $dia Un día de la semana
 * @return integer Su número correspondiente, comenzando en 0, o -1 en caso de
 *     cadena inválida.
 */
function aNumero($dia) {
    switch($dia) {
        case 'lunes':
            return 0;
        case 'martes':
            return 1;
        case 'miercoles':
            return 2;
        case 'jueves':
            return 3;
        case 'viernes':
            return 4;
        case 'sabado':
            return 5;
        case 'domingo':
            return 6;
        default:
            return -1;
    }
}