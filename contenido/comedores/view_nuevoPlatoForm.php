<?php
/*
 * view_nuevoPlatoForm.php
 * Archivo de estructura del modal para introducir un nuevo plato. Este modal
 * conlleva un script en JavaScript que mediante AJAX provee un autocompletado
 * de los platos introducidos. Esto, además de ahorrar tiempo al usuario,
 * permite reaprovechar los platos de unos comedores en otros, reduciendo
 * así el espacio utilizado en la base.
 *
 * David Campos Rodríguez
 */
?>
<!-- Modal de nuevo plato -->
<div id="modNuevoPlato" class="modal modal-fixed-footer">
	<div class="modal-content">
		<form class="col s12" autocomplete="off">
			<div class="row">
				<div class="input-field col s8">
					<input id="nombreNuevo" type="text" maxlength="50" length="50">
					<label for="nombreNuevo">Nombre</label>
				</div>
				<div class="input-field col s1">
					<div id="loading" class="preloader-wrapper small active">
						<div class="spinner-layer spinner-yellow-only">
						  <div class="circle-clipper left">
							<div class="circle"></div>
						  </div><div class="gap-patch">
							<div class="circle"></div>
						  </div><div class="circle-clipper right">
							<div class="circle"></div>
						  </div>
						</div>
					</div>
				</div>
				<div class="input-field col s3">
					<select id="tipoNuevo">
						<option value="0" selected>Primero</option>
						<option value="1">Segundo</option>
						<option value="2">Postre</option>
					</select>
					<label>Tipo</label>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<textarea id="descripcionNuevo" placeholder="Procura que no sea muy larga" class="materialize-textarea" maxlength="140" length="140"></textarea>
					<label for="descripcionNuevo">Descripción</label>
				</div>
			</div>
			<div class="row">
				<p id="modalError" class="red lighten-4 red-text"></p>
				<p id="modalExito" class="light-green lighten-4 light-green-text"></p>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<a id="nuevoPlatoCerrar" class="waves-effect btn-flat">Cerrar</a>
		<a id="nuevoPlatoAnhadir" class="waves-effect waves-light btn amber">Añadir</a>
	</div>
</div>
<script language="JavaScript" src="/js/formulario_nuevoplato.js"></script>
