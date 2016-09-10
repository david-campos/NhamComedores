/*
 * manejoPlatosMenus.js
 * Script de manejo de los platos y menús mostrados en el panel principal
 *
 * David Campos Rodríguez
 */
var divPlatos = null;
var divMenus = null;
var platosEliminados = {};
var platosAgotados = [];

$(document).ready(function(){
    divPlatos = $("#platos");
    divMenus = $("#menus");

    if (divPlatos.length != 1 || divMenus.length != 1) {
        alert("No se ha encontrado el div de platos o el div de menús, se requiren ambos.");
        return;
    }

    // Material-box para la imagen
    $('.materialboxed').materialbox();
    $('.modal-trigger').leanModal({
        complete: function () {
            $('#modEditInfoForm').find("input:not(.select-dropdown),textarea").val("");
        }
    });
    $('select').material_select();

    // Al hacer click en el tab de platos, se muestran los platos y se ocultan los menus
    $("#platos_tab").click(function () {
        if (!$(this).hasClass("active")) {
            divMenus.slideUp("fast", function () {
                divPlatos.delay(100).slideDown("fast");
            });
        }
    });
    // Al hacer click en el tab de menus, se muestran los menus y se ocultan los platos
    $("#menus_tab").click(function () {
        if (!$(this).hasClass("active")) {
            divPlatos.slideUp("fast", function () {
                divMenus.delay(100).slideDown("fast");
            });
        }
    });

    // Los platos se pueden deslizar para eliminar, y clickear en agotar, desagotar o eliminar
    divPlatos.find("li.eliminable").each(function () {
        hacerDeslizable($(this));
        $(this).on("dismissed", function () {
            eliminarPlato($(this))
        });

        $(this).find("a.eliminar").click(function (event) {
            event.preventDefault();
            eliminarPlato($(this).closest("li"));
        }).tooltip({tooltip: 'Eliminar'});
        $(this).find("a.agotar").click(function (event) {
            event.preventDefault();
            agotarPlato($(this).closest("li"), true);
        }).tooltip({tooltip: 'Agotar'});
        $(this).find("a.desagotar").click(function (event) {
            event.preventDefault();
            agotarPlato($(this).closest("li"), false);
        }).tooltip({tooltip: 'Desagotar'});
    });

    var modE = $("#modEditInfo");
    // Máscaras del modal de edición
    modE.find("#tlfn").mask("999 99 99 99");
    modE.find("#apertura_horas").mask("99:99 - 99:99");
    modE.find("#horario").mask("99:99 - 99:99");
    modE.find("input[id],textarea[id],div[data-tooltip]").tooltip();
    modE.find("#btnEditInfoSave").click(handleSaveClick);
});

/**
 * Maneja el click sobre el boton de guardar del modal de editar la información del comedor
 */
function handleSaveClick(e) {
    e.preventDefault();
    $("#modEditInfoForm").submit();
}

/**
 * Agota o desagota el plato, segun valor
 * @param liPlato {JQuery} Elemento li del plato a agotar
 * @param valor {boolean} True para agotar y false para desagotar
 */
function agotarPlato(liPlato, valor) {
    var id = liPlato.attr('data-id');
    platosAgotados.push({id: id, li: liPlato});
    $.post("/mysql/view_agotarPlato.php",
        {'idPlato': id, 'agotado': valor},
        platoAgotado,
        "json");
}

/**
 * Maneja la respuesta del ajax de agotar plato
 * @param data {{status:String,error?:String,respuesta?:{_id:int,agotado}}} Respuesta del php parseada
 */
function platoAgotado(data) {
    if (data.status === "OK") {
        var id = data.respuesta._id;
        var agotado = (data.respuesta.agotado === 'true');
        for (var i = 0; i < platosAgotados.length; i++) {
            if (platosAgotados[i].id == id) {
                var li = platosAgotados[i].li;
                var a = platosAgotados[i].li.find("a.agotar, a.desagotar");

                a.removeClass("desagotar").removeClass("agotar");
                li.attr("data-agotado", agotado ? 1 : 0);

                if (agotado) {
                    a.addClass('desagotar');
                    a.find("i")
                        .removeClass('amber-text')
                        .addClass('light-green-text')
                        .html('check_circle');
                    a.unbind().click(function (event) {
                        event.preventDefault();
                        agotarPlato($(this).closest("li"), false);
                    }).tooltip('remove');
                    a.tooltip({tooltip: 'Desagotar'});
                } else {
                    a.addClass('agotar');
                    a.find("i")
                        .removeClass('light-green-text')
                        .addClass('amber-text')
                        .html('remove_circle');
                    a.unbind().click(function (event) {
                        event.preventDefault();
                        agotarPlato($(this).closest("li"), true);
                    }).tooltip('remove');
                    a.tooltip({tooltip: 'Agotar'});
                }
                platosAgotados.slice(i, 1);
                break;
            }
        }
    } else if (console && console.log) console.log(data.error);
}

