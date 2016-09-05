<?php
/**
 * Funciones necesarias en contenido/comedores/view_misPlatos.php
 * David Campos Rodríguez
 * 31/08/2016
 */

require_once dirname(__FILE__) . "/../../includes/model/MisPlatos.php";

/**
 * Formatea el tipo del plato a una cadena más legible
 * @param string $tipo Tipo del plato a formatear
 * @return string El tipo en un formato humanamente legible
 */
function formatearTipo($tipo) {
    switch(substr($tipo,0,1)) {
        case '0':
            return 'Primero';
        case '1':
            return 'Segundo';
        case '2':
            return 'Postre';
        default:
            return 'Desconocido';
    }
}

/**
 * Imprime la lista de platos paginada
 * @param $page int Número de página a imprimir
 * @param $pagesize int Tamaño de las páginas
 * @param $total &int Variable donde almacenar el número total de platos (si se desea)
 */
function printListaMisPlatos($page, $pagesize, &$total=null) {
    // Ajustamos $page
    $total = misPlatosCount($_SESSION['id_comedor']);
    $paginas = ceil($total/$pagesize);
    if( $page < 0) $page = 0;
    if( $page >= $paginas) $page = $paginas-1;

    $misPlatos = obtenerMisPlatos($_SESSION['id_comedor'], $page, $pagesize);
    foreach( $misPlatos as $plato) {
        ?>
        <tr <?php echo ($plato['prestado']?"class='prestado'":""); ?> idPlato="<?php echo $plato['_id']; ?>">
            <td><?php echo $plato['nombre']; ?></td>
            <td><?php echo $plato['descripcion']; ?></td>
            <td tipo="<?php echo $plato['tipo'][0]; ?>"><?php echo formatearTipo($plato['tipo']); ?></td>
            <td><a href="#" class="borrarMiPlato"><i class="material-icons red-text">delete</i></a></td>
        </tr>
        <?php
    }
}

/**
 * Imprime el índice de paginación de Mis Platos
 * @param $page int Número de página actual
 * @param $pagesize int Tamaño de las páginas
 * @param $total int Número total de platos
 */
function printPaginacionMisPlatos($page, $pagesize, $total) {
    $paginas = ceil($total/$pagesize);

    if( $paginas < 2 ) return; //Si no hay más que una página no se muestra nada

    // Ajustamos $page
    if( $page < 0) $page = 0;
    if( $page >= $paginas) $page = $paginas-1;

    // Inicio
    $code = '<ul class="pagination">';
    if( $page == 0 ) {
        $code .= '<li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>';
    } else {
        $code .= '<li class="waves-effect"><a href="?pag='.($page-1).'"><i class="material-icons">chevron_left</i></a></li>';
    }

    // Paginas
    for($i=0;$i<$paginas;$i++) {
        if($i == $page) {
            $code .= '<li class="active"><a>' . ($i + 1) . '</a></li>';
        } else {
            $code .= '<li class="waves-effect"><a href="?pag='.$i.'">' . ($i + 1) . '</a></li>';
        }
    }

    // Fin
    if($page == $paginas-1) {
        $code .= '<li class="disabled"><a><i class="material-icons">chevron_right</i></a></li>';
    } else {
        $code .= '<li class="waves-effect"><a href="?pag='.($page+1).'"><i class="material-icons">chevron_right</i></a></li>';
    }
    $code .= '</ul>';

    echo $code;
}