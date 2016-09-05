<?php
/**
 * Lista de menús, aspecto.
 * @author David Campos R.
 */

$menus = obtenerMenus($_SESSION['id_comedor']);
?>
<ul class="collection">
    <?php
    for ($i = 0; $i < count($menus); $i++) {
        $menu = &$menus[$i];
        ?>
        <li class="collection-item">
            <?php
            printf("<span class='menu-title'>%s (%.2f€): </span>", $menu['nombre'], $menu['precio']);
            printf("<span class='menu-elements'>%s</span>", listarElementos($menu['elementos']));
            ?>
        </li>
        <?php
    }
    ?>
</ul>