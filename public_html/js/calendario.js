/*
 * Script de manejo del calendario.
 *
 * Autor: David Campos R.
 * Fecha: 30/06/2016
 */

const API_URL = "/api/";
const LOOKS = ["one", "two", "3"];
const COLORES = ["#aed581", "#8bc34a", "#689f38", "#f44336"];
const TIPOS_PLATOS = ["Primero", "Segundo", "Postre", "Desconocido"];

var comedor_listo = false;
var mis_platos_listo = false;
var document_ready = false;
var introduccion_platos_listo = false;

var COMEDOR = null;
var MIS_PLATOS = null;

$(document).on('quizas-listo', function () {
    if (comedor_listo && mis_platos_listo && document_ready && introduccion_platos_listo)
        $(document).trigger("iniciar-calendario");
});

$(document).ready(function () {
    document_ready = true;
    $(document).trigger('quizas-listo');
});
$(document).on(IntroduccionPlatos.READY_EVENT, function () {
    introduccion_platos_listo = true;
    $(document).trigger('quizas-listo');
});
$.get("/mysql/view_comedorInfo.php", function (data) {
    if (!data.error) {
        COMEDOR = data;
        comedor_listo = true;
        $(document).trigger('quizas-listo');
    }
}, "json");
$.get("/mysql/view_misPlatosInfo.php", function (data) {
    if (!data.error) {
        MIS_PLATOS = data;
        mis_platos_listo = true;
        $(document).trigger('quizas-listo');
    }
}, "json");

// Arrays de nombres de meses
var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
	'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

 // Función para añadir días a una fecha
Date.prototype.plusDays = function(days) {
	return new Date(this.getFullYear(), this.getMonth(), this.getDate() + days);
};

/****** Clase Cache ******/
Cache = function() {
	this.diasConPlatos = {}; // Array asociativo "mesAño: [dias]" con los dias que tienen platos
	this.platos = {}; // Array asociativo "mesAñoDia: [platos]" con los platos por dia
};

Cache.prototype = {
	keyDias: function(fecha) {
		return meses[fecha.getMonth()]+fecha.getFullYear().toString();
	},
	
	setDias: function(fecha, dias) {
		this.diasConPlatos[this.keyDias(fecha)] = dias;
	},
	
	getDias: function(fecha) {
		return this.diasConPlatos[this.keyDias(fecha)];
	},
	
	diasCacheados: function(fecha) {
		return (this.getDias(fecha) !== undefined);
	},

    actualizarDias: function(month, alFinalizar) {
	    var self = this;
        if(cache.diasCacheados(month)) {
            $.get(
                API_URL,
                {tipo: 5, id: COMEDOR._id, month: month.getMonth()+1, year: month.getFullYear()},
                function(data) {
                    // Hay que coger hasta <!--FIN--> por la propaganda, cuando tengamos
                    // server propio ya no hará falta.
                    var info = data.substring(0,data.indexOf("<!--FIN-->"));
                    var json = JSON.parse(info);
                    if( json['status'] == 'OK' ) {
                        self.setDias(month, json['respuesta']);
                        if(alFinalizar) alFinalizar();
                    } else {
                        console.log(json);
                    }
                }, "text");
        }
    },

	addDia: function(fecha) {
		if( this.getDias(fecha).indexOf(fecha.getDate()) < 0) {
			this.getDias(fecha).push(fecha.getDate());
		}
	},

	removeDia: function(fecha) {
		var idx = this.getDias(fecha).indexOf(fecha.getDate());
		if( idx >= 0) {
			this.getDias(fecha).splice(idx, 1);
		}
	},

	tienePlatos: function(mes, dia) {
		return (this.getDias(mes).indexOf(dia) != -1);
	}/*,
	
	keyPlatos: function(fecha) {
		return this.keyDias(fecha)+fecha.getDate().toString;
	},
	
	getPlatos: function(fecha) {
		return this.platos[this.keyPlatos(fecha)];
	},
	
	addPlato: function(fecha, plato) {
		if( ! this.getPlatos(fecha) ) {
			this.platos[this.keyPlatos(fecha)] = [plato];
		} else {
			this.platos[this.keyPlatos(fecha)].push(plato);
		}
	}*/
};

