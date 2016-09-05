/*
 * comedores.js
 * Completa de forma dinámica la información del comedor seleccionado en inicio.php
 * 
 *
 * Lorenzo Vaquero Otal
 * 12/08/2016
 */
$(document).ready(function(){
				$("div#imagenComedor").parallax();
				$("ul#listaComedores li").click(function() {
						$("ul#listaComedores").hide();
						$("div#detallesComedor").show();
						var a = $.get("comedoresusc.site88.net", {tipo: 4, id: 1}, function(){}, "text");
						$("div#contenido").append($("<p></p>")
							.html( $(this)
								.find("span.title h6").text()));
						});
});