<?php
/*
 * Estructura de la cabecera de la página.
 *
 * David Campos Rodríguez
 */
include_once(dirname(__FILE__) . '/../includes/functions.php');

$arc = dirname(esc_url($_SERVER['PHP_SELF']));
$pag = obtenerPaginaActual()->id;
?>
<nav class="light-green">
    <div class="container">
        <div class="nav-wrapper">
            <a class="brand-logo">Comedores</a>
            <ul id="nav-mobile" class="right hide-on-med-and-down">
                <li <?= ($pag == "inicio") ? 'class="active"' : '' ?>>
                    <a href="<?= $arc ?>inicio/">Inicio</a></li>
                <?php
                if (!login_check()) :
                    ?>
                    <li <?= ($pag == "login") ? 'class="active"' : '' ?>>
                        <a href="<?= $arc ?>login/">Login</a></li>
                    <?php
                else:
                    include_once dirname(__FILE__) . '/comedores/view_cabecera.php';
                endif;
                ?>
            </ul>
        </div>
    </div>
</nav>
