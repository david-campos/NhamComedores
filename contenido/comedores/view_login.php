<?php
/*
 * Estructura del formulario de login de la página. Se incluye, en la versión
 * actual, en acceso.php
 * Incluye un JavaScript de login que codifica la contraseña antes de enviarla
 *
 * David Campos Rodríguez
 */
?>
<script language="JavaScript" type="text/javascript" src="/js/login.js"></script>
<script language="JavaScript" type="text/javascript" src="/js/sha512.js"></script>
<div class="row valign login">
	<h4>Login</h4>
	<form class="col s12" action="/mysql/login.php<?php
		if( isset($_GET['c']) )
			echo '?c='.addslashes(htmlentities(urlencode($_GET['c'])));
	?>" method="post" id="formularioLogin">
		<div class="row">
			<div class="input-field col s12">
				<i class="material-icons prefix">account_box</i>
				<input id="loginName" type="text" name="loginName" style="font-size: xx-large;">
				<label for="loginName">Login</label>
			</div>
		</div>
		<div class="row">
			<div class="input-field col s12">
				<i class="material-icons prefix">credit_card</i>
				<input id="password" type="password" name="codigo" style="font-size: xx-large;">
				<label for="password">Código</label>
			</div>
		</div>
		<div class="row">
			<div class="input-field col s12">
				<div class="switch">
					<label>
						<span>Recordar sesión</span>
						<input type="checkbox" name="recordar_sesion">
						<span class="lever"></span>
					</label>
				</div>
				<button class="btn waves-effect waves-light amber right" type="submit" name="action">
					Enviar
					<i class="material-icons right">send</i>
				</button>
			</div>
		</div>
	</form>
</div>

<div class="row valign cargando center">
	<div class="preloader-wrapper big active">
		<div class="spinner-layer spinner-blue">
			<div class="circle-clipper left">
				<div class="circle"></div>
			</div><div class="gap-patch">
				<div class="circle"></div>
			</div><div class="circle-clipper right">
				<div class="circle"></div>
			</div>
		</div>
		
	  <div class="spinner-layer spinner-red">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>

      <div class="spinner-layer spinner-yellow">
        <div class="circle-clipper left">
          <div class="circle"></div>
        </div><div class="gap-patch">
          <div class="circle"></div>
        </div><div class="circle-clipper right">
          <div class="circle"></div>
        </div>
      </div>

      <div class="spinner-layer spinner-green">
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
