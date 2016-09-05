<?php
    $pag = obtenerPaginaActual()->id;
?>
<li <?php echo ($pag == "panel")?'class="active"':''; ?>>
	<a href="/panel/">Panel</a></li>
<li <?php echo ($pag == "calendario")?'class="active"':''; ?>>
	<a href="/calendario/">Calendario</a></li>
<li <?php echo ($pag == "misplatos")?'class="active"':''; ?>>
	<a href="/misplatos/">Mis platos</a></li>
	
<li><a href="/mysql/logout.php">Cerrar sesión</a></li>