/****** Clase ModalPlatos ******/
ModalPlatos = function(modalJqueryNode) {
	this.modalNode = modalJqueryNode;
	
	modalJqueryNode.find("#calPlatosOpen").click(function(){
		var dia = new Date(mes);
        var strDia;
		if( ! (strDia = modPlatos.obtenerDia()) ) {
			console.log("No se puede obtener el dia, cerrando modal.");
			modPlatos.cerrar();
		} else {
			dia.setDate(parseInt(strDia));
			IntroduccionPlatos.abrir(dia);
		}
	});
};

ModalPlatos.prototype = {
	abrir: function(dia) {
		this.modalNode.attr("dia", dia);
		this.modalNode.openModal({complete: function(){
			// Al cerrar el modal hay que reactivar los botones adecuadamente
			$("table#tCalendario th#calNext").click(calSiguiente);
			$("table#tCalendario th#calNext a").removeClass("disabled");
			
			// No se puede retroceder si estamos en el mes actual
			if(mes.getTime() > (new Date()).getTime()) {
				$("table#tCalendario th#calBefore").click(calAnterior);
				$("table#tCalendario th#calBefore a").removeClass("disabled");
			}
			
			// A veces se queda la sombrilla gris si abres varios a la vez
			// así que me las cargo todas jeje
			$(".lean-overlay").remove();
		}});
	},
	
	obtenerDia: function(){
		return this.modalNode.attr("dia");
	},
	
	cerrar: function() {
		this.modalNode.closeModal();
	},
	
	clear: function() {
		this.modalNode.find("div.modal-content").empty();
	},
	
	texto: function(texto, error) {
		var p;
        if(error === undefined) error = false;
		if( ( p = this.modalNode.find("p.textoListaPlatos") ).length == 0 ) {
			this.modalNode.find("div.modal-content")
				.append(p = $("<p></p>").addClass("textoListaPlatos"));
		}
		p.addClass(error?"red-text":"")
			.text(texto);
	},

	/**
	 * Agrega platos a la lista de platos del modal.
	 * @param platos {[{_id:int,nombre:String,tipo:String,descripcion:String,agotado:int}]}
     */
	agregar: function( platos ) {
	    var i;
		var ul = this.modalNode.find("div.modal-content ul");
		if( ul.length == 0 )
			this.modalNode.find("div.modal-content")
				.prepend(ul = $("<ul></ul>")
					.addClass("collapsible popout")
					.addClass("lista-de-platos")
					.collapsible());
		
		for(i=0; i<platos.length; i++) {
			var id = platos[i]._id;
			var nombre = platos[i].nombre;
			if( nombre === undefined ) nombre = "Desconocido";
			var tipo = platos[i].tipo;
			var color = COLORES[3];
			var look = '';
			var tipoStr = TIPOS_PLATOS[3];
			if( tipo !== undefined ) {
				var c = platos[i].tipo.charAt(0);
				if(COLORES.hasOwnProperty(c))
					color = COLORES[c];
				if(LOOKS.hasOwnProperty(c))
					look = '_'+LOOKS[c];
				if(TIPOS_PLATOS.hasOwnProperty(c))
					tipoStr = TIPOS_PLATOS[c];
			}

			var agotado = platos[i].agotado;
			ul.append(
					$("<li></li>")
					.attr("id_plato", id)
					.attr("nombre", nombre)
					.append(
						$("<div></div>")
						.addClass("collapsible-header")
						.append(
							$("<i></i>")
							.addClass("material-icons")
							.css("color", color)
							.html("looks"+look))
						.append(
							$("<span></span>")
							.addClass("tipo-plato")
							.css("color", color)
							.text("("+tipoStr+")"))
						.append(
							$("<span></span>")
							.css("color", (agotado==1)?"#f44336":"")
							.html(nombre))
						.append(
							$("<a></a>")
							.addClass("secondary-content amber-text")
							.append(
								$("<i></i>")
								.addClass("material-icons")
								.html("delete_forever"))
							.click({'self': this}, this.eliminarPlato)))
					.append(
						$("<div></div>")
						.addClass("collapsible-body")
						.html("<p>"+platos[i].descripcion+"</p>")));
		}
	},
	
	eliminarPlato: function(event) {
		var self = event.data.self;
		var $this = $(this);
        var strDia;

		event.stopPropagation();
		$this.unbind();
		$this.closest(".collapsible-header")
			.append($("<div></div>")
				.addClass("progress")
				.append($("<div></div>")
					.addClass("indeterminate")));
		
		if( ! (strDia = modPlatos.obtenerDia()) ) {
			console.log("No se puede obtener el dia");
			return;
		}
		var dia = new Date(mes);
		dia.setDate(parseInt(strDia));

		var paramId = $this.closest("li").attr("id_plato");
		var nombrePlato = $this.closest("li").attr("nombre");
		var paramFecha = dia.getFullYear().toString() + "-" + (dia.getMonth()+1).toString() +
			"-" + dia.getDate().toString();

		$.post("/mysql/view_eliminarPlato.php",
				{'idPlato': paramId, 'fecha': paramFecha, 'asoc': 'tener'},
				function( data ) {
					if( data.status == "OK") {
						if($this.closest("ul").find("li").length == 1) {
							self.texto("No hay platos que mostrar.");
							cache.removeDia(dia);
							redibujar();
						}
						$this.closest("li").remove();
						var accion = new Historial.AccionEliminarPlato({
							'_id': paramId,
							'fecha': paramFecha,
							'nombre': nombrePlato},
                            function(){ cache.actualizarDias(dia, redibujar)});
						acciones.nuevaAccion(accion);
					} else {
						alert("FALLO eliminando plato: '"+data.error+"'");
						console.log(data);
						$this.closest("li").find(".progress").remove();
						$this.click(self.eliminarPlato);
					}
				}, "json");
	}
};

