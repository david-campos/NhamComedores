<?php
/**
 * Diseño de la lista de platos
 * @author David Campos R.
 */

require_once dirname(__FILE__).'/../../../includes/model/Tener.php';

$icons = array('restaurant_menu', 'restaurant', 'free_breakfast');
$names = array('Primeros', 'Segundos', 'Postres');

$platos = obtenerPlatosServidos($_SESSION['id_comedor'], date('Y-m-d'));
?>
<ul class="collapsible" data-collapsible="expandable">
    <?php
    for ($i = 0; $i < 3; $i++) {
        ?>
        <li>
            <div class="collapsible-header">
                <?php printIcon($icons[$i], "amber"); ?>
                <?php echo $names[$i]; ?>
            </div>
            <div class="collapsible-body">
                <ul class="collection">
                <?php
                for($j=0; $j<count($platos[$i]); $j++) {
                    $plato = $platos[$i][$j];
                    ?>
                    <li class="collection-item eliminable" data-agotado='<?php echo $plato['agotado']; ?>'
                        data-id='<?php echo $plato['_id']; ?>'>
                         <h6 class='nombre'> <?php echo $plato['nombre']; ?> </h6>
                         <span class='descripcion'><?php  echo $plato['descripcion']; ?></span>
                        <a href="#" class="secondary-content red-text eliminar"><i class="material-icons">delete</i></a>
                        <?php if ($plato['agotado']) { ?>
                            <a href="#" class="secondary-content light-green-text desagotar"><i class="material-icons">check_circle</i></a>
                        <?php } else { ?>
                            <a href="#" class="secondary-content amber-text agotar"><i class="material-icons">remove_circle</i></a>
                        <?php } ?>
                     </li>
                    <?php
                }
                if(count($platos[$i]) == 0) {
                    ?>
                    <ul class="collection">
                        <li class="collection-item">
                            <?php printIcon('priority_high', 'red', null, null, 'left'); ?>
                            No sirve usted ningún plato de este tipo hoy.
                        </li>
                    </ul>
                    <?php
                }
                ?>
                </ul>
            </div>
        </li>
        <?php
    }
    ?>
</ul>