/**
 * Cuando es eliminado un plato, consulta si realmente se desea eliminar,
 * si la respuesta es afirmativa se elimina.
 * @param liPlato {JQuery} El li con el plato a eliminar
 */
function eliminarPlato(liPlato) {
    const RESP_ELIMINAR = "Eliminar";
    const RESP_CANCELAR = "Cancelar";
    ModalGenerico.show(
        function (resp) {
            if (resp === RESP_ELIMINAR) {
                    var paramId = liPlato.attr('data-id');
                    var hoy = new Date();
                    var paramFecha = hoy.getFullYear() + "-" + (hoy.getMonth() + 1) + "-" + hoy.getDate();

                    platosEliminados[paramId] = liPlato;

                    liPlato.addClass("amber");

                    $.post("/mysql/view_eliminarPlato.php",
                        {'idPlato': paramId, 'fecha': paramFecha, 'asoc': 'tener'},
                        platoEliminado,
                        "json");
            }
        },
        "¿Realmente desea eliminar el plato '" + liPlato.find("h6").text() + "'?",
        "Eliminar plato",
        [{icon: 'delete', text: RESP_ELIMINAR, color: 'red-text'},
            {text: RESP_CANCELAR}],
        null, {dismissible: false});
}

/**
 * Maneja la respuesta del ajax de eliminar plato
 * @param data {{status:'OK',respuesta:{id_plato:int,fecha:String}}|{status:'ERROR',error:String}} Respuesta de ajax parseada de JSON
 */
function platoEliminado(data) {
    if (data.status == "OK") {
        var parent = platosEliminados[data.respuesta.id_plato].parent();
        platosEliminados[data.respuesta.id_plato].remove();
        if (parent.children().length == 0) {
            parent.append('<li class="collection-item">' +
                '<i class="material-icons red-text left">priority_high</i>' +
                'No sirve usted ningún plato de este tipo hoy. </li>');
        }
        platosEliminados[data.respuesta.id_plato] = null;
    } else {
        alert("FALLO eliminando plato: '" + data.error + "'");
    }
}

/**
 * Materializecss no lanza eventos cuando se hace dismiss de un elemento dismissable,
 * esta función imita la suya para hacer el dismiss pero sí lanza evento.
 *
 * @param $this {JQuery} elemento jquery a hacer deslizable
 */
function hacerDeslizable($this){
    var swipeLeft = false;
    var swipeRight = false;
    // Materializecss incluye la librería Hammer.min.js entre sus archivos...
    if (!$this.hammer) return;
	$this.hammer({
		prevent_default: false
	}).bind('pan', function(e) {
		if (e.gesture.pointerType === "touch") {
			var direction = e.gesture.direction;
			var x = e.gesture.deltaX;
			var velocityX = e.gesture.velocityX;

			$this.velocity({ translateX: x}, {duration: 50, queue: false, easing: 'easeOutQuad'});

			// Swipe Left
			if (direction === 4 && (x > ($this.innerWidth() / 2) || velocityX < -0.75)) {
				swipeLeft = true;
			}

			// Swipe Right
			if (direction === 2 && (x < (-1 * $this.innerWidth() / 2) || velocityX > 0.75)) {
				swipeRight = true;
			}
		}
	}).bind('panend', function(e) {
		// Reset if collection is moved back into original position
        if (Math.abs(e.gesture.deltaX) < ($this.innerWidth() / 2)) {
			swipeRight = false;
			swipeLeft = false;
		}

		if (e.gesture.pointerType === "touch") {
            if ((swipeLeft || swipeRight)) {
				var fullWidth;
				if (swipeLeft) { fullWidth = $this.innerWidth(); }
				else { fullWidth = -1 * $this.innerWidth(); }

                $this.velocity({translateX: fullWidth}, {
					duration: 100,
					queue: false,
					easing: 'easeOutQuad',
					complete: function() {
                        $this.trigger("dismissed");
                        $this.velocity({translateX: 0}, {duration: 1, queue: false});
                        swipeLeft = false;
                        swipeRight = false;
					}
				});
			}
			else {
                $this.velocity({translateX: 0},
					{duration: 100, queue: false, easing: 'easeOutQuad'});
			}
			swipeLeft = false;
			swipeRight = false;
		}
	});
}