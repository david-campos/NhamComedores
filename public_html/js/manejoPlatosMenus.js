/*
 * manejoPlatosMenus.js
 * Script de manejo de los platos y menús mostrados en el panel principal
 *
 * David Campos Rodríguez
 */
var platos;
var menus;

$(document).ready(function(){
    // Al hacer click en el tab de platos, se muestran los platos y se ocultan los menus
        $("#platos_tab").click(function(){
            if(! $("#platos_tab").hasClass("active") ) {
                $("#menus").slideUp("fast", function() {
                    $("#platos").delay(100).slideDown("fast");
                });
            }
        });

    // Al hacer click en el tab de menus, se muestran los menus y se ocultan los platos
        $("#menus_tab").click(function(){
            if(! $("#menus_tab").hasClass("active") ) {
                $("#platos").slideUp("fast", function(){
                    $("#menus").delay(100).slideDown("fast");
                });
            }
        });

// 	$("#platos  li.dismissable a.dismisser").click(eliminarLi);
// 	$("#menus li.dismissable a.dismisser").click(eliminarLi);
//
// 	$("a.modBtnEliminar").click(function(){ $(this).closest(".modal").attr("result", "eliminar");});
// 	$("a.modBtnCancelar").click(function(){ $(this).closest(".modal").attr("result", "cancelar");});
//
// 	setTimeout(comprobarPlatosMenus, 1000);
// 	platos = $("#platos li.dismissable");
// 	menus = $("#menus li.dismissable");
});


// Los elementos generados dinámicamente, dejan de ser deslizables (por desgracia)
//por lo que empleamos esta función para reaplicar las transiciones.
//Es casi una copia literial del código de materializecss
function hacerDeslizable($this){
	$this.hammer({
		prevent_default: false
	}).bind('pan', function(e) {
		if (e.gesture.pointerType === "touch") {
			var $this = $(this);
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
		if (Math.abs(e.gesture.deltaX) < ($(this).innerWidth() / 2)) {
			swipeRight = false;
			swipeLeft = false;
		}

		if (e.gesture.pointerType === "touch") {
			var $this = $(this);
			if (swipeLeft || swipeRight) {
				var fullWidth;
				if (swipeLeft) { fullWidth = $this.innerWidth(); }
				else { fullWidth = -1 * $this.innerWidth(); }

				$this.velocity({ translateX: fullWidth,}, {
					duration: 100,
					queue: false,
					easing: 'easeOutQuad',
					complete: function() {
						$this.css('border', 'none');
						$this.velocity({ height: 0, padding: 0,}, {
							duration: 200,
							queue: false,
							easing: 'easeOutQuad',
							complete: function() { $this.remove(); }
						});
					}
				});
			}
			else {
				$this.velocity({ translateX: 0,},
					{duration: 100, queue: false, easing: 'easeOutQuad'});
			}
			swipeLeft = false;
			swipeRight = false;
		}
	});
}

// Elimina un elemento, se llama al hacer click en el icono correspondiente
function eliminarLi(){
	$(this).closest("li.dismissable").slideUp(function(){$(this).remove();});
}

// Comprueba si se ha eliminado algún plato... y tu te preguntarás ¿por qué?
//Yo te diré por qué: Materializecss, al menos al momento de escribir estas líneas,
//¡no lanza ningún evento cuando has arrastrado y eliminado un elemento! Indignante,
//¿verdad? Lo mismo pensé yo, por lo que he hecho un fork al proyecto de GitHub,
//he añadido el maldito evento, y he hecho push-request. Han pasado de mí. Genial.
function comprobarPlatosMenus() {
	var nuevosPlatos = $("#platos li.dismissable");
	if( platos.not(nuevosPlatos).length != 0 ) {
		$this = platos.not(nuevosPlatos).first();
		
		// Preparamos el modal y lo abrimos
		$('#modDelPlato #platoEliminado').text($this.find(".nombre").text());
		$('#modDelPlato #descripcionEliminado').text($this.find(".descripcion").text());
		$("#modDelPlato").attr("result", "cancelar");
		
		$('#modDelPlato').openModal({
			dismissible: false,
			complete: function() {
				if($("#modDelPlato").attr("result") != "eliminar") {
					pos = $this.attr("pos");
					tipo = $this.attr("tipo");
					var elem = null;
					$("#platos"+tipo).children().each(function(index, elemento) {
						if($(this).attr("pos") < pos)
							elem = $(this);
						else
							return false;
					});
					if( elem == null) {
						$("#platos"+tipo).prepend($this);
					} else {
						elem.after($this);
					}
					$this.find("a.dismisser").click(eliminarLi);
					$this.removeAttr("style");
					$this.css("touch-action", "pan-y");
					$this.css("-webkit-user-drag", "none");
					$this.css("-webkit-tap-highlight-color", "rgba(0, 0, 0, 0)");
					
					// Reestablecemos el deslizamiento que perdió...
					hacerDeslizable($this);
					setTimeout(comprobarPlatosMenus, 1000);
				} else {
					// Plato borrado
					var idPlato = $this.attr('platoid');
					$.post(
						"mysql/eliminarPlato.php",
						{'idPlato': idPlato},
						function( data ) {
							if( data == "0")
								setTimeout(comprobarPlatosMenus, 1000);
							else
								alert("FALLO eliminando plato: '"+data+"'");
						});
					platos = platos.not($this);
				}
			}
		});
	} else {
		// Si los platos no han cambiado, menús, y si no... reesperamos
		var nuevosMenus = $("#menus li.dismissable");
		if( menus.not(nuevosMenus).length != 0 ) {
			$this = menus.not(nuevosMenus).first();
			
			// Preparamos el modal y lo abrimos
			$("#modDelMenu").attr("result", "cancelar");
			$("#modDelMenu #menuEliminado").text($this.find(".menNombre").text());
			$("#modDelMenu #precioEliminado").text($this.find(".menPrecio").text());
			$('#modDelMenu').openModal({
				dismissible: false,
				complete: function() {
					if($("#modDelMenu").attr("result") != "eliminar") {
						pos = $this.attr("pos");
						tipo = $this.attr("tipo");
						var elem = null;
						$("#menus ul").children().each(function(index, elemento) {
							if($(this).attr("pos") < pos)
								elem = $(this);
							else
								return false;
						});
						if( elem == null) {
							$("#menus ul").prepend($this);
						} else {
							elem.after($this);
						}
						$this.find("a.dismisser").click(eliminarLi);
						$this.removeAttr("style");
						$this.css("touch-action", "pan-y");
						$this.css("-webkit-user-drag", "none");
						$this.css("-webkit-tap-highlight-color", "rgba(0, 0, 0, 0)");
						
						// Reestablecemos el deslizamiento que perdió...
						hacerDeslizable($this);
					} else {
						// Menu borrado
						var idMenu = $this.attr('menuid');
						$.post("/mysql/eliminarMenu.php",
							{'idMenu': idMenu},
							function( data ) {
								if( data == "0")
									setTimeout(comprobarPlatosMenus, 1000);
								else
									alert("FALLO eliminando menu: '"+data+"'");
							});
						menus = menus.not($this);
					}
					setTimeout(comprobarPlatosMenus, 1000);
				}
			});
		} else {
			setTimeout(comprobarPlatosMenus, 600);
		}
	}
}