<?php
/*
 * view_inicio.php
 * Página principal, que muestra los comedores y su disponibilidad
 * 
 *
 * David Campos Rodríguez
 */
include_once(dirname(__FILE__) . '/../includes/api-core.php');
include_once(dirname(__FILE__) . '/../includes/model/ComedorTO.php');

$lineas = obtenerLineas(COMEDORES, null); // Obtenemos los comedores a mostrar

if ($lineas['status'] == 'OK') { // Comprobamos si existe algún tipo de error con la API al traer la información
    $comedores = $lineas['respuesta'];
    ?>
    <div id="contenido" class="row">
        <ul class="collection" id="listaComedores">
            <?php
            foreach ($comedores as $comedor) {
                if ($comedor->enHorarioDeComedor()) {
                    ?>
                    <li class="collection-item avatar light-green lighten-4" data-id="<?php echo $comedor->getId(); ?>">
                    <?php
                } else {
                    ?>
                    <li class="collection-item avatar" data-id="<?php echo $comedor->getId(); ?>">
                    <?php
                }
                if ($comedor->tieneMiniatura()) {
                    echo '<img src="' . $comedor->getMiniatura() . '" alt="" class="circle">';
                } else {
                    ?>
                    <i class="material-icons circle">restaurant</i>
                    <?php
                }
                ?>
                <span class="title"><?php echo $comedor->getNombre(); ?></span>
                <div>Apertura: <?php echo $comedor->getAperturaEnHtml(); ?> </div>
                <div>Horario de comedor: <?php echo $comedor->getHorarioComedorEnHtml(); ?></div>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
    <?php
} else { // Si algo ha ido mal, se muestra un error
    ?>
    <div class="container">
        <div class="card-panel light-green">
            <h4 class="center-align"><span class="white-text">Se ha producido un error</p></span></h4>
            <h5 class="center-align"><span
                    class="flow-text white-text">Por favor, vuelva a intentarlo más adelante.</span></h5>
        </div>
    </div>
    <?php
}
?>

<script language="JavaScript" src="/js/comedores.js"></script>
