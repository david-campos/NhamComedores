<?php
/**
 * Modal de edición de la infomación del comedor
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../../../includes/functions.php';

if (!isset($comedor)) {
    $fabrica = obtenerDAOFactory();
    $dao = $fabrica->obtenerComedoresDAO();

    $comedor = $dao->obtenerComedorTO($_SESSION['id_comedor']);

    unset($dao);
    unset($dabrica);

    if ($comedor === null) die('El comedor asociado a la sesión no existe.');
}
$horario = $comedor->getHorarioComedor();
$strHorario = ComedorTO::sFormatearHora($horario[0]) . ' - ' . ComedorTO::sFormatearHora($horario[1]);
unset($horario);

$apertura = $comedor->getApertura();
$horas = $apertura['horas'];
$strHorasApertura = ComedorTO::sFormatearHora($horas[0]) . ' - ' . ComedorTO::sFormatearHora($horas[1]);
$abre = ComedorTO::sEnNumero($apertura['dias'][0]);
$cierra = ComedorTO::sEnNumero($apertura['dias'][1]);
unset($horas);
unset($apertura);

$dias = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
$dias_val = array("lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo");
?>

<div id="modEditInfo" class="modal modal-fixed-footer">
    <div class="modal-content">
        <form id="modEditInfoForm" action="/mysql/view_editarInfo.php"
              method="post" <?php //enctype="multipart/form-data"?>>
            <div class="row">
                <div class="input-field col s4">
                    <input id="name" name="name" type="text"
                           maxlength="50" length="50"
                           data-tooltip="Introduce el nombre del comedor, p.ej. 'El Bulli'."
                           placeholder="<?php echo $comedor->getNombre(); ?>">
                    <label for="name">Nombre</label>
                </div>
                <div class="input-field col s8">
                    <input id="direccion" name="direccion" type="text"
                           maxlength="120" length="120"
                           data-tooltip="Introduce la dirección del comedor, p.ej. 'Calle Falsa, 123'"
                           placeholder="<?php echo $comedor->getDireccion(); ?>">
                    <label for="direccion">Dirección</label>
                </div>

                <div class="input-field col s1">
                    <label>Apertura:</label>
                </div>
                <div class="input-field col s6" data-tooltip="Primer y último día de la semana de apertura del comedor">
                    <select title="Primer día de apertura de la semana" class="col s6" name="selDesde" id="selDesde">
                        <option disabled>Desde</option>
                        <?php
                        for ($i = 0; $i < 7; $i++) {
                            $selected = ($abre == $i ? 'selected' : '');
                            printf('<option value="%s" %s>%s</option>', $dias_val[$i], $selected, $dias[$i]);
                        }
                        ?>
                    </select>
                    <select title="Último día de apertura de la semana" class="col s6" name="selHasta" id="selHasta">
                        <option disabled>Hasta</option>
                        <?php
                        for ($i = 0; $i < 7; $i++) {
                            $selected = ($cierra == $i ? 'selected' : '');
                            printf('<option value="%s" %s>%s</option>', $dias_val[$i], $selected, $dias[$i]);
                        }
                        ?>
                    </select>
                </div>
                <div class="input-field col s5">
                    <input id="apertura_horas" name="apertura_horas"
                           pattern="[^0-9]*|([01][0-9]|2[0-3]):[0-5][0-9] - ([01][0-9]|2[0-3]):[0-5][0-9]"
                           data-tooltip="Introduce la hora de apertura y la de cierre del comedor, p.ej. '00:00 - 23:59' (¡qué trabajadores!)."
                           type="text" class="validate" placeholder="<?php echo $strHorasApertura; ?>">
                    <label for="apertura_horas">Horario de apertura</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s4">
                    <input id="contact_name" name="contact_name" type="text"
                           maxlength="20" length="20"
                           data-tooltip="Introduce el nombre por el que preguntar al llamar, p. ej. 'Jon Snow'."
                           placeholder="<?php echo $comedor->getNombreContacto(); ?>">
                    <label for="contact_name">Nombre de contacto</label>
                </div>
                <div class="input-field col s4">
                    <input id="tlfn" name="tlfn" type="tel"
                           data-tooltip="Introduce el teléfono del comedor, p.ej. '987 65 43 21'."
                           placeholder="<?php echo $comedor->getTlfn(); ?>">
                    <label for="tlfn">Teléfono</label>
                </div>
                <div class="input-field col s4">
                    <input id="horario" name="horario" data-tooltip="Introduce el horario en que se sirve comida."
                           pattern="[^0-9]*|([01][0-9]|2[0-3]):[0-5][0-9] - ([01][0-9]|2[0-3]):[0-5][0-9]"
                           type="text" class="validate" placeholder="<?php echo $strHorario; ?>">
                    <label for="horario">Horario de comedor</label>
                </div>

                <div class="input-field col s12">
                    <textarea id="promocion" name="promocion" class="materialize-textarea"
                              maxlength="280" length="280"
                              data-tooltip="Introduce un texto promocional que anime a los usuarios a venir a tu comedor."
                              placeholder="<?php echo $comedor->getPromocion(); ?>"></textarea>
                    <label for="promocion">Promoción</label>
                </div>
                <?php /* Eliminado por las vulnerabilidades que causa, cuando tenga más tiempo se añadirá
                <div class="input-field col s6">
                    <div class="file-field input-field">
                        <div class="btn amber white-text">
                            <span>Imagen</span>
                            <input id="imagen" name="imagen" type="file" accept="image/png"
                                   data-tooltip="Solo se admiten imagenes en png, ¡procura que la resolución sea buena!">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text" pattern=".*\.png">
                        </div>
                    </div>
                </div>
                <div class="input-field col s6">
                    <div class="file-field input-field">
                        <div class="btn amber white-text">
                            <span>Miniatura</span>
                            <input id="miniatura" name="miniatura" type="file" accept="image/png"
                                   data-tooltip="No recomendamos cambiarla, asegúrate de que tenga transparencias para que los usuarios puedan ver si tu comedor está sirviendo comida o no en la app.">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text" pattern=".*\.[pP][nN][gG]">
                        </div>
                    </div>
                </div>*/ ?>
            </div>
            <span class="info">Los campos en blanco no se modificarán.</span>

            <?php // Protección CSRF ?>
            <input type="hidden" name="auth_token" value="<?= generarFormToken('editar_info') ?>"/>

        </form>
    </div>
    <div class="modal-footer">
        <a href="#" id="btnEditInfoSave" class="modal-action modal-close waves-effect waves-green btn-flat ">Guardar</a>
        <a href="#" class="modal-action modal-close waves-effect waves-red btn-flat ">Cancelar</a>
    </div>
</div>
