<?php
/*
 * Panel de administración de un comedor
 * David Campos Rodríguez
 */

require_once(dirname(__FILE__) . '/../../includes/functions.php');

/* LA PAGINA REQUIERE ESTAR LOGEADO */
if (!login_check()) die("¡Eres un pequeño hackercillo! ¿O qué eres tú? Pillín... e.e");
/* LA PAGINA REQUIERE ESTAR LOGEADO */

include dirname(__FILE__) . '/view_nuevoPlatoForm.php';
include dirname(__FILE__) . '/panel_elements/view_modalNuevoMenu.php';
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

<div class="fixed-action-btn" style="bottom: 24px; right: 24px;">
    <a class="btn-floating btn-large amber">
        <i class="large material-icons">add</i>
    </a>
    <ul>
        <li><a id="menAddLink" class="btn-floating blue-grey" title="Nuevo menú" href="#modNewMenu"><i
                    class="material-icons">menu</i></a></li>
        <li><a class="btn-floating red" onclick="IntroduccionPlatos.abrir();" title="Nuevo plato"><i
                    class="material-icons">all_out</i></a></li>
    </ul>
</div>