<?php
/**
 * Panel que muestra la información del comedor, se sitúa en la parte inferior del panel
 * @author David Campos Rodríguez
 */

require_once dirname(__FILE__) . "/../../../includes/model/ComedorTO.php"; // ComedorTO.php
require_once dirname(__FILE__) . "/../../../includes/model/DAO.php"; // Fabrica

// Obtenemos el Comedor (abstract factory)
$fabrica = obtenerDAOFactory();
$dao = $fabrica->obtenerComedoresDAO();

$comedor = $dao->obtenerComedorTO($_SESSION['id_comedor']);

unset($dao);
unset($fabrica);

if ($comedor === null) die('El comedor no existe?');

include dirname(__FILE__) . "/view_modalEditarInfo.php";
?>
<div class="row">
    <div class="etiqueta col s2 amber lighten-1 white-text">
        <a class="modal-trigger waves-effect btn-flat waves-light white-text" href="#modEditInfo">Editar</a>
    </div>
    <div class="col s12 amber lighten-1">
        <ul class="collection z-depth-1">
            <li class="collection-item">
                <div class="row center" id="dispImg">
                    <img class="materialboxed responsive-img z-depth-1" data-caption="Imagen del comedor"
                         src="<?php echo $comedor->getImagen(); ?>">
                </div>
            </li>

            <li class="collection-item">
                <h5 class="amber-text"><i class="material-icons prefix ajustado">grade</i>Promoción</h5>
                <p id="dispPromo"><?php echo $comedor->getPromocion(); ?></p>
            </li>
            <li class="collection-item">
                <div class="row">
                    <div class="col s12 m6">
                        <h5 class="amber-text">
                            <i class="material-icons prefix ajustado">perm_identity</i>Nombre</h5>
                        <span id="dispName"><?php echo $comedor->getNombre(); ?></span>
                    </div>
                    <div class="col s12 m6">
                        <h5 class="amber-text"><i class="material-icons prefix ajustado">location_on</i>Dirección</h5>
                        <span><?php echo $comedor->getDireccion(); ?></span>
                    </div>
                </div>
            </li>
            <li class="collection-item">
                <div class="row">
                    <div class="col s12 m6" id="dispApertura">
                        <h5 class="amber-text"><i class="material-icons prefix ajustado">watch_later</i>Apertura</h5>
                        <?php echo $comedor->getAperturaEnHtml(); ?>
                    </div>
                    <div class="col s12 m6">
                        <h5 class="amber-text"><i class="material-icons prefix ajustado">restaurant_menu</i>Horario de
                            comedor
                        </h5>
                        <p id="dispHorario"><?php echo $comedor->getHorarioComedorEnHtml(); ?></p>
                    </div>
                </div>
            </li>
            <li class="collection-item">
                <div class="row">
                    <div class="col s12 m6">
                        <h5 class="amber-text"><i class="material-icons prefix ajustado">account_box</i>Nombre de
                            contacto
                        </h5>
                        <span id="dispContacto">
                    <?php
                    if ($comedor->getNombreContacto() !== null)
                        echo $comedor->getNombreContacto();
                    else
                        echo 'No indicado';
                    ?>
                </span>
                    </div>
                    <div class="col s12 m6">
                        <h5 class="amber-text"><i class="material-icons prefix ajustado">call</i>Teléfono</h5>
                        <span id="dispTlfn"><?php echo $comedor->getTlfn(); ?></span>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>