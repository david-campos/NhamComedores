<?php
/*
 * funcionesInicio.php
 * Contiene algunas funciones útiles para view_inicio.php.
 * 
 *
 * Lorenzo Vaquero Otal
 * 13/08/2016
 */

include_once(dirname(__FILE__) . '/panel.php');

function normalizaHora($hora) { // Para que al obtener las horas de la API no aparezcan los segundos
	if(empty($hora)){
		return('--:--');
	} else {
		$horaNormalizada = "";
		$contador = 0;
		for($i = 0; $i < strlen($hora); ++$i) {
			if($hora[$i] == ':'){
				if($contador == 0){
					++$contador;
					$horaNormalizada .= $hora[$i];
				}else{
					return($horaNormalizada);
				}
			}else{
				$horaNormalizada .= $hora[$i];
			}
		}
		return($horaNormalizada);
	}
}

function comedorAbierto($diaInicioApertura, $diaFinApertura){
	$diaActual = new DateTime("now", new DateTimeZone('Europe/Madrid') ); // Para que no se muestre el día del servidor
	$diaActual = date_format($diaActual,'w');
	
	--$diaActual; // Dado que se cuenta el Domingo como 0
	if($diaActual < 0){
		$diaActual = 6;
	}
	
	return (enPlazo($diaActual, aNumero($diaInicioApertura), aNumero($diaFinApertura)));
}

function comparaHoras($horaA, $horaB){
	$stringHA = "";
	$stringMA = "";
	for($i = 0; $i < strlen($horaA); ++$i) {
		if($horaA[$i] == ':'){
			break;
		}else{
			$stringHA .= $horaA[$i];
		}
	}
	$horasA = intval($stringHA);
	for(++$i; $i < strlen($horaA); ++$i) {
		if($horaA[$i] == ':'){
			break;
		}else{
			$stringMA .= $horaA[$i];
		}
	}
	$minutosA = intval($stringMA);
	
	$stringHB = "";
	$stringMB = "";
	for($i = 0; $i < 2; ++$i) {
		if($horaB[$i] == ':'){
			break;
		}else{
			$stringHB .= $horaB[$i];
		}
	}
	$horasB = intval($stringHB);
	for(++$i; $i < 5; ++$i) {
		if($horaB[$i] == ':'){
			break;
		}else{
			$stringMB .= $horaB[$i];
		}
	}
	$minutosB = intval($stringMB);
	
	if($horasA > $horasB){
		return 1;
	} else if ($horasA < $horasB){
		return -1;
	} else {
		if($minutosA > $minutosB){
			return 1;
		} else if($minutosA < $minutosB){
			return -1;
		}else {
			return 0;
		}
	}
}

function enHorarioComedor($horaInicio, $horaFin, $diaInicioApertura, $diaFinApertura){
	$horaActual = new DateTime("now", new DateTimeZone('Europe/Madrid') ); // Para que no se muestre la hora del servidor
	$horaActual = date_format($horaActual,'H:i:s');
	
	if(comparaHoras($horaActual,$horaFin) >= 0 && comparaHoras($horaActual,$horaFin) <= 0 && comedorAbierto($diaInicioApertura, $diaFinApertura)){
		return true;
	} else{
		return false;
	}
}
?>