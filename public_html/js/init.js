/**
 * Created by David Campos R on 11/09/2016.
 * Este script debe cargarse antes de todas las páginas y de forma síncrona, facilita la sincronización
 * entre scripts
 */
(function (IntroduccionPlatos, $, undefined) {
    /* Parte publica */
    /**
     * Evento lanzado sobre document cuando el modal está listo para empezar a usarse
     * @type {string}
     */
    IntroduccionPlatos.READY_EVENT = 'introduccionplatos-ready';
}(window.IntroduccionPlatos = window.IntroduccionPlatos || {}, jQuery)); // Fin del "namespace"