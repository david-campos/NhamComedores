<?php
/*
 * Panel de administración de un comedor
 * David Campos Rodríguez
 */

require_once(dirname(__FILE__) . '/../../includes/util/panel.php');
require_once(dirname(__FILE__) . '/../../includes/functions.php');

/* LA PAGINA REQUIERE ESTAR LOGEADO */
if (!login_check()) die("¡Eres un pequeño hackercillo! ¿O qué eres tú? Pillín... e.e");
/* LA PAGINA REQUIERE ESTAR LOGEADO */

include_once dirname(__FILE__) . "/panel_elements/view_modalConfirmarEliminacion.php";

// Utiliza el script manejoPlatosMenus
simple_script_include("/js/manejoPlatosMenus.js");

?>
<div class="row center"><img class="responsive-img z-depth-1" src="/api/imagenes/detail_1.png"></div>

<div class="row">
    <ul class="tabs">
        <li class="tab col s6"><a href="#" class="active" id="platos_tab">Platos</a></li>
        <li class="tab col s6"><a href="#" id="menus_tab">Menús</a></li>
    </ul>
    <div id="platos">
        <?php include dirname(__FILE__).'/panel_elements/view_platosList.php'; ?>
    </div>
    <div id="menus" class="z-depth-1">
        <?php include dirname(__FILE__).'/panel_elements/view_menusList.php'; ?>
    </div>
</div>

<div class="row">

</div>
