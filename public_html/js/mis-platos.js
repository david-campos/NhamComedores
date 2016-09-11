/**
 * Created by David Campos Rodríguez on 31/08/2016.
 */

// Declaracion de un "namespace" al estilo de Enterprise JQuery
(function( MisPlatos, $, undefined ) {
    const URL_ELIMINAR = "/mysql/view_eliminarPlato.php";
    const URL_MODIFICAR = "/mysql/view_modificarPlato.php";

    $(document).ready(function () {
        $(document).on(IntroduccionPlatos.READY_EVENT, function () {
            IntroduccionPlatos.fijarModo(IntroduccionPlatos.MODOS.MIS_PLATOS);
            $(".tablaMisPlatos #btnAgregar").click(function () {
                IntroduccionPlatos.abrir()
            });
            $(".tablaMisPlatos a.borrarMiPlato").click(function (e) {
                borrarPlato(e);
            });
            $(".tablaMisPlatos tbody td").dblclick(function (e) {
                editarPlato(e);
            });
            $(document).keyup(function (event) {
                if (event.key == "Escape") {
                    cancelarModificacionPlato();
                }
                if (event.key == "Enter") {
                    guardarModificacionPlato(event);
                }
            });
            IntroduccionPlatos.alCerrar(platosInsertados);
            IntroduccionPlatos.multiplato(true);
        });
    });

    /**
     * tr del plato en edición
     * @type {JQuery|null}
     */
    var trPlatoEditado = null;
    /**
     * Plato editado si es que se está editando alguno
     * @type {{_id:int,nombre:String,descripcion:String,tipo:String}|null}
     */
    var platoEditado = null;

    /**
     * Maneja la recepción de la introducción de nuevos platos.
     */
    var platosInsertados = function(){
        // Recargamos, pues la lista es algo compleja y se genera por php,
        // si tengo más tiempo después podría pasar esto y lo demás a AJAX
        location.reload();
    };

    /**
     * Handler para la acción de clickado sobre el enlace para borrar un plato de MisPlatos
     * @param event {JQueryEventObject} el evento de jQuery
     */
    var borrarPlato = function(event){
        event.preventDefault();

        var tr = $(event.currentTarget).closest("tr");
        var id_plato = tr.attr("idPlato");
        $.post(URL_ELIMINAR,
            {'idPlato': id_plato, 'asoc': 'MisPlatos'},
            function(data){
                if(data.status) {
                    if(data.status == "OK") {
                        tr.remove();
                    }else {
                        alert("No se ha podido eliminar de mis platos el plato requerido.");
                        if(console && console.log) console.log(data.error);
                    }
                } else
                    alert("Ha habido algún error.");
            },
            'json');
    };

    /**
     * Handler para el doble click sobre un campo de un plato de MisPlatos, edita el plato
     * @param event {JQueryEventObject} el evento de jQuery
     */
    var editarPlato = function(event) {
        event.preventDefault();
        var td = $(event.currentTarget);
        var tr = td.closest("tr");
        if (trPlatoEditado === tr) return;
        if( platoEditado !== null ) {
            cancelarModificacionPlato();
        }
        _ponerTrEnEdicion(tr, td);
    };

    /**
     * Handler para el click sobre el boton de guardar que se genera cuando editas un plato
     * @param event {JQueryEventObject} el evento de jQuery
     */
    var guardarModificacionPlato = function(event) {
        event.preventDefault();

        if(platoEditado === null || trPlatoEditado === null)
            return;

        var tds = trPlatoEditado.find("td");
        var nuevoNombre = tds.eq(0).find("input").val();
        var nuevaDescripcion = tds.eq(1).find("textarea").val();
        var nuevoTipo = tds.eq(2).find("select").val();
        if(nuevoNombre !== undefined && nuevoNombre !== null && nuevoNombre !== "") {
            platoEditado.nombre = nuevoNombre.trim().replace(/[^a-z0-9\s]/gi, "");
        }
        if(nuevaDescripcion !== undefined && nuevaDescripcion !== null && nuevaDescripcion !== "") {
            platoEditado.descripcion = nuevaDescripcion.trim().replace(/[^a-z0-9\s]/gi, "");
        }
        if(nuevoTipo !== undefined && nuevoTipo !== null) {
            nuevoTipo = nuevoTipo.replace(/[^0-2]/g, "");
            if(nuevoTipo !== "") {
                platoEditado.tipo = nuevoTipo;
            }
        }
        platoEditado.tipo += "desc";
        $.post(URL_MODIFICAR,
            platoEditado,
            platoGuardado,
            "json").fail(guardandoFail);
        platoEditado.tipo = platoEditado.tipo.substr(0,1);
        cancelarModificacionPlato(true);
    };

    /**
     * Maneja si falla el guardado de un plato
     */
    var guardandoFail = function(data) {
        console.log(data);
        Materialize.toast($("<div></div>")
            .append("<p>Ha habido un fallo guardando alg&uacute;n plato, lo sentimos.</p>")
            .append("<p>Aconsejamos recargar la p&aacute;gina para comprobar qu&eacute; cambios se han guardado.</p>"),
            8000);
        $(".tablaMisPlatos tr.amber-text").removeClass("amber-text").addClass("error");
    };

    /**
     * Procesa la respuesta de AJAX sobre si el plato ha sido guardado o no.
     * @param data {{status:String,respuesta:{},error:String|undefined}}
     */
    var platoGuardado = function(data){
        if(data.status) {
            var tr;
            if(data.respuesta && data.respuesta._id) {
                tr = $(".tablaMisPlatos tr[idPlato=" + data.respuesta._id + "]");
            }
            switch(data.status) {
                case "OK":
                     if(tr) tr.removeClass("amber-text");
                    break;
                case "ERROR":
                    if(tr) {
                        tr.removeClass("amber-text").addClass("error");
                        tr.tooltip({tooltip:"El plato no ha podido ser guardado :("});
                    }
                    if(console && console.log)
                        console.log("platoGuardado: error", data);
                    break;
                default:
                    if(console && console.log)
                        console.log("platoGuardado: la respuesta contiene un status desconocido", data);
                    break;
            }
        } else {
            if(console && console.log)
                console.log("platoGuardado: la respuesta no contiene status", data);
        }
    };

    /**
     * Cancela la modificación del plato que se esté modificando actualmente, dejando sus valores como estaban
     * @param [pendienteDeAprobacion] {boolean} Indica que el tr está pendiente de la confirmación del servidor
     */
    var cancelarModificacionPlato = function(pendienteDeAprobacion) {
        if(platoEditado === null || trPlatoEditado === null)
            return;
        if(pendienteDeAprobacion === undefined)
            pendienteDeAprobacion = false;

        var tds = trPlatoEditado.find("td");
        tds.eq(0).empty().text(platoEditado.nombre).removeAttr("style");
        tds.eq(1).empty().text(platoEditado.descripcion).removeAttr("style");
        tds.eq(2).empty()
            .removeAttr("style")
            .attr("tipo", platoEditado.tipo)
            .text(formatoTipo(platoEditado.tipo));
        tds.eq(3).empty()
            .append('<a href="#" class="borrarMiPlato"><i class="material-icons red-text">delete</i></a>')
            .find("a.borrarMiPlato").click(borrarPlato);

        if(pendienteDeAprobacion)
            trPlatoEditado.addClass("amber-text");

        platoEditado = trPlatoEditado = null;
    };

    /**
     * Pone el tr en edición, esto es: convierte sus td's en campos editables y pone un botón de guardar
     * @param tr {JQuery} tr a poner en edición
     * @param tdFocused td que se ha seleccionado
     * @private
     */
    var _ponerTrEnEdicion = function(tr, tdFocused) {
        if(platoEditado !== null || trPlatoEditado !== null)
            return;

        trPlatoEditado = tr;

        var id = tr.attr("idPlato");
        var tds = tr.find("td");
        var tdNombre = tds.eq(0);
        var nombre = tdNombre.text();
        var tdDescripcion = tds.eq(1);
        var descripcion = tdDescripcion.text();
        var tdTipo = tds.eq(2);
        var tipo = tdTipo.attr("tipo");

        platoEditado = {_id:id,nombre:nombre,descripcion:descripcion,tipo:tipo};

        tdNombre.empty()
            .append($("<input>")
                .attr("maxlength", 50)
                .attr("length", 50)
                .attr("type", "text")
                .attr("placeholder", nombre))
            .append($("<label></label>"))
            .css("vertical-align", "bottom");
        tdDescripcion.empty()
            .append($("<textarea></textarea>")
                .addClass("materialize-textarea")
                .attr("maxlength", 140)
                .attr("length", 140)
                .attr("placeholder", descripcion))
            .append($("<label></label>"))
            .css("padding-bottom", "9.5px")
            .css("vertical-align", "bottom");
        tdTipo.empty()
            .append($("<select></select>")
                .append($("<option></option>")
                    .attr("value", 0)
                    .text("Primero"))
                .append($("<option></option>")
                    .attr("value", 1)
                    .text("Segundo"))
                .append($("<option></option>")
                    .attr("value", 2)
                    .text("Postre"))
                .val(tipo))
            .css("vertical-align", "bottom");
        tdTipo.find("select").material_select();
        tds.eq(3).empty().append($("<a></a>")
            .attr("href", "#")
            .click(guardarModificacionPlato)
            .append($("<i></i>")
                .addClass("small material-icons light-green-text")
                .html("save"))
        );
        tdFocused.find("input,textarea").focus();
    };

    /**
     * Formatea el tipo para que se adapte al formato legible
     * @param tipo {String} El identificativo del tipo
     * @return {String} El tipo en formato legible
     */
    var formatoTipo = function(tipo) {
        switch(tipo) {
            case "0": return "Primero";
            case "1": return "Segundo";
            case "2": return "Postre";
            default: return "Desconocido";
        }
    }
}( window.MisPlatos = window.MisPlatos || {}, jQuery )); // Fin del "namespace"