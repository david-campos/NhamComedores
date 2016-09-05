<?php
/**
 * Los comedores pueden administrar sus platos desde aquí.
 * David Campos Rodríguez
 * 31/08/2016
 */

// Archivo de funciones necesarias
require_once dirname(__FILE__) . "/../../includes/util/mis-platos.php";

define("PAGINADO_KEY", "pag");
define("PAGE_SIZE", 10);

$page = obtener(PAGINADO_KEY, FILTER_SANITIZE_NUMBER_INT) or $page = 0;

/* Este archivo debe ser cargado en index.php, en el main del html. */
if( login_check() ) {
	include_once dirname(__FILE__)."/view_nuevoPlatoForm.php";
    ?>
    <script language="JavaScript" src="/js/mis-platos.js" async></script>
    <div class="row">
        <table class="striped tablaMisPlatos">
            <thead><tr>
                <th data-field="nombre">Nombre</th>
                <th data-field="descripcion">Descripción</th>
                <th data-field="tipo" colspan="2">Tipo</th>
            </tr></thead>
            <tbody><?php printListaMisPlatos($page, PAGE_SIZE, $total); ?></tbody>
            <tfoot><tr>
                <td colspan="2" class="left-align">
                    <?php printPaginacionMisPlatos($page, PAGE_SIZE, $total); ?>
                </td>
                <td colspan="2" class="right-align">
                    <a class="btn amber" id="btnAgregar"><i class="material-icons left">add</i>Agregar</a>
                </td>
            </tr></tfoot>
        </table>
    </div>
    <?php
} else {
    ?>
    ¡Eres un pequeño hackercillo! ¿o qué eres tú? Pillín... e.e
    <?php
}
?>
