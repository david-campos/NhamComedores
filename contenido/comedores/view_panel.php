<?php
	/*
	 * Panel de administración del comedor, estructura. A esta p�gina solo
	 * se puede tener acceso si se est� logeado. El contenido de este fichero
	 * ser� cargado, en la versi�n actual, en acceso.php...
	 * En un futuro tal vez esta direcci�n cambie.
	 *
	 * David Campos Rodr�guez
	 */
	/* Este archivo debe ser cargado en acceso.php, en el main del html. */
	require dirname(__FILE__)."/../../includes/db_connect.php";

	if( login_check() ) {
		$consulta = $mysqli->query("SELECT * FROM Comedores WHERE _id = '".$_SESSION['id_comedor']."' LIMIT 1");
		if ($linea = $consulta->fetch_assoc()) {
			require(dirname(__FILE__) . '/../../includes/util/panel-util.php');
?>

	<!-- Modal de eliminar plato -->
	<div id="modDelPlato" class="modal">
		<div class="modal-content">
			<h4>¿Eliminar plato?</h4>
			<p>¿Realmente deseas eliminar este plato: <span id="platoEliminado"></span>?</p>
			<p id="descripcionEliminado"></p>
		</div>
		<div class="modal-footer">
			<a href="#!" class="modBtnEliminar modal-action modal-close waves-effect btn-flat"><i class="material-icons prefix red-text ajustado">delete</i>Eliminar</a>
			<a href="#!" class="modBtnCancelar modal-action modal-close waves-effect btn-flat">Cancelar</a>
		</div>
	</div>
	<!-- Modal de eliminar menú -->
	<div id="modDelMenu" class="modal">
		<div class="modal-content">
			<h4>�Eliminar men�?</h4>
			<p>�Realmente deseas eliminar este men�: <span id="menuEliminado"></span>(<span id="precioEliminado"></span>)?</p>
		</div>
		<div class="modal-footer">
			<a href="#!" class="modBtnEliminar modal-action modal-close waves-effect btn-flat"><i class="material-icons prefix red-text ajustado">delete</i>Eliminar</a>
			<a href="#!" class="modBtnCancelar modal-action modal-close waves-effect btn-flat">Cancelar</a>
		</div>
	</div>
	<!-- Modal nuevo elemento -->
	<div id="modAdd" class="modal">
		<div class="modal-content">
			<h4>A�adir nuevo elemento</h4>
			<form>
				<div class="row">
					<div class="input-field col s6">
						<input id="nombreNuevo" type="text" class="validate">
						<label for="nombreNuevo">Nombre</label>
					</div>
					<div class="input-field col s12">
						<textarea id="descripcionNuevo" placeholder="Procura que no sea muy larga" class="materialize-textarea"></textarea>
						<label for="descripcionNuevo">Descripci�n</label>
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<a href="#!" class="modBtnEngadir modal-action modal-close waves-effect waves-green btn-flat">A�adir</a>
			<a href="#!" class="modBtnCancelar modal-action modal-close waves-effect waves-red btn-flat">Cancelar</a>
		</div>
	</div>
	
	<div class="row">
	</div>
	
	<div class="row center">
	<img class="responsive-img z-depth-1" src="/api/imagenes/<?=$linea['_id']; ?>/">
	</div>

	<div class="row">
    <div class="col s12">
      <ul class="tabs">
        <li class="tab col s6"><a href="#" class="active" id="platos_tab">Platos</a></li>
        <li class="tab col s6"><a href="#" id="menus_tab">Men�s</a></li>
      </ul>
    </div>
	</div>
	<div class="row" id="platos">
		<?php
			// Obtenemos los platos correspondientes a hoy :D
			$fechaHoy = date("Y-m-d");
			$respPlatos = $mysqli->query("SELECT _id, nombre, descripcion, tipo, t.agotado ".
											"FROM Platos p INNER JOIN Tener t ON (p._id = t.id_plato) ".
											"WHERE id_comedor = '".$linea['_id']."' AND fecha = '$fechaHoy'"); 
			$platos = array();
			while( $plato = $respPlatos->fetch_assoc() ) {
				$platos[] = $plato;
			}
		?>
		<ul class="collapsible" data-collapsible="accordion">
			<li>
				<div class="collapsible-header"><i class="material-icons amber-text">restaurant_menu</i>Primeros<a class="addPlato primero secondary-content amber-text"><i class="material-icons anhadir">add</i></a></div>
				<div class="collapsible-body"> <? imprimirCollectionPlatos($platos, "0"); ?> </div>
			</li>
			<li>
				<div class="collapsible-header"><i class="material-icons amber-text">restaurant</i>Segundos<a class="addPlato segundo secondary-content amber-text"><i class="material-icons anhadir">add</i></a></div>
				<div class="collapsible-body"> <? imprimirCollectionPlatos($platos, "1"); ?> </div>
			</li>
			<li>
				<div class="collapsible-header"><i class="material-icons amber-text">free_breakfast</i>Postres<a class="addPlato postre secondary-content amber-text"><i class="material-icons anhadir">add</i></a></div>
				<div class="collapsible-body"> <? imprimirCollectionPlatos($platos, "2"); ?> </div>
			</li>
		</ul>
	</div>
	<div class="row" id="menus">
		<?php
			// Obtenemos los menus correspondientes
			$consulta = "SELECT _id, nombre, precio ".
											"FROM TiposMenu ".
											"WHERE id_comedor = '".$linea['_id']."'";
			$respMenus = $mysqli->query($consulta);
			imprimirCollectionMenus($respMenus);
		?>
	</div>
	<!-- Script de manejo de los platos y men�s -->
	<script language="JavaScript" src="/js/manejoPlatosMenus.js"></script>
	
	<div class="row">
		<ul class="collection z-depth-1">
			<li class="collection-item">
			<div class="row">
				<div class="col s12 m6">
					<h5 class="amber-text"><i class="material-icons prefix ajustado">perm_identity</i>Nombre</h5> <span><?=$linea['nombre']; ?></span>
				</div>
				<div class="col s12 m6">
					<h5 class="amber-text"><i class="material-icons prefix ajustado">location_on</i>Direcci�n</h5> <span><?=$linea['direccion']; ?></span>
				</div>
			</div>
			</li>
			<li class="collection-item">
			<div class="row">
				<div class="col s12 m6">
					<h5 class="amber-text"><i class="material-icons prefix ajustado">watch_later</i>Apertura</h5>
					<p><?=apertura($linea['diaInicioApertura'], $linea['diaFinApertura']); ?></p>
					<p><?=formatearHora($linea['hAperturaIni']); ?> - <?=formatearHora($linea['hAperturaFin']); ?></p>
				</div>
				<div class="col s12 m6">
					<h5 class="amber-text"><i class="material-icons prefix ajustado">restaurant_menu</i>Horario de comedor</h5>
					<p><?=formatearHora($linea['horaInicio']); ?> - <?=formatearHora($linea['horaFin']); ?></p>
				</div>
			</div>
			</li>
			<li class="collection-item">
				<h5 class="amber-text"><i class="material-icons prefix ajustado">grade</i>Promoci�n</h5>
				<p><?=$linea['promocion']; ?></p>
			</li>
			<li class="collection-item">
			<div class="row">
				<div class="col s12 m6">
					<h5 class="amber-text"><i class="material-icons prefix ajustado">account_box</i>Nombre de contacto</h5> <span><?=$linea['nombreContacto']; ?></span>
				</div>
				<div class="col s12 m6">
					<h5 class="amber-text"><i class="material-icons prefix ajustado">call</i>Tel�fono</h5> <span><?=$linea['telefono']; ?></span>
				</div>
			</div>
			</li>
		</div>
	</div>
<?php
		} else {
?>
	<div class="row valign">
	Ha habido alg�n tipo de error al cargar el panel de control para el comedor logeado: '<?=$sesionValidada ?>'
	</div>
<?php
		}
	} else {
?>
	�Eres un peque�o hackercillo! �o qu� eres t�? Pill�n... e.e
<?php
	}
?>