/**
 * Clase ListaMisPlatos
 */
ListaMisPlatos = function(up, tds, down, destinos) {
    this.up = up;
    this.tds = tds;
    this.down = down;

    this.showLoad = 0;
    this.fromHere = false;

    this.ini = 0;
    this.display_size = tds.length;
    this.end = MIS_PLATOS.length - this.display_size;

    var self = this; // Para las funciones anonimas
    this.up.click(function(e){e.preventDefault(); self.sube();});
    this.down.click(function(e){e.preventDefault(); self.baja();});

    [].forEach.call(destinos, function(d){
        d.addEventListener('dragover', function(e){ self.dragover(e);}, false);
        d.addEventListener('dragenter', function(e){ self.dragin(e);}, true);
        d.addEventListener('dragleave', function(e){ self.dragout(e);}, false);
        d.addEventListener('drop', function(e){ self.drop(e);}, false);
    });

    this._iniciar();
    this.dibujar();
};
ListaMisPlatos.prototype = {
    readaptarLongitud: function(){
        this.end = MIS_PLATOS.length - this.display_size;
        this._iniciar();
        this.dibujar();
    },
    /**
     * Inicia la lista, preparandola para dibujar sobre ella
     * @private
     */
    _iniciar: function() {
        for (var i = 0; i < this.display_size; i++) {
            if(i < MIS_PLATOS.length) {
                this.tds.eq(i)
                    .empty()
                    .addClass("iniciada amber white-text left-align")
                    .attr("draggable", "true")
                    .append($("<div></div>")
                        .addClass("truncate")
                        .append($("<i></i>")
                            .addClass("material-icons left")
                            .html("restaurant"))
                        .append($("<span></span>")));
            } else {
                this.tds.eq(i)
                    .empty()
                    .removeClass()
                    .removeAttr("draggable")
                    .addClass("ranuraMisPlatos");
            }
        }
    },
    /**
     * Dibuja de nuevo la lista
     */
    dibujar: function() {
        var self = this;

        if(this.ini <= 0) this.up.addClass("disabled");
        else this.up.removeClass("disabled");
        if(this.ini >= this.end) this.down.addClass("disabled");
        else this.down.removeClass("disabled");

        for (var i = 0; i < this.display_size; i++) {
            var plato = MIS_PLATOS[this.ini + i];
            if(this.ini+i < MIS_PLATOS.length) { // Podria haber menos platos que display_size
                this.tds.eq(i)
                    .tooltip({
                        tooltip: "<span class='title'>" + plato.nombre + "</span>" +
                        "<span class='type'>" + formatoTipo(plato.tipo.substr(0, 1)) + "</span>" +
                        "<p><span>Descripcion:</span> " + plato.descripcion + "</p>",
                        position: 'left',
                        html: true
                    })
                    .attr("idx", i)
                    .on('dragstart', function(evt){self.dragstart(evt);})
                    .on('dragend', function(evt){self.dragend(evt);})
                    .find("span").text(plato.nombre);
                var iconito = this.getIcon(plato.tipo);
                this.tds.eq(i).find("i").html(iconito);
            }
        }
    },
    getIcon: function(tipo) {
        switch(tipo.substr(0,1)){
            case '0': return "looks_one";
            case '1': return "looks_two";
            case '2': return "free_breakfast";
            default: return "help";
        }
    },
    dragstart: function(event) {
        var idx = parseInt($(event.currentTarget).attr("idx"));
        this.fromHere = true;
        event.originalEvent.dataTransfer.setData("plato", MIS_PLATOS[this.ini + idx]._id);
    },
    dragover: function(event) {
        if(this.fromHere && !$(event.currentTarget).hasClass("otroMes")) {
            event.preventDefault();
        }
    },
    dragin: function(event) {
        if(this.fromHere && !$(event.currentTarget).hasClass("otroMes")) {
            event.preventDefault();
            $(event.currentTarget).addClass("puedes-dropear");
        }
    },
    dragout: function(event) {
        if(this.fromHere && !$(event.currentTarget).hasClass("otroMes")) {
            event.preventDefault();
            $(event.currentTarget).removeClass("puedes-dropear");
        }
    },
    dragend: function() {
        this.fromHere = false;
    },
    drop: function(event) {
        $(event.currentTarget).removeClass("puedes-dropear");
        var id_plato = event.dataTransfer.getData("plato");

        if(!id_plato || $(event.currentTarget).hasClass("otroMes")) return;

        var dia = $(event.currentTarget).find("span").text();
        var fecha =  mes.getFullYear().toString() + "-" +
            (mes.getMonth() + 1).toString() + "-" +
            dia;
        var parametros =  {'idPlato': id_plato, 'fecha': fecha, 'paraServir': 1};
        console.log(parametros);
        this.showLoad+=1;
        if(calendario) calendario.find("div.progress").show();

        var self = this;
        $.post("/mysql/view_insertarPlato.php",
            parametros,
            function(data){self.insercionCompletada(data);},
            "json");
    },

    insercionCompletada: function(data) {
        this.showLoad-=1;
        if(this.showLoad <= 0 && calendario) {
            calendario.find("div.progress").hide();
            this.showLoad = 0;
        }

        if( data.status ) {
            if(data.status == "OK") {
                data = data.respuesta;

                // Añadimos dia a la cache
                cache.addDia(new Date(data.fecha));
                redibujar();

                // Añadimos accion
                var accion = new Historial.AccionNuevoPlato({
                        '_id': data._id,
                        'fecha': data.fecha,
                        'nombre': data.nombre
                    },
                    function () {
                        cache.actualizarDias(new Date(data.fecha), redibujar);
                    });
                acciones.nuevaAccion(accion);
            } else {
                alert("Ha habido algun error... Recarga para obtener información real sobre los platos de cada día.");
                console.log(data);
            }
        } else {
            if(console) console.log("Data no contiene status.");
        }
    },
    sube: function(){
        if(this.ini > 0) {
            this.ini = this.ini - 1;
            this.dibujar();
        }
    },
    baja: function() {
        if(this.ini < this.end) {
            this.ini = this.ini + 1;
            this.dibujar();
        }
    }
};

