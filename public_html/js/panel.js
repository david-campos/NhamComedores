/*
 * manejoPlatosMenus.js
 * Script de manejo de los platos y menús mostrados en el panel principal
 *
 * David Campos Rodríguez
 */
var divPlatos = null;
var divMenus = null;
var platosEliminados = {};

$(document).ready(function(){
    divPlatos = $("#platos");
    divMenus = $("#menus");

    if (divPlatos.length != 1 || divMenus.length != 1) {
        alert("No se ha encontrado el div de platos o el div de menús, se requiren ambos.");
        return;
    }

    // Material-box para la imagen
	$('.materialboxed').materialbox();

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

    // Los platos se pueden deslizar para eliminar
    divPlatos.find("li.eliminable").each(function () {
        hacerDeslizable($(this));
        $(this).on("dismissed", function () {
            eliminarPlato($(this))
        });
        $(this).find("a").click(function () {
            eliminarPlato($(this).closest("li"));
        });
    });
});

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
            switch (resp) {
                case RESP_ELIMINAR:
                    var paramId = liPlato.attr('data-id');
                    var hoy = new Date();
                    var paramFecha = hoy.getFullYear() + "-" + (hoy.getMonth() + 1) + "-" + hoy.getDate();

                    platosEliminados[paramId] = liPlato;

                    liPlato.addClass("amber");

                    $.post("/mysql/view_eliminarPlato.php",
                        {'idPlato': paramId, 'fecha': paramFecha, 'asoc': 'tener'},
                        platoEliminado,
                        "json");
                    break;
                case RESP_CANCELAR:
                    break;
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
 * @param data {{status:'OK',respuesta:{id_plato:int,fecha:String}}} Respuesta de ajax parseada de JSON
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