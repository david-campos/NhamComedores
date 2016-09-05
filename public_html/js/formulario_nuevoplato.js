/*
 * formNuevoPlato.js
 * Script que controla el formulario de nuevo plato, especialmente el
 * autocompletado.
 * Siento la falta de orientación a objetos mostrada, no parecía necesaria :)
 *
 * David Campos Rodríguez
 * 03/08/2016
 */

// Declaracion de un "namespace" al estilo de Enterprise JQuery
(function( IntroduccionPlatos, $, undefined ) {
	/* Parte publica */
	/**
	 * Abre el modal para introducir nuevo plato.
	 *
	 * @param {string|Date} [nuevaFecha] - Fecha a asignar al nuevo plato
	 */
	IntroduccionPlatos.abrir = function( nuevaFecha ) {
        prepararModal();
        if( nuevaFecha ) IntroduccionPlatos.fecha(nuevaFecha);
		modal.openModal({dismissible: false});
	};
	
	/**
	 * Asigna el handler indicado al evento de introducción de un plato con éxito
	 *
	 * @param {function} handler - Función a llamar cuando se haya introducido un
	 *		plato con éxito.
	 */
	IntroduccionPlatos.platoIntroducido = function( handler ) {
		modal.on("plato-introducido", handler);
	};

    /**
     * Asigna el handler indicado al evento de cierre del modal
     *
     * @param handler {function} handler - Función a llamar cuando se cierre el modal
     */
	IntroduccionPlatos.alCerrar = function( handler ) {
	    alCerrar = handler;
    };

	/**
	 * Asigna la fecha asociada al plato a introducir
	 *
	 * @param {string|Date} nuevaFecha - Fecha a asignar al nuevo plato
	 */
	IntroduccionPlatos.fecha = function( nuevaFecha ) {
		if( typeof nuevaFecha == 'string' ) {
			modal.attr("fecha", nuevaFecha);
		} else if( typeof nuevaFecha == 'object' ) {
			modal.attr("fecha", nuevaFecha.getFullYear().toString() + "-" +
				(nuevaFecha.getMonth()+1).toString() + "-" +
				nuevaFecha.getDate().toString());
		}
	};

	/**
	 * Contiene los modos de funcionamiento disponibles.
	 * - CALENDARIO lo hace funcionar en modo calendario, los platos serán asociados como servidos en una fecha concreta
	 * - MIS_PLATOS lo hace funcionar en modo misplatos, los platos serán asociados como platos del comedor, pero no servidos
	 *     en ninguna fecha en concreto
	 * @type {{CALENDARIO: string, MIS_PLATOS: string}}
     */
	IntroduccionPlatos.MODOS = {CALENDARIO: 'calendario', MIS_PLATOS: 'misplatos'};

	/**
	 * Cambia el modo de funcionamiento, los valores admitidos son CALENDARIO o MIS_PLATOS.
	 * @param {string} nuevoModo IntroduccionPlatos.MODOS.CALENDARIO o IntroduccionPlatos.MODOS.MIS_PLATOS
     */
	IntroduccionPlatos.fijarModo = function(nuevoModo) {
		switch(nuevoModo) {
			case IntroduccionPlatos.MODOS.CALENDARIO:
			case IntroduccionPlatos.MODOS.MIS_PLATOS:
				modo = nuevoModo;
				break;
			default:
				// No cambia
		}
	};

    /**
     * Indica si se pueden insertar varios platos seguidos sin cierre o no.
     * @param valor {boolean} true para poder insertar múltiples platos, false si no.
     */
	IntroduccionPlatos.multiplato = function(valor) {
        modoMultiplato = !!valor;
    };

	/* A partir de aqui todo es privado */
	var alCerrar = null; //Handler
	var timeout; //Para guardar el timeout de las pulsaciones de tecla
	var getXhr; // Para guardar la consulta get de turno
	var cachePlatos = []; // Pequeña cache para la lista
	var elegido = -1; // Elemento elegido en la lista
	var mostrar = true; // Permite que se muestre la lista si llega un get
	var lista = null; // Para guardar la lista grafica
	var listaVirt = {mios:[],otros:[],length:function(){return this.mios.length+this.otros.length;}}; // Representacion virtual de la lista
	var ipt; // El input del nombre
	var modal; // El modal que contiene el formulario
	var loading; // Circulito de carga
	var modo = IntroduccionPlatos.MODOS.CALENDARIO; // Modo de funcionamiento (calendario / misplatos)
    var modoMultiplato = false;

	$(document).ready(function(){
		modal = $("div#modNuevoPlato");
		ipt = modal.find("#nombreNuevo");
		loading = modal.find("#loading");

		loading.hide();
	
		ipt.keyup(keyUp)
			.focusout(focusLost);
		modal.find("input[type=text], textarea, select")
			.change(somethingChanged);
        modal.keyup(function(event){
            if(event.key == 'Enter' && event.ctrlKey) {
                enviar();
            }
        });
		modal.find('select').material_select(); // Selects de materializecss
		modal.find("form").submit(function(event){event.preventDefault();});
	});

    var prepararModal = function(){
        modal.find("#nombreNuevo").val("");
        modal.find("#descripcionNuevo").val("");
        modal.find("#tipoNuevo").val("0");
        modal.find("#modalError").empty();
        somethingChanged();
        botonesEnabled(true);
        if( lista ) lista.hide();
        mostrar = false;
        ipt.focus();
    };

	var botonesEnabled = function(enabled, soloCierre) {
		if( soloCierre === undefined ) soloCierre = false;
		if( enabled ) {
			if( !soloCierre ) {
				modal.find("#nuevoPlatoAnhadir").removeClass("disabled");
				modal.find("#nuevoPlatoAnhadir").click(enviar);
			}
			modal.find("#nuevoPlatoCerrar").removeClass("disabled");
			modal.find("#nuevoPlatoCerrar").click(cerrar);
		} else {
			if( !soloCierre ) {
				modal.find("#nuevoPlatoAnhadir").addClass("disabled");
				modal.find("#nuevoPlatoAnhadir").unbind();
			}
			modal.find("#nuevoPlatoCerrar").addClass("disabled");
			modal.find("#nuevoPlatoCerrar").unbind();
		}
	};

	var cerrar = function() {
		modal.closeModal();
        if(alCerrar) alCerrar();
	};

	var enviar = function() {
		// Antes de nada, anulamos las respuestas
		botonesEnabled(false);
		// Comprobar si hay un id... enviar datos o id según sea necesario
		var parametros, id;
		if( (id = modal.find("form").attr("plato")) && id >= 0) {
			parametros =  {'idPlato': id};
		} else {
			var nombre = modal.find("#nombreNuevo").val();
			var descripcion = modal.find("#descripcionNuevo").val();
			var tipo = modal.find("#tipoNuevo").val() + "desc";

			if(nombre === "" || descripcion === "") {
				modal.find("#modalError").html("Hay campos sin cubrir.");
				return;
			}

			parametros = {"nombre": nombre, "descripcion": descripcion, "tipo": tipo};
		}
		parametros.paraServir = 0; // En modo MisPlatos, no son para servir

		// En modo calendario, añadimos la fecha e indicamos que es para servir
		if( modo == IntroduccionPlatos.MODOS.CALENDARIO) {
			var fecha = modal.attr("fecha");
			if (!fecha) {
				var hoy = new Date();
				fecha = hoy.getFullYear().toString() + "-" +
					(hoy.getMonth() + 1).toString() + "-" +
					hoy.getDate().toString();
			}
			parametros.fecha = fecha;
			parametros.paraServir = 1;
		}

		$.post("/mysql/view_insertarPlato.php",
			parametros,
			platoIntroducido,
			"json");
	};

    /**
     * Función llamada cuando finaliza la inserción de un plato, maneja la respuesta enviada desde el servidor
     * @param jsonData {{status:String,respuesta:{_id:int,nombre:String,descripcion:String,tipo:String}}|{status:String,error:String}}
     *      Json ya parseado correspondiente a la respuesta del servidor
     */
	var platoIntroducido = function( jsonData ) {
		if( jsonData['status'] ) {
			switch(jsonData['status']) {
                case 'OK':
                    modal.trigger("plato-introducido", jsonData['respuesta']);
                    if(cachePlatos[jsonData.respuesta._id] !== undefined) {
                        cachePlatos[jsonData.respuesta._id].mio = true; // Si se ha introducido, ahora es mío!
                    }
                    if( modoMultiplato ) {
                        prepararModal();
                        modal.find("#modalExito").html(jsonData['respuesta']['nombre'] + " introducido con éxito.");
                    } else {
                        cerrar();
                    }
                    break;
                case 'ERROR':
                    if(jsonData['error'] == 'Repetido') {
                        switch(modo) {
                            case IntroduccionPlatos.MODOS.CALENDARIO:
                                modal.find("#modalError").html("Este plato ya está asignado a este día.");
                                break;
                            default:
                                modal.find("#modalError").html("Este plato ya está en tus platos.");
                                break;
                        }
                    } else {
                        console.log("view_insertarPlato.php", jsonData['error']);
                        modal.find("#modalError").html("El plato no ha podido insertarse. "+
                            "Reinténtelo más tarde o, si sigue sin funcionar, "+
                            "<a href='.?c=bugs&bugInfo="+
                            encodeURIComponent(JSON.stringify(jsonData))+"'>notifíquenoslo</a>. "+
                            "Muchas gracias.");
                    }
                    botonesEnabled(true);
                    break;
			}
		} else {
			console.log("La respuesta de view_insertarPlato.php no contiene status",
				jsonData);
			var bugInfo = {error: 'La respuesta de view_insertarPlato.php no contiene status'};
			modal.find("#modalError").html(
				"El plato puede no haberse insertado. "+
				"<a href='.?c=bugs&bugInfo='" + encodeURIComponent( JSON.stringify( bugInfo ) ) +  ">Notificar del error</a>");
			botonesEnabled(true,true);
		}
	};

	var clickItem = function() {
		var _id = $(this).attr("idPlato");
		var plato = cachePlatos[_id];
		lista.hide();
		loading.hide();
		// Fijamos nombre, descripción y tipo
		ipt.val(plato['nombre']);
		modal.find("#descripcionNuevo").val(plato['descripcion']);
		modal.find("#tipoNuevo").val(parseInt(plato['tipo'].charAt(0)));
		modal.find("#tipoNuevo").material_select();
		// Anotamos el id en el modal, hasta que se cambie
		modal.find("form").attr("plato", _id);
		mostrar = false;
	};

	var enterItem = function() {
	  	elegido = parseInt($(this).attr("nth"));
		lista.find("li").removeClass("selected");
		lista.find("li:eq("+elegido+")").addClass("selected");
	};

	var somethingChanged = function() {
	    modal.find("#modalError").empty();
        modal.find("#modalExito").empty();
		modal.find("form").attr("plato", -1);
	};

	var focusLost = function(/*event*/) {
		// TODO: Cambiar esto por algo que asegure que si el foco se pierde
		// para hacer click sobre la lista, no va a desaparecer la lista
		// antes de registrarse el click (porque entonces nunca se puede hacer
		// click sobre ella) más elegante
		setTimeout(function(){if(lista) lista.hide(); mostrar=false;}, 200);
	};

	var crearLista = function() {
		// Encontramos o creamos una lista
		if( !lista ) {
			ipt.parent().append(
				lista = $("<ul></ul>")
					.css("display", "none")
					.css("width", ipt.width()+"px")
					.addClass("autocompletado"));
		}
	};

	var vaciarLista = function() {
		lista.empty();
		listaVirt.mios.length = 0;
		listaVirt.otros.length = 0;
		elegido = -1;
	};

	var teclaAdecuada = function(t) {
		// Las teclas que provocan recarga son las alfanuméricas, supr y retroceso
		return ( t == 46 || t==8 || (t <= 90 && t >= 48) || (t >= 96 && t <= 105) );
	};

	/**
	 * Agrega un plato a la lista de platos virtual y redibuja la lista gráfica.
	 * @param nuevoPlato {{nombre:String,_id:int,mio:boolean,descripcion:String}}
     */
	var agregarPlato = function(nuevoPlato) {
		// Agregamos el plato a la lista virtual y redibujamos la grafica
		if( nuevoPlato.mio ) {
			listaVirt.mios.push(nuevoPlato);
		} else {
			listaVirt.otros.push(nuevoPlato);
		}

		lista.empty();
        var plato, li, i;
		for(i=0; i < listaVirt.mios.length; i++) {
			plato = listaVirt.mios[i];
			li = _nuevoLiPlato(plato._id, i, plato.nombre);
			li.addClass("mio");
			lista.append(li);
		}
        for(i=0; i < listaVirt.otros.length; i++) {
            plato = listaVirt.otros[i];
            lista.append(_nuevoLiPlato(plato._id, i+listaVirt.mios.length, plato.nombre));
        }
    };

    /**
     * Genera un nuevo <li> para de plato para la lista de platos del autocompletado
     * @param id {int} Id del plato asociado al li
     * @param i {int} Index del li en la lista
     * @param nombre {String} Nombre del plato asociado al li
     * @returns {JQuery} Objeto jQuery con el <li> creado
     * @private
     */
	var _nuevoLiPlato = function(id, i, nombre) {
	    return $("<li></li>")
            .attr("idPlato", id)
            .attr("nth", i)
            .click(clickItem)
            .mouseenter(enterItem)
            .html(nombre);
    };

	var descargarDatos = function(nombre, abortable) {
		if(abortable === undefined) abortable = true;

		// Llamamos al autocompletado, anulando previamente el anterior
		if (getXhr && abortable) { getXhr.abort(); }
		loading.show();
		var temp = $.get("/mysql/view_autocompletado.php",
			{'nombre': nombre, 'misPlatos': (modo==IntroduccionPlatos.MODOS.CALENDARIO?1:0)},
			function(data){
				loading.hide();
				// console.log(data);
				if( data['status'] == "OK" ) {
				    // Nos devolverá un json de platos coincidentes, con nombre,
					//descripcion, tipo y si son nuestros
					var res; // Para ir almacenando resultados
					for(var k in data['resultados']) {
						if ( data['resultados'].hasOwnProperty(k) ) {
							res = data['resultados'][k];
							if (!cachePlatos[res['_id']]) {
								//Añadimos a la cache cada uno de los resultados
								cachePlatos[res['_id']] = res;

								// Si el nombre buscado coincide con el
								//resultado, lo agregamos a la lista
								//añadimos también a la lista los resultados
								if (res['nombre'] && res['nombre'].toLowerCase()
										.startsWith(ipt.val().toLowerCase())) {
									agregarPlato(res);
								}
							}
						}
					}
					if(listaVirt.length() > 0 && mostrar) {
						lista.show();
					}
				} else {
					// Podemos manejar errores si se quiere
					console.log(data);
				}
			},
			"json");
		// Si es abortable, la guardamos en getXhr
		if(abortable)
			getXhr = temp;
	};

	var keyUp = function(event) {
		// Obtenemos que tecla se pulsó y guardamos el elemento
		var t = event.which;
	
		// Comprobamos si es una tecla que merezca recarga
		if(teclaAdecuada(t)) {
			// Obtenemos nombre escrito
			var nombre = ipt.val().trim().toLowerCase();
		
			// Creamos la lista
			// Si se crea en document.ready su ancho no es el adecuado
			crearLista();
			lista.hide(); // La ocultamos
			vaciarLista(); // La vaciamos

			// Si hay algo escrito proseguimos
			if(nombre !== "") {
				// Realizamos una busqueda en la cache de platos,
				// quizás podemos ir rellenando algo ya
				var plato;
			
				// Activamos mostrar la lista ante la llegada de GET's
				mostrar = true;
			
				for(_id in cachePlatos) {
					plato = cachePlatos[_id];
					if(plato['nombre'].toLowerCase().startsWith(nombre)) {
						agregarPlato(plato);
					}
				}
			
				// Si hay algo ya, la mostramos
				if(listaVirt.length() > 0) {
					lista.show();
				}
			
				// Un pequeño delay para que si escribes mucho seguido
				// no provoque AJAX innecesario (pero la primera letra
				// sí carga)
				if(nombre.length > 1) {
					clearTimeout(timeout);
					timeout = setTimeout(descargarDatos.bind(this,nombre), 500);
				} else
					descargarDatos(nombre,false);
			}
		} else {
			// La tecla no provoca recarga, ¿es arriba o abajo?
			switch(event.key) {
				case "ArrowUp":
					if( elegido > -1 ) {
						elegido-=1;
						lista.find("li").removeClass("selected");
						if( elegido > -1)
							lista.find("li:eq("+elegido+")").addClass("selected");
					}
					break;
				case "ArrowDown":
					if( elegido < listaVirt.length() - 1) {
						elegido+=1;
						lista.find("li").removeClass("selected");
						lista.find("li:eq("+elegido+")").addClass("selected");
					}
					break;
				case "Enter":
				    if( !event.ctrlKey ) {
                        if (elegido > -1 && elegido < listaVirt.length())
                            lista.find("li:eq(" + elegido + ")").click();
                    }
					break;
			}
		}
	};
}( window.IntroduccionPlatos = window.IntroduccionPlatos || {}, jQuery )); // Fin del "namespace"
