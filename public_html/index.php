<?php
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();
?>
<!DOCTYPE html>
<!-- David Campos R. on 24/03/2016 -->
<html>
<head>
    <meta charset="UTF-8">
    <title> &Ntilde;am! </title>
    <?php scripts_include(); ?>
    <?php links_include(); ?>
    <?php metas_include(); ?>
</head>
<body>
<?php
require(dirname(__FILE__) . '/../contenido/view_cabecera.php');
?>
<main class="valign-wrapper">
    <div class="container">
        <?php include_content(obtenerPaginaActual()); ?>
    </div>
</main>
<?php
require(dirname(__FILE__) . '/../contenido/view_footer.php');
?>
</body>
</html>
