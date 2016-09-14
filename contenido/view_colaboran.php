<?php
 /*
  * Archivo de agradecimiento a todos aquellos que han hecho posible este proyecto
  *
  * David Campos Rodríguez
  * 07/08/2016
  */

$colaboradores = array(
	array(
		"nombre" => "David Campos Rodríguez",
		"rol" => "Idea original y director del proyecto, desarrollo de gran parte de la web, API y app.",
		"perfiles" => array(
			"Linked-In" => "https://www.linkedin.com/in/david-campos-rodr%C3%ADguez-02240b112",
			"GitHub" => "https://github.com/david-campos"),
		"foto" => "/img/david.png"),
	array(
		"nombre" => "María Luz Mosteiro",
		"rol"=>"Colaboración en el desarrollo de la idea inicial y desarrollo de la interfaz original en la app para móvil.",
		"abandono"=>"27/07/2016",
		"perfiles" => array(
			"Linked-In" => "https://www.linkedin.com/in/marialuzmosteiro",
			"GitHub" => "https://github.com/marymost22/"),
		"foto"=>"https://media.licdn.com/media/AAEAAQAAAAAAAAl3AAAAJDk1OWYxYWQ0LTA1ZjYtNDY3Ni05ZTM1LWRkMDQ5ZWM0N2ZjNQ.jpg"),
);
?>
<div class="row">
	
	<h2>Colaboradores</h2>
	<div class="card-panel hoverable white">
		<div class="slider white">
			<ul class="slides white">
<?php
	for($i=0; $i<count($colaboradores); ++$i):
		$clbd = $colaboradores[$i];
?>
				<li class="valign white">
					<div class="row">
						<div class="col hide-on-small-only m4 offset-m1">
							<img class="circle responsive-img" src="<?=$clbd['foto']?>">
						</div>
						<div class="col s12 m7">
							<h2 class="amber-text hide-on-med-and-down"><?=$clbd['nombre']?></h2>
							<h4 class="amber-text hide-on-large-only"><?=$clbd['nombre']?></h4>
							<p><?=$clbd['rol']?></p>
<?php
		if( isset($clbd['inicio']) ):
?>
							<p class="light-green-text" style="font-style: italic;">
								Se unió al proyecto el <?=$clbd['inicio']?>
							</p>
<?php
		endif;
		if( isset($clbd['abandono']) ):
?>
							<p class="light-green-text" style="font-style: italic;">
									Abandonó el proyecto el
									<?=$clbd['abandono']?>
							</p>
<?php
		endif;
		foreach ($clbd['perfiles'] as $key => $val):
?>
							<p><a target="_blank" class="grey-text"
								href="<?=$val?>">
									Ir al perfil de <?=$key?></a>
							</p>
<?php
		endforeach;
		if ( isset($clbd['email']) ):
?>
							<p class="grey-text">Correo: <a target="_blank" 
								href="mailto:<?=$clbd['email']?>"><?=$clbd['email']?></a>
<?php
		endif;
?>
						</div>
					</div>
				</li>
<?php
	endfor;
?>
			</ul>
		</div>
	</div>
</div>
<script language="JavaScript">
$(document).ready(function() {
      $('.slider').slider({full_width: true, interval: 10000});
});
</script>
