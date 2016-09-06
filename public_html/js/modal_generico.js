/**
 * Script que provee una interfaz cómoda para poder crear un modal con el texto
 * y las opciones de resultado deseados de forma sencilla (mediante materializecss).
 */

//  <div id="modDelConfirm" class="modal">
//      <div class="modal-content">
//          <h4>¿Eliminar?</h4>
//          <p>¿Realmente deseas eliminar '<span id="mdcNombreEliminado"></span>'?</p>
//          <p id="mdcDescripcionEliminado"></p>
//      </div>
//      <div class="modal-footer">
//          <a href="#" class="modal-action modal-close waves-effect btn-flat">
//              <i class="material-icons prefix red-text ajustado">delete</i>Eliminar
//          </a>
//          <a href="#" class="modal-action modal-close waves-effect btn-flat">Cancelar</a>
//     </div>
//  </div>

// Declaracion de un "namespace" al estilo de Enterprise JQuery
(function (ModalGenerico, $, undefined) {
    /**
     * Muestra el modal con el título, texto y respuestas indicadas
     * @param handleResponse {function(respuesta: String)} Handler para manejar la respuesta
     * @param textoHtml {String} Texto del modal en formato HTML
     * @param [titulo] {String|null} Título a mostrar en el modal
     * @param [respuestas] {[{text:String,icon?:String,color?:String}]|null} Opciones de respuesta
     * @param [style] {String|null} Permite hacer el modal fixed-footer o bottom-sheet
     * @param [openOptions] {LeanModalOptions} Opciones de apertura del modal
     */
    ModalGenerico.show = function (handleResponse, textoHtml, titulo, respuestas, style, openOptions) {
        if (!textoHtml) return;
        if (titulo === undefined || titulo === null) titulo = "";
        if (respuestas === undefined || respuestas === null || respuestas.length == 0) respuestas = [{text: "Aceptar"}, {text: "Cancelar"}];

        _crearModal();
        _modal.handler = handleResponse;
        _modal.titulo.text(titulo);
        _modal.html.html(textoHtml);
        _setOpciones(respuestas);
        _setStyle(style);
        _modal.all.openModal(openOptions);
    };

    /**
     * Representación abstracta del modal
     * @type {{all:JQuery,titulo:JQuery,html:JQuery,footer:JQuery,handler:function(String)}}
     * @private
     */
    var _modal = {};

    /**
     * Cambia las opciones de que dispone el modal
     * @param opciones {[{text:String,icon?:String,color?:String}]|null} Nuevas opciones de respuesta
     * @private
     */
    function _setOpciones(opciones) {
        if (_modal.footer !== undefined && opciones !== null && opciones.length > 0) {
            _modal.footer.empty();
            opciones.forEach(function (opcion) {
                var nuevoA = $("<a></a>")
                    .addClass("waves-effect btn-flat");
                nuevoA.append($("<span></span>").text(opcion.text));
                if (opcion.color)
                    nuevoA.addClass(opcion.color);
                if (opcion.icon)
                    nuevoA.prepend($("<i></i>")
                        .addClass("material-icons prefix ajustado")
                        .html(opcion.icon));
                nuevoA.click(_handleOptionClick);
                _modal.footer.append(nuevoA);
            });
        }
    }

    /**
     * Cambia el estilo del modal
     * @param style {String|null} Hacer el modal fixed-footer o bottom-sheet
     * @private
     */
    function _setStyle(style) {
        if (_modal.all) {
            _modal.all.removeClass('modal-fixed-footer');
            _modal.all.removeClass('bottom-sheet');
            if (style) {
                switch (style) {
                    case 'fixed-footer':
                        _modal.all.addClass('modal-fixed-footer');
                        break;
                    case 'bottom-sheet':
                        _modal.all.addClass('bottom-sheet');
                        break;
                }
            }
        }
    }

    /**
     * Manejador del click en una opción
     * @private
     */
    function _handleOptionClick() {
        var respuesta = $(this).find("span").text();
        _modal.all.closeModal();
        if (_modal.handler) {
            _modal.handler(respuesta);
        }
    }

    /**
     * Crea el modal si no está creado
     * @private
     */
    function _crearModal() {
        if (!_modal.all) {
            _modal.titulo = $("<h4></h4>");
            _modal.html = $("<div></div>");
            _modal.footer = $("<div></div>").addClass("modal-footer");
            _modal.all = $("<div></div>")
                .addClass("modal")
                .append($("<div></div>")
                    .addClass("modal-content")
                    .append(_modal.titulo)
                    .append(_modal.html))
                .append(_modal.footer);
            $("body").append(_modal.all);
        }
    }
}(window.ModalGenerico = window.ModalGenerico || {}, jQuery)); // Fin del "namespace"