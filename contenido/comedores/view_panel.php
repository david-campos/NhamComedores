<?php
/*
 * Panel de administración de un comedor
 * David Campos Rodríguez
 */

require_once(dirname(__FILE__) . '/../../includes/functions.php');

/* LA PAGINA REQUIERE ESTAR LOGEADO */
if (!login_check()) die("¡Eres un pequeño hackercillo! ¿O qué eres tú? Pillín... e.e");
/* LA PAGINA REQUIERE ESTAR LOGEADO */

// Utiliza el script manejoPlatosMenus
simple_script_include("/js/panel.js");
simple_script_include("/js/modal_generico.js");

?>

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

<?php include dirname(__FILE__) . '/panel_elements/view_comedorInfo.php'; ?>
