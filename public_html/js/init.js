/**
 * Created by David Campos R on 11/09/2016.
 * Este script debe cargarse antes de todas las páginas y de forma síncrona, facilita la sincronización
 * entre scripts
 */

/**
 * Almacena los scripts inicializados hasta el momento (para scripts que necesitan inicializacion)
 */
(function (Init, $, undefined) {
    Init.ready_scripts = {
        IntroduccionPlatos: false,
        Historial: false
    };

    Init.NEW_READY_SCRIPT_EVENT = "new-ready-script";

    Init.PP_READY_EVENT = "introduccionplatos-ready";
    Init.HST_READY_EVENT = "historial-ready";

    $(document).on(Init.PP_READY_EVENT, function () {
        Init.ready_scripts.IntroduccionPlatos = true;
        $(document).trigger(Init.NEW_READY_SCRIPT_EVENT);
    });
    $(document).on(Init.HST_READY_EVENT, function () {
        Init.ready_scripts.Historial = true;
        $(document).trigger(Init.NEW_READY_SCRIPT_EVENT);
    });
}(window.Init = window.Init || {}, jQuery)); // Fin del "namespace"