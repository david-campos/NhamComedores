<?php
/**
 * Lista de menús, aspecto.
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../../../includes/model/DAO.php';
require_once dirname(__FILE__) . '/../../../includes/model/TipoMenuTO.php';
require_once dirname(__FILE__) . '/../../../includes/model/ElementosMenu.php';

$icons = array(
    'plato' => 'timelapse',
    'bebida' => 'local_drink',
    'extra' => 'local_offer');

$fabrica = obtenerDAOFactory();
$dao = $fabrica->obtenerTiposMenuDAO();
$menus = $dao->obtenerMenus($_SESSION['id_comedor']);
unset($fabrica);
unset($dao);
?>
<table class="bordered">
    <?php
    for ($i = 0; $i < count($menus); $i++) {
        $menu = &$menus[$i];
        ?>
        <tr class="menu" data-id="<?php echo $menu->getId(); ?>">
            <td>
                <a class="btn-flat borrar"><i class="material-icons red-text">delete</i></a>
                <span class='menu-title'>
                    <?php printf("%s (%.2f€): ", $menu->getNombre(), $menu->getPrecio()); ?>
                </span>
            </td>
            <td class="right-align">
                <?php
                $elementos = obtenerElementos($menu->getId());
                for ($j = 0; $j < count($elementos); $j++) {
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
    if (count($menus) == 0) {
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
