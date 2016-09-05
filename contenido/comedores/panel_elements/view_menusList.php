<?php
/**
 * Lista de menús, aspecto.
 * @author David Campos R.
 */

require_once dirname(__FILE__).'/../../../includes/model/TiposMenu.php';
require_once dirname(__FILE__).'/../../../includes/model/ElementosMenu.php';

$icons = array(
    'plato'=>'timelapse',
    'bebida'=>'local_drink',
    'extra'=>'local_offer');

$menus = obtenerMenus($_SESSION['id_comedor']);
?>
<table class="bordered">
<?php
for ($i = 0; $i < count($menus); $i++) {
    $menu = &$menus[$i];
    ?>
    <tr class="menu">
        <td>
            <?php
            printf("<span class='menu-title'>%s (%.2f€): </span>", $menu['nombre'], $menu['precio']);
            ?>
        </td>
        <td>
            <?php
            $elementos = obtenerElementos($menu['_id']);
            for($j=0; $j < count($elementos); $j++) {
                $elemento = &$elementos[$j];
                ?>
                <div class='chip'>
                    <?php printIcon($icons[$elemento['tipo']], 'amber', null, null, 'ajustado'); ?>
                    <?php echo $elemento['nombre']; ?>
                </div>
                <?php
            }
            ?>
        </td>
    </tr>
    <?php
}
if(count($menus) == 0) {
    ?>
    <tr>
        <td>
            <?php printIcon('warning', 'red', null, null, 'left'); ?>
            No se ha configurado ningún menú. Debería comenzar a configurar sus menús pronto.
        </td>
    </tr>
    <?php
}
?>
</table>