var listaMisPlatos = null;

var mes; // Maneja el mes que estamos mostrando (y su año)
var cache; // Cache de platos y dias
var modPlatos; // Modal de platos
var calendario; // Tabla del calendario
var acciones; // Manejador de acciones

// -- Inicialización del calendario
$(document).on('iniciar-calendario', function () {
    mes = new Date();
	cache = new Cache();
	modPlatos = new ModalPlatos($("#modPlatos"));
	calendario = $("table#tCalendario");
    acciones = new Historial.ManejadorAcciones($("#acciones"), $("#control-acciones"));

    var listaUp = calendario.find("thead th.misPlatos a");
    var listaTds = calendario.find("tbody td.ranuraMisPlatos");
    var listaDown = calendario.find("tfoot td.misPlatos a");
    var dias = calendario.find("tbody td.calDia");
	listaMisPlatos = new ListaMisPlatos(listaUp, listaTds, listaDown, dias);

    // La introducción de un plato llevará a esto
    IntroduccionPlatos.platoIntroducido(platoIntroducido);

    acciones.siSolicitaRedibujar(redibujar);

	$("body").css('overflow-x', 'hidden'); //No hay scroll horizontal aqui

	$('select').material_select(); // Selects de materializecss

    // Descargamos dias con platos y mostramos
    $.get(
        API_URL,
        {tipo: 5, id: COMEDOR._id, month: mes.getMonth() + 1, year: mes.getFullYear()},
        function (data) {
            // Hay que coger hasta <!--FIN--> por la propaganda, cuando tengamos
            // server propio ya no hará falta.
            var info = data.substring(0, data.indexOf("<!--FIN-->"));
            var json = JSON.parse(info);
            if (json['status'] == 'OK') {
                cache.setDias(mes, json['respuesta']);
                redibujar();

                siguienteAnteriorEnabled(true);

                // Hemos cargado!
                $("div#rCargando").hide();
                $("div#rCalendario").show();

                // Precarga el mes siguiente
                var smes = mes.getMonth() + 1;
                var syear = mes.getFullYear();
                if (smes == 12) {
                    syear += 1;
                    smes = 0;
				}

                precargarMes(new Date(syear, smes));
            } else {
                alert("Ha habido un error."); // TODO: cambiar alert por algo con más estilo
                console.log(json);
            }
        }, "text");
});
// -- Fin de la inicialización

