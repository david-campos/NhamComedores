/*
 * calendario_acciones.js
 * Script que controla un historial de acciones, diseñado especialmente para la
 * sección calendario.
 *
 * David Campos Rodríguez
 * 19/08/2016
 */

// Declaracion de un "namespace" al estilo de Enterprise JQuery
(function( Historial, $, undefined ) {
	const ELIMINAR_PLATO = "/mysql/view_eliminarPlato.php";
	const INSERTAR_PLATO = "/mysql/view_insertarPlato.php";

	/* ****** Accion Añadir Plato ******* */
	Historial.AccionNuevoPlato = function(data, onUndoRedo) {
		if(!data['_id'] || !data['fecha'])
			throw 'Data no válida - '+JSON.stringify(data);
		this.idPlato = data._id;
		this.fecha = data.fecha;
		this.description = 'Añadido plato "'+data.nombre+'" en '+data.fecha;
		this.text = 'Añadido "'+data.nombre+'"';
		this.alRehacer = this.alDeshacer = onUndoRedo;
	};
	Historial.AccionNuevoPlato.prototype = {
		TIPO: 'nuevo-plato',
		COLOR: 'light-green',
		deshacer: function(onComplete, onFailure) {
			var self = this;
            var token = $("input[name=token_eliminar_plato]").val();
			$.ajax({url:ELIMINAR_PLATO,
				method: 'POST', async: false,
                data: {'idPlato': this.idPlato, 'fecha': this.fecha, 'asoc': 'tener', auth_token: token},
				complete: function( data ) {
					data = data.responseJSON;
					if( data.status && data.status == "OK") {
						if(self.alDeshacer) self.alDeshacer();
						onComplete();
					} else {
						onFailure();
						console.log(data);
					}
				},
				dataType: "json"});
		},
		rehacer: function(onComplete, onFailure) {
			var self = this;
            var token = $("#modNuevoPlato").find("input[name=auth_token]").val();
			$.ajax({url:INSERTAR_PLATO,
				method: 'POST', async: false,
                data: {'idPlato': this.idPlato, 'fecha': this.fecha, auth_token: token},
				complete: function( data ) {
					data = data.responseJSON;
					if( data.status && data.status == "OK") {
						if(self.alRehacer) self.alRehacer();
						onComplete();
					} else {
						onFailure();
						console.log(data);
					}
				},
				dataType: "json"});
		}
	};

	/* ****** Accion Eliminar Plato ****** */
	Historial.AccionEliminarPlato = function(data, onUndoRedo) {
		if(!data['_id'] || !data['fecha'])
			throw 'Data no válida - '+JSON.stringify(data);
		this.idPlato = data._id;
		this.fecha = data.fecha;
		this.description = 'Eliminado plato "'+data.nombre+'" en '+data.fecha;
		this.text = 'Eliminado "'+data.nombre+'"';
		this.alRehacer = this.alDeshacer = onUndoRedo;
	};
	Historial.AccionEliminarPlato.prototype = {
		TIPO: 'eliminar-plato',
		COLOR: 'red',
		deshacer: function(onComplete, onFailure) {
			var self = this;
            var token = $("#modNuevoPlato").find("input[name=auth_token]").val();
			$.ajax({url:INSERTAR_PLATO,
				method: 'POST', async: false,
                data: {'idPlato': this.idPlato, auth_token: token, 'fecha': this.fecha},
				complete: function( data ) {
					data = data.responseJSON;
					if( data.status && data.status == "OK") {
						if(self.alDeshacer) self.alDeshacer();
						onComplete();
					} else {
						onFailure();
						console.log(data);
					}
				},
				dataType: "json"});
		},
		rehacer: function(onComplete, onFailure) {
			var self = this;
            var token = $("input[name=token_eliminar_plato]").val();
			$.ajax({url:ELIMINAR_PLATO,
				method: 'POST', async: false,
                data: {'idPlato': this.idPlato, 'fecha': this.fecha, 'asoc': 'tener', auth_token: token},
				complete: function( data ) {
					data = data.responseJSON;
					if( data.status && data.status == "OK") {
						if(self.alRehacer) self.alRehacer();
						onComplete();
					} else {
						onFailure();
						console.log(data);
					}
				},
				dataType: "json"});
		}
	};

	/* ****** Clase ManejadorAcciones ****** */
	Historial.ManejadorAcciones = function(jqueryNode, controladorAcciones) {
		this.accionesHechas = [];
		this.accionesDeshechas = [];
		this.solicitarRedibujar = null;
		this.view = new AccionesView(this, jqueryNode, controladorAcciones);
	};
	Historial.ManejadorAcciones.prototype = {
		siSolicitaRedibujar: function(handler) {
			this.solicitarRedibujar = handler;
		},
		nuevaAccion: function(accion) {
			if( (typeof accion.deshacer != 'function') ||
					(typeof accion.rehacer != 'function') ) {
				throw {'info': 'Añadida accion no válida', 'accion': accion};
			}
			this.accionesDeshechas = [];
			this.accionesHechas.push(accion);
			this.view.nuevaAccion(this.accionesHechas.length, accion.text, accion.description, accion.COLOR);
			this.view.actualizarControl(this);
		},
		puedoDeshacer: function() {
			return this.accionesHechas.length > 0;
		},
		puedoRehacer: function() {
			return this.accionesDeshechas.length > 0;
		},
		deshacer: function() {
			if( this.puedoDeshacer() ) {
				var accion = this.accionesHechas.pop();
				var self = this;
				accion.deshacer(function () {
					self.accionesDeshechas.push(accion);
					self.view.retirarUltimaAccion();
					self.view.actualizarControl();
					if(self.solicitarRedibujar)
						self.solicitarRedibujar();
				}, function () {
					alert("No se pudo deshacer la acción");
					self.accionesHechas.push(accion);
				});
			}
		},
		hacer: function() {
			if( this.puedoRehacer() ) {
				var accion = this.accionesDeshechas.pop();
				var self = this;
				accion.rehacer(function () {
					self.accionesHechas.push(accion);
					self.view.reponerUltimaAccion();
					self.view.actualizarControl();
					if(self.solicitarRedibujar)
						self.solicitarRedibujar();
				}, function () {
					alert("No se pudo rehacer la acción");
					self.accionesDeshechas.push(accion);
				});
			}
		},
		accion: function(index) {
			return this.accionesHechas[index];
		}
	};
	
	/* ****** Clase AccionesView ****** */
	var AccionesView = function(manejador, contenedor, controlador) {
		this.manejador = manejador;
		this.jqueryNode = $("<div></div>")
			.css("position", "relative")
			.addClass("acciones-contenido");
		contenedor.append(this.jqueryNode);
		this.controlador = controlador;
		controlador.find("#undo").click(function(){manejador.deshacer();});
		controlador.find("#redo").click(function(){manejador.hacer();});
		this.cards = [];
		this.undone_cards = [];
	};
	AccionesView.prototype = {
		_createCard: function(accion, text, description, color) {
			return $("<div></div>")
				.addClass("card")
				.addClass(color)
				.css("background-color", "#bdbdbd") // Cuando se desactiva se pone gris
				.attr("accion", accion)
				.attr("title", description)
				.attr("cardColor", color)
				.append(this._contenidoCard(text));
		},
		_contenidoCard: function(text) {
			return $("<div></div>")
				.addClass("card-content white-text truncate")
				.text(text);
		},
		_eliminarUndones: function() {
			var i;
			for(i=0;i<this.undone_cards.length;i++)
				this.undone_cards[i].remove();
			this.undone_cards = [];
		},
		nuevaAccion: function(accionIndex, text, description, color) {
			var self = this;

			self._eliminarUndones();

			var newCard = self._createCard(accionIndex, text, description, color);
			if( self.cards.length > 3 ) {
				self.jqueryNode.append(newCard);
				// La última hace fade-out y se elimina
				self.cards[self.cards.length - 4].fadeOut(function(){
					$(this).remove();
					self.cards.splice(0, 1); // Eliminamos la primera de la lista
				});
				// Se desplazan a la izquierda todas
				self.jqueryNode.animate({'left': '-25%'}, function() {
					$(this).css('left', '0'); // Vuelve a su posición al completar
				});
			} else {
				this.jqueryNode.append(newCard);
			}
			newCard.hide().fadeIn();
			this.cards.push(newCard);
		},
		retirarUltimaAccion: function() {
			if(this.cards.length > 0) {
				var card = this.cards.pop();
				card.removeClass(card.attr("cardColor"));
				this.undone_cards.push(card);
			}
		},
		reponerUltimaAccion: function() {
			if(this.undone_cards.length > 0) {
				var card = this.undone_cards.pop();
				card.addClass(card.attr("cardColor"));
				this.cards.push(card);
			}
		},
		actualizarControl: function() {
			var u = this.controlador.find("#undo");
			var r = this.controlador.find("#redo");
			u.hide(); r.hide(); // Ocultamos ambos
			if(this.manejador.puedoDeshacer()) { u.show(); } //Mostramos undo
			if(this.manejador.puedoRehacer()) { r.show(); } // Mostramos redo
		}
	};
}( window.Historial = window.Historial || {}, jQuery )); // Fin del "namespace"