<?php
/**
 * Modal de creación de un nuevo menú
 * @author David Campos R.
 */

require_once dirname(__FILE__) . '/../../../includes/model/ElementosMenu.php';

?>
<div id="modNewMenu" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4>Nuevo menú</h4>
        <form class="col s12" method="post" action="/mysql/view_nuevoMenu.php">
            <div class="row">
                <div class="input-field col s6">
                    <input placeholder="p.ej. 'Menú individual'" name="name" id="nmName" type="text">
                    <label for="name">Nombre</label>
                </div>
                <div class="input-field col s6">
                    <input title="Precio del menú" id="nmPrecio" name="precio" type="number" min="0" max="50"
                           step="0.01">
                    <label for="precio">Precio</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <select title="Selector de elementos del menú" id="selAddElementos">
                        <option value="0" disabled selected>Elige el elemento</option>
                        <?php
                        $elementos = obtenerTodosLosElementos();
                        foreach ($elementos as $elemento) {
                            printf('<option value="%d">%s</option>', $elemento['_id'], $elemento['nombre']);
                        }
                        ?>
                    </select>
                    <label>Añadir elemento</label>
                </div>
            </div>
            <div class="row">
                <div class="chipsElementos"></div>
            </div>
            <div class="error"></div>

            <?php // Protección CSRF ?>
            <input type="hidden" name="auth_token" value="<?= generarFormToken('nuevo_menu') ?>"/>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" id="modNewMenuSave" class="waves-effect waves-green btn-flat ">Guardar</a>
        <a href="#" class="modal-action modal-close waves-effect waves-red btn-flat ">Cencelar</a>
    </div>
</div>