// Activa / desactiva los botones siguiente y anterior
siguienteAnteriorEnabled = function( valor ) {
	if( valor ) {
		calendario.find("th#calNext").click(calSiguiente);
		calendario.find("th#calNext a").removeClass("disabled");
		
		// No se puede retroceder si estamos en el mes actual
		if(mes.getTime() > (new Date()).getTime()) {
			calendario.find("th#calBefore").click(calAnterior);
			calendario.find("th#calBefore a").removeClass("disabled");
		}
	} else {
		calendario.find("th#calNext").unbind();
		calendario.find("th#calNext a").addClass("disabled");
		calendario.find("th#calBefore").unbind();
		calendario.find("th#calBefore a").addClass("disabled");
	}
};

// Cambia al siguiente mes
calSiguiente = function() { avanzar('izq'); };

// Cambia al mes anterior
calAnterior = function() { avanzar('der'); };

avanzar = function(dir) {
	switch(dir) {
		case 'izq':
			mes = new Date(mes.getFullYear(), mes.getMonth() + 1, 1);
			break;
		case 'der':
			mes = new Date(mes.getFullYear(), mes.getMonth() - 1, 1);
			break;
	}
	// Los botones de siguiente y anterior dejan de funcionar
	siguienteAnteriorEnabled(false);
	
	if(!cache.diasCacheados(mes)) {
		// Cargar el nuevo mes (no esta cargado)
		calendario.find("div.progress").show();
		$.get(
			API_URL,
			{tipo: 5, id: COMEDOR._id, month: mes.getMonth()+1, year: mes.getFullYear()},
			function(data) {
				if(!cache.diasCacheados(mes)) {
					// Hay que coger hasta <!--FIN--> por la propaganda, cuando tengamos
					// server propio ya no hará falta.
					var info = data.substring(0,data.indexOf("<!--FIN-->"));
					var json = JSON.parse(info);
					if( json['status'] == 'OK' ) {
						cache.setDias(mes, json['respuesta']);
						calendario.find("div.progress").hide();
						animarDibujar(dir);
					} else {
						alert("Hubo algún error.");
						console.log(json);
					}
				}
			}, "text");
	} else {
		// Mes listo
		animarDibujar(dir);
	}
};

