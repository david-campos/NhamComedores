<?php
/**
 * Modal de confirmación de una eliminación
 * @author David Campos R.
 */ ?>
<div id="modDelConfirm" class="modal">
    <div class="modal-content">
        <h4>¿Eliminar?</h4>
        <p>¿Realmente deseas eliminar '<span id="mdcNombreEliminado"></span>'?</p>
        <p id="mdcDescripcionEliminado"></p>
    </div>
    <div class="modal-footer">
        <a href="#" class="modal-action modal-close waves-effect btn-flat">
            <i class="material-icons prefix red-text ajustado">delete</i>Eliminar
        </a>
        <a href="#" class="modal-action modal-close waves-effect btn-flat">Cancelar</a>
    </div>
</div>