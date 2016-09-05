/*
 * Script que simplemente se ocupa de la codificación en sha512 de la contraseña
 * del usuario antes de enviarla en el login.
 *
 * David Campos Rodríguez
 */
$(document).ready(function() {
	$("#formularioLogin").submit(formhash);
});

function formhash(event) {
	var form = $(this);
	var password = form.find("#password");
	
    // Crea una entrada de elemento nuevo, esta será nuestro campo de contraseña con hash. 
    var p = $("<input>");
 
    // Agrega el elemento nuevo a nuestro formulario. 
    form.append(p);
    p.attr('name', "p");
    p.attr('type', "hidden");
	var shaObj = new jsSHA("SHA-512", "TEXT");
	shaObj.update(password.val());
    p.val(shaObj.getHash("HEX"));
 
    // Asegúrate de que la contraseña en texto simple no se envíe. 
    password.val("");
	
	// Ponemos el cargando en lugar de este form
	$("div.row.login").hide();
	$("div.row.cargando").show();
}