animarDibujar = function(dir) {
	var signo = (dir=='der'?'-':'+');
	var signoCont = (signo=='-'?'+':'-');
	
	var calendarioViejo = calendario;
	calendarioViejo.find("td").unbind();
	// Copiamos el calendario viejo, y le cambiamos el id
	calendario = calendarioViejo.clone(); // Copiamos calendario
	calendarioViejo.attr("id", "tCalendarioViejo");
	
	// Colocaremos el nuevo al lado del viejo, para animarlo
	var ancho = calendarioViejo.width();
	calendario.css('position', 'absolute');
	calendario.css('top', calendarioViejo.position().top + 'px');
	calendario.css('left', calendarioViejo.position().left + 'px');
	calendario.css('width', ancho + 'px');
	
	// Preparamos para desplazar, ocultamos barra horizontal
	var desp = $(window).width();
	calendario.css('marginLeft', signo + desp + 'px');
	calendarioViejo.parent().append(calendario);
	
	// Redibujar dibujará en el nuevo
	redibujar();
	var listaUp = calendario.find("thead th.misPlatos a");
	var listaTds = calendario.find("tbody td.ranuraMisPlatos");
	var listaDown = calendario.find("tfoot td.misPlatos a");
	var dias = calendario.find("tbody td.calDia");
	listaMisPlatos = new ListaMisPlatos(listaUp, listaTds, listaDown, dias);
	
	// Animamos los calendarios para que se desplacen
	calendarioViejo.add(calendario).animate(
		{'marginLeft': signoCont + '=' + desp + 'px'},
		function() {
			if($(this).attr('id') === 'tCalendarioViejo') {
				// Cuando se completa la animación del viejo, se elimina
				calendario.css('position', '');
				calendario.css('top', '');
				calendario.css('left', '');
				calendario.css('width', '100%');
				$(this).remove();
			} else {
				// Cuando se completa la animación del nuevo, se le ponen los
				// handlers a los botones de siguiente y anterior
				siguienteAnteriorEnabled(true);
			}
		});
};

precargarMes = function(precMes) {
	if(!cache.diasCacheados(precMes)) {
		$.get(
			API_URL,
			{tipo: 5, id: COMEDOR._id, month: precMes.getMonth()+1, year: precMes.getFullYear()},
			function(data) {
				if(!cache.diasCacheados(precMes)) {
					// Hay que coger hasta <!--FIN--> por la propaganda, cuando tengamos
					// server propio ya no hará falta.
					var info = data.substring(0,data.indexOf("<!--FIN-->"));
					var json = JSON.parse(info);
					if( json['status'] == 'OK' ) {
						cache.setDias(precMes, json['respuesta']);
					} else {
						console.log(json);
					}
				}
			}, "text");
	}
};

// Al hacer click en un día clickable
clickDia = function() {
	if( $(this).hasClass('otroMes') || $(this).hasClass('pasado') ) {
		// Esto no debería pasar!
		console.log("Click donde no click, que clickante!");
		return;
	}
	
	// Los botones de siguiente y anterior dejan de funcionar
	siguienteAnteriorEnabled(false);
	
	// Barra de cargando, el get puede tardar!!
	calendario.find("div.progress").show();
	
	var dia = $(this).find('span').html();
	
	// Conseguimos los platos y mostramos el modal
	$.get(API_URL, {
			tipo: 3,
			id: COMEDOR._id,
			fecha: mes.getFullYear()+"-"+(mes.getMonth()+1)+"-"+dia},
			function(data){
				// Hay que coger hasta <!--FIN--> por la propaganda, cuando tengamos
				// server propio ya no hará falta.
				var info = data.substring(0,data.indexOf("<!--FIN-->"));
				var objetoJson = JSON.parse(info);
				
				modPlatos.clear();
				if(objetoJson['status'] == 'OK') {
					if(objetoJson['respuesta'].length > 0) {
						modPlatos.agregar(objetoJson['respuesta']);
					} else {
						modPlatos.texto("No hay plato alguno.");
					}
				} else {
					modPlatos.texto("Error al cargar los platos.", true);
					console.log(objetoJson);
				}
				
				modPlatos.abrir(dia);

				calendario.find("div.progress").hide();
			}, "text");
};

