<?php
/**
 * Diseño de la lista de platos
 * @author David Campos R.
 */

$icons = array('restaurant_menu', 'restaurant', 'free_breakfast');
$names = array('Primeros', 'Segundos', 'Postres');
?>
<ul class="collapsible" data-collapsible="accordion">
    <?php
    for ($i = 0; $i < 3; $i++) {
        ?>
        <li>
            <div class="collapsible-header">
                <?php printIcon($icons[$i], "amber"); ?>
                <?php echo $names[$i]; ?>
            </div>
            <div class="collapsible-body">
                <? imprimirPlatos(); ?>
            </div>
        </li>
        <?php
    }
    ?>
</ul>
