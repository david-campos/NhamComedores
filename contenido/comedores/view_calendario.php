<?php
/**
 * Estructura del calendario, manejado por javascript, que permite a los comedores
 * programar sus platos con antelación.
 *
 * @author David Campos Rodríguez
 */

/* Este archivo debe ser cargado en index.php, en el main del html. */
if (login_check()) {
    require_once dirname(__FILE__) . "/../../includes/api-core.php";
    require_once dirname(__FILE__) . "/../../includes/model/MisPlatos.php";
    ?>

    <?php // Protección CSRF ?>
    <input type="hidden" name="token_eliminar_plato" value="<?= generarFormToken('eliminar_plato') ?>"/>

    <!-- Modal de listar platos -->
    <div id="modPlatos" class="modal modal-fixed-footer">
        <div class="modal-content">
        </div>
        <div class="modal-footer">
            <a id="calPlatosClose" class="modal-action modal-close waves-effect btn-flat">Cerrar</a>
            <a id="calPlatosOpen" class="waves-effect waves-light btn amber">Añadir plato</a>
        </div>
    </div>

    <?php
    // Modal de insertar platos
    require_once dirname(__FILE__) . "/view_nuevoPlatoForm.php";
    ?>

    <div class="row" id="rCalendario" style="display:none;">
        <table id="tCalendario" class="calendario centered">
            <thead>
            <tr>
                <th><?php // Mis platos iran aqui ?></th>
                <th id="calYear" colspan="7">Año de prueba</th>
            </tr>
            <tr>
                <th><?php // Mis platos iran aqui ?></th>
                <th id="calMonth" colspan="5">Mes de prueba</th>
                <th id="calBefore"><a class="btn light-green disabled"><i class="material-icons">navigate_before</i></a>
                </th>
                <th id="calNext"><a class="btn light-green disabled"><i class="material-icons">navigate_next</i></a>
                </th>
            </tr>
            <tr>
                <th class="misPlatos"><a class="btn amber white-text"><i
                            class="material-icons">keyboard_arrow_up</i></a></th>
                <th>L</th>
                <th>Ma</th>
                <th>Mi</th>
                <th>J</th>
                <th>V</th>
                <th>S</th>
                <th>D</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td class="misPlatos"><a class="btn amber white-text"><i class="material-icons">keyboard_arrow_down</i></a>
                </td>
                <td colspan="7">
                    <div class="progress light-green lighten-3" style="display: none;">
                        <div class="indeterminate light-green"></div>
                    </div>
                </td>
            </tr>
            </tfoot>
            <tbody>
            <?php
            // Generamos el cuerpo del calendario
            for ($i = 0; $i < 6; $i++) {
                echo "<tr>";
                echo "<td class='ranuraMisPlatos'></td>";
                for ($j = 0; $j < 7; $j++) {
                    echo "<td class='calDia' id='cal" . ($i * 7 + $j) . "'><i class='material-icons tachar'>close</i><i class='material-icons platos'>restaurant_menu</i><span></span></td>";
                }
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="row s12" id="acciones"></div>
    <div class="row" id="control-acciones">
        <div class="col s6 left-align">
            <a class="btn-floating red" style="display:none" id="undo"><i class="material-icons">undo</i></a>
        </div>
        <div class="col s6 right-align">
            <a class="btn-floating light-green" style="display:none" id="redo"><i class="material-icons">redo</i></a>
        </div>
    </div>

    <?php /*Cargando (mientras no descarga el script)*/ ?>
    <div id="rCargando" class="row valign center">
        <div class="preloader-wrapper big active">
            <div class="spinner-layer spinner-blue">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="gap-patch">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>

            <div class="spinner-layer spinner-red">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="gap-patch">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>

            <div class="spinner-layer spinner-yellow">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="gap-patch">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>

            <div class="spinner-layer spinner-green">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div>
                <div class="gap-patch">
                    <div class="circle"></div>
                </div>
                <div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    ?>
    ¡Eres un pequeño hackercillo! ¿o qué eres tú? Pillín... e.e
    <?php
}
?>