var platoIntroducido = function(event, plato) {
    // Añadimos plato al modal
    modPlatos.texto("");
    modPlatos.agregar([plato]);

    // Añadimos dia a la cache
    cache.addDia(new Date(plato.fecha));
    redibujar();

    // Añadimos a la lista si no estaba
    var i;
    for(i=0; i<MIS_PLATOS.length; i++) {
        if(MIS_PLATOS[i]._id == plato._id)
            break;
    }
    if(i == MIS_PLATOS.length)
        MIS_PLATOS[MIS_PLATOS.length] = plato;
    listaMisPlatos.readaptarLongitud();

    // Añadimos accion
    var accion = new Historial.AccionNuevoPlato({
            '_id': plato._id,
            'fecha': plato.fecha,
            'nombre': plato.nombre},
        function(){ cache.actualizarDias(new Date(plato.fecha), redibujar);});
    acciones.nuevaAccion(accion);
};

 // Redibuja los valores y estilos del calendario
redibujar = function() {
	// Corregimos mes para quitarle horas y minutos y segundos
	mes.setHours(0);
	mes.setMinutes(0);
	mes.setSeconds(0);
	mes.setMilliseconds(0);
	mes.setDate(1); // Lo ponemos al inicio del mes
	
	// Escribimos el año y el mes
	calendario.find("th#calYear").html(mes.getFullYear());
	calendario.find("th#calMonth").html(meses[mes.getMonth()]);

	// ini determinará en que día de la semana comienza el mes
	var ini = mes.getDay()-1;
    if( ini < 0 ) ini = 6; // getDay empieza en domingo, nosotros en lunes
	var ptr = new Date(mes); // Copiamos mes
	ptr = ptr.plusDays(-ini); // Ponemos ptr al inicio del calendario
	
	// hoy guardará hoy jeje
	var hoy = new Date();
	hoy.setHours(0);
	hoy.setMinutes(0);
	hoy.setSeconds(0);
	hoy.setMilliseconds(0);

    // Dia de inicio y fin de apertura
    var iniAp = obtenerDia(COMEDOR.diaInicioApertura);
    var finAp = obtenerDia(COMEDOR.diaFinApertura);

	// Para cada casilla
	calendario.find("tbody td.calDia").each( function(/*idx*/) {
		$(this).removeClass().addClass('calDia'); // Quitamos estilos anteriores (pero sigue siendo dia)
		$(this).unbind(); // Quitamos handlers
		$(this).css('cursor', ''); // Quitamos cursor
		
		if(ptr.getMonth() != mes.getMonth()) {
			// No es un dia del mes actual
			$(this).addClass("otroMes");
			// Si haces click, cambias al mes correspondiente
			if(ptr.getMonth() < mes.getMonth()) {
				if(ptr.getTime() > hoy.getTime())
					$(this).click(calAnterior);
				else
					$(this).css('cursor', 'default');
			} else {
				$(this).click(calSiguiente);
			}
		} else {
		    if ( !enPlazo(ptr.getDay(), iniAp, finAp) ) {
		        $(this).addClass("cerrado");
            }
            if (ptr.getTime() < hoy.getTime()) {
                // Es un día ya pasado
                $(this).addClass("pasado");
                $(this).css('cursor', 'default');
            } else {
                if (ptr.getTime() == hoy.getTime())
                    $(this).addClass("hoy");

                if (cache.tienePlatos(mes, ptr.getDate())) {
                    // Tiene platos
                    $(this).addClass("conPlatos");
                }

                $(this).click(clickDia);
            }
        }
		
		// Ponemos el número de día
		$(this).find('span').html(ptr.getDate());
		ptr = ptr.plusDays(1);
	});
};

enPlazo = function(dia, ini, fin) {
    if(fin > ini) {
        return ini <= dia && dia <= fin;
    } else {
        return dia <= ini || dia >= fin;
    }
};

obtenerDia = function(dia) {
    switch(dia) {
        case 'domingo': return 0;
        case 'lunes': return 1;
        case 'martes': return 2;
        case 'miercoles': return 3;
        case 'jueves': return 4;
        case 'viernes': return 5;
        case 'sabado': return 6;
        default: return -1;
    }
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
};