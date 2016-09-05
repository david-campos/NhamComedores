<?php
/*
 * view_inicio.php
 * Página principal, que muestra los comedores y su disponibilidad
 * 
 *
 * Lorenzo Vaquero Otal
 * 12/08/2016
 */
	include_once(dirname(__FILE__) . '/../includes/api-core.php');
	include_once(dirname(__FILE__) . '/../includes/util/inicio-funciones.php');
	
	$lineas = obtenerLineas(COMEDORES,null); // Obtenemos los comedores a mostrar
	
	?>
		<!DOCTYPE html>
		<html>
	<?php
	
	if( $lineas['status'] == 'OK' ) { // Comprobamos si existe algún tipo de error con la API al traer la información
		$comedores = $lineas['respuesta']; // Si todo ha ido bien, $comedores tendrá el valor de la respuesta
		?>
			<div id="contenido" class="row">
				<ul class="collection" id="listaComedores">
					<?php
						for($i = 0; $i < count($comedores); ++$i) {
							if(enHorarioComedor($comedores[$i]['horaInicio'], $comedores[$i]['horaFin'], $comedores[$i]['hAperturaIni'], $comedores[$i]['hAperturaFin'])){
								?>
									<li class="collection-item avatar light-green" idComedor="<?php echo $comedores[$i]['_id']; ?>">
								<?php
							}else{
								?>
									<li class="collection-item avatar" idComedor="<?php echo $comedores[$i]['_id']; ?>">
								<?php
							}
							$rutaImagen = 'api/miniaturas/mini_' . $comedores[$i]['_id'] . '.png';
							if(file_exists ( $rutaImagen )){
								echo '<img src="/api/miniaturas/'.$comedores[$i]['_id'].'" alt="" class="circle">';
							}else{
								?>
									<i class="material-icons circle">restaurant</i>
								<?php
							}
							?>
							<span class="title"><h6><?php echo $comedores[$i]['nombre']; ?></h6></span>
							<p>Apertura: <?php echo normalizaHora($comedores[$i]['hAperturaIni']) ?>  -  <?php echo normalizaHora($comedores[$i]['hAperturaFin']) ?> | Comedor: <?php echo normalizaHora($comedores[$i]['horaInicio']) ?> - <?php echo normalizaHora($comedores[$i]['horaFin']) ?>
							</p>
							</li>
							<?php
						}
					?>
				</ul>
				
				
				<div id="detallesComedor" hidden>
					<div class="parallax-container">
						<div class="parallax" id="imagenComedor"><img src="/api/imagenes/1"></div>
					</div>
					<div class="section">
						<div class="row">
							<div class="col s5 center-align">
								<p><b>Horario de apertura</b><br>12:00 - 16:00</p>
								<p><b>Horario de comedor</b><br>12:00 - 16:00</p>
							</div>
							<div class="col s5 push-s2 center-align">
								<p><br>626111453 (Antonio)</p>
								<p>Campus Vida sin número<br></p>
							</div>
						</div>
						
						<div class="divider"></div>
						
						<div class="section">
							<h5>Primeros</h5>
							<div class="row white" id="primeros">
								&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<b>Ensalada</b><br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDe pepino en el colegio femenino
								<br><br>
								&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<b>Ensalada</b><br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDe pepino en el colegio femenino
							</div>
							<h5>Segundos</h5>
							<div class="row white" id="segundos">
								&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<b>Ensalada</b><br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDe pepino en el colegio femenino
								<br><br>
								&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<b>Ensalada</b><br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDe pepino en el colegio femenino
							</div>
							<h5>Postres</h5>
							<div class="row white" id="postres">
								&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<b>Ensalada</b><br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDe pepino en el colegio femenino
								<br><br>
								&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<b>Ensalada</b><br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspDe pepino en el colegio femenino
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
	} else { // Si algo ha ido mal, se muestra un error
		?>
			<div class="container">
			<div class="card-panel light-green">
				<h4 class="center-align"><span class="white-text">Se ha producido un error</p></span></h4>
				<h5 class="center-align"><span class="flow-text white-text">Por favor, vuelva a intentarlo más adelante.</span></h5>
			</div>
			</div>
		<?php
	}
?>

</html>

<script language="JavaScript" src="/js/comedores.js"></script>
