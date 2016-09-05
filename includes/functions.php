<?php

include_once dirname(__FILE__).'/db_config/psl-config.php';

class PaginaActual {
    var $dir;
    var $error;
    var $error_html;
    var $id;
    function __construct($id, $dir, $error=false, $error_html=null) {
        $this->id = $id;
        $this->dir = $dir;
        $this->error = $error;
        $this->error_html = $error_html;
    }
}

/**
 * Inicia una sesión php segura
 */
function sec_session_start() {
    $nombre_sesion = 'sec_session_id';
    $seguro = SECURE;
    $httponly = true;
    
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        $error = urlencode("Error: No se pudo iniciar una sesión segura");
        header("Location: ../public_html/?error=$error");
        exit();
    }
    
    // Obtiene los params de los cookies actuales.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
        $cookieParams["path"], 
        $cookieParams["domain"], 
        $seguro,
        $httponly);
    // Configura el nombre de sesión al configurado arriba.
    session_name($nombre_sesion);
    session_start();            // Inicia la sesión PHP.
    session_regenerate_id();    // Regenera la sesión, borra la previa.
}

/**
 * @param $login_name string
 * @param $codigo string
 * @param $mysqli mysqli
 * @return bool TRUE si se ha iniciado sesión, FALSE si el inicio ha fallado
 */
function login($login_name, $codigo, $mysqli) {
    $mysqli->autocommit(false);
    $mysqli->query("START TRANSACTION");
    if( $statement = $mysqli->prepare(
            "SELECT _id, codigo, salt ".
            "FROM Comedores ".
            "WHERE loginName = ? ".
            "LIMIT 1") ){
        $statement->bind_param('s', $login_name);
        $statement->execute();
        $statement->store_result();

        if ($statement->num_rows == 1) {
            $statement->bind_result($id_comedor, $db_codigo, $salt);
            $statement->fetch();
 
            $codigo = hash('sha512', $codigo . $salt);
		
            if (checkbrute($id_comedor, $mysqli) === true) {
                // TODO: Hacer captcha jaja salu2
                return false;
            } else {
                // Comprobamos si hay una conexión activa (no se permiten conexiones simultáneas)
                if ( $statement = $mysqli->prepare(
                    "SELECT c.`initialTimestamp`, u.`string`
                     FROM Conexiones c JOIN UserAgents u ON (c.`userAgent_id` = u.`_id`) 
                     WHERE c.`comedor_id` = ? AND c.`terminated` = 0 
                     LIMIT 1") ) {
                    $statement->bind_param('i', $id_comedor);
                    $statement->execute();
                    $statement->store_result();

                    if ($statement->num_rows > 0) {
                        // Otra sesión ya ha sido iniciada y no ha finalizado, se fuerza a finalizar
                        $statement->bind_result($timestamp, $agent);
                        $statement->fetch();

                        if( $updateStmt = $mysqli->prepare(
                            "UPDATE Conexiones ".
                            "SET `terminated` = 1, `forced` = 1 ".
                            "WHERE comedor_id = ? AND initialTimestamp = ? ".
                            "LIMIT 1") ) {
                            $updateStmt->bind_param('is', $id_comedor, $timestamp);
                            $updateStmt->execute();
                            $updateStmt->close();
                        }
                    }
                    $statement->close();

                    if ($db_codigo == $codigo && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
                        // Configuramos todas las variables de sesión
                        $navegador_web = $_SERVER['HTTP_USER_AGENT'];
                        //  Protección XSS ya que podríamos imprimir este valor.
                        $id_comedor = preg_replace("/[^0-9]+/", "", $id_comedor);
                        $_SESSION['id_comedor'] = $id_comedor;
                        // Protección XSS ya que podríamos imprimir este valor.
                        $login_name = preg_replace("/[^a-zA-Z0-9_\\-]+/", "", $login_name);
                        $_SESSION['login_name'] = $login_name;
                        $login_string = hash('sha512', $codigo . $navegador_web);
                        $_SESSION['login_string'] = $login_string;
                        $addr = $_SERVER['REMOTE_ADDR'];
                        $timestamp = date('Y-m-d H:i:s');
                        $_SESSION['session_timestamp'] = $timestamp;

                        // Obtenemos/registramos el HTTP_USER_AGENT
                        $id_agent = obtenerRegistrarAgent($navegador_web, $mysqli);

                        // Insertamos la nueva conexión
                        if ($id_agent > 0 && $statement = $mysqli->prepare(
                                "INSERT INTO Conexiones(comedor_id, userAgent_id, loginString, lastActivity, initialTimestamp, initialAddress) " .
                                "VALUES(?, ?, ?, ?, ?, ?)")
                        ) {
                            $statement->bind_param('iissss', $id_comedor, $id_agent, $login_string, $timestamp, $timestamp, $addr);
                            $res = $statement->execute();
                            $statement->close();
                            $mysqli->commit();
                            return $res;
                        }

                        // Si no se ha podido registrar en la base, nada
                        $_SESSION = array();
                        $mysqli->rollback();
                        return false;
                    } else {
                        // Error de contraseña, se graba este intento en la base de datos.
                        $mysqli->query("INSERT INTO IntentosLogin(comedor)
                                        VALUES ('$id_comedor')");
                        $mysqli->commit();
                        return false;
                    }
                }
                $mysqli->rollback();
                return false;
            }
        } else {
            // El usuario no existe.
            $mysqli->rollback();
            return false;
        }
    }
    $mysqli->rollback();
    return false;
}

/**
 * Obtiene o registra el agente indicado en la base de datos.
 * @param $agent string Agente
 * @param $mysqli mysqli Conexión
 * @return bool
 */
function obtenerRegistrarAgent($agent, $mysqli) {
    $agentId = -1;
    if( $stm = $mysqli->prepare(
        "SELECT _id FROM UserAgents WHERE `string` = ? LIMIT 1") ) {
        $stm->bind_param('s', $agent);
        $stm->execute();
        $stm->store_result();
        if( $stm->num_rows == 1 ) {
            $stm->bind_result($id);
            $stm->fetch();
            $agentId = $id;
        } else {
            if( $stm2 = $mysqli->prepare("INSERT INTO UserAgents(string) VALUES(?)") ) {
                $stm2->bind_param('s', $agent);
                $stm2->execute();
                $agentId = $stm2->insert_id;
                $stm2->close();
            }
        }
        $stm->close();
    }
    return $agentId;
}

/**
 * @param $id_comedor integer
 * @param $mysqli mysqli
 * @return bool
 */
function checkbrute($id_comedor, $mysqli) {
    // Obtiene el timestamp del tiempo actual.
    $now = time();
 
    // Todos los intentos de inicio de sesión se cuentan desde las 2 horas anteriores.
    $intentos_validos = $now - (2 * 60 * 60);
 
    if ($statement = $mysqli->prepare("
            SELECT time 
            FROM IntentosLogin 
            WHERE comedor = ? 
            AND time > '$intentos_validos'")) {
        $statement->bind_param('i', $id_comedor);
 
        $statement->execute();
        $statement->store_result();
 
        // Si ha habido más de 5 intentos de inicio de sesión fallidos.
        if ($statement->num_rows > 5) {
            return true;
        } else {
            return false;
        }
    } else
        die("Error de preparacion de consulta bruteforce");
}

/**
 * @return bool
 */
function login_check() {
    global $mysqli;
    static $login_checked = false;
    static $loged = false;

    if($login_checked) return $loged;

    // Revisa si todas las variables de sesión están configuradas.
    if (isset($_SESSION['id_comedor'], $_SESSION['login_name'], $_SESSION['login_string'], $_SESSION['session_timestamp'])) {
        $id_comedor = $_SESSION['id_comedor'];
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['login_name'];
 
        // Obtiene la cadena de agente de usuario del usuario.
        $navegador_web = $_SERVER['HTTP_USER_AGENT'];
 
        if ($statement = $mysqli->prepare("
                SELECT codigo 
                FROM Comedores 
                WHERE _id = ? AND loginName = ?
                LIMIT 1")) {
            $statement->bind_param('is', $id_comedor, $username);
            $statement->execute();
            $statement->store_result();
 
            if ($statement->num_rows == 1) {
                // Si el usuario existe, obtiene la contraseña
                $statement->bind_result($codigo);
                $statement->fetch();
                $login_check = hash('sha512', $codigo . $navegador_web);

                // Comprobamos que la login_string es la correcta con la sesión
                if ($login_check == $login_string) {
                    // Contrastamos la sesión con la base de datos de conexiones para saber si está activa
                    $timestamp = $_SESSION['session_timestamp'];
                    if( $stmt = $mysqli->prepare(
                            "SELECT `terminated` ".
                            "FROM Conexiones ".
                            "WHERE comedor_id = ? AND initialTimestamp = ? ".
                            "LIMIT 1") ) {
                        $stmt->bind_param("is", $id_comedor, $timestamp);
                        $stmt->execute();
                        $stmt->store_result();

                        if( $stmt->num_rows == 1) {
                            // Está terminada?
                            $stmt->bind_result($terminada);
                            $stmt->fetch();
                            $stmt->close();

                            // Marcamos actividad
                            $ahora = date('Y-m-d H:i:s');
                            if( $updateStmt = $mysqli->prepare(
                                "UPDATE Conexiones ".
                                "SET lastActivity = ? ".
                                "WHERE comedor_id = ? AND initialTimestamp = ? ".
                                "LIMIT 1") ) {
                                $updateStmt->bind_param('sis', $ahora, $id_comedor, $timestamp);
                                $updateStmt->execute();
                                $updateStmt->close();
                            }

                            // Si no está terminada estamos conectados
                            $login_checked = true;
                            $loged = !$terminada;
                            return !$terminada;
                        } else {
                            // No hay conexion en la base correspondiente
                            $login_checked = true;
                            $loged = false;
                            return false;
                        }
                    } else {
                        // Error al preparar la consulta a la base
                        return false;
                    }
                } else {
                    // No coinciden loginString y el check
                    $login_checked = true;
                    $loged = false;
                    return false;
                }
            } else {
                // No existe el usuario
                $login_checked = true;
                $loged = false;
                return false;
            }
        } else {
            // No se pudo preparar la consulta de usuario
            return false;
        }
    } else {
        // Falta alguna variable de sesión
        $login_checked = true;
        $loged = false;
        return false;
    }
}

function esc_url($url) {
    if ('' == $url) {
        return $url;
    }
 
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
 
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
 
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }
 
    $url = str_replace(';//', '://', $url);
 
    $url = htmlentities($url);
 
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);
 
    if ($url[0] !== '/') {
        // Solo nos interesan los enlaces relativos de  $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

/**
 * Obtiene la página actual a cargar... si el parámetro lookFor se deja en blanco será cubierto con la entrada filtrada
 * de $c. Tener en cuenta que esta función solo escoge páginas definidas en direcciones.json y ateniéndose a sus
 * restricciones de login. Para incluir la página actual devuelta por esta función emplear include_content.
 * La pagina actual solo es buscada una vez, si se ha encontrado ya en alguna llamada previa se conserva en una variable
 * estática para evitar realizar la búsqueda de nuevo.
 * @param null|string $lookFor Cadena a buscar en direcciones.json para obtener la pagina actual
 * @return PaginaActual La pagina que se debe mostrar actualmente
 */
function obtenerPaginaActual($lookFor=null) {
    static $json = null;
    static $pagActual = null;

    if($pagActual !== null)
        return $pagActual;

    if($lookFor === null)
        $lookFor = filter_input(INPUT_GET, 'c', $filter = FILTER_SANITIZE_STRING);

    if($json === null)
        $json = json_decode(file_get_contents(dirname(__FILE__)."/direcciones.json"), true);

    if(isset($json[$lookFor])) {
        $nodo = $json[$lookFor];
        if(isset($nodo['if_not_logged']) && !login_check()) {
            return obtenerPaginaActual($nodo['if_not_logged']);
        } else if(isset($nodo['if_logged']) && login_check()) {
            return obtenerPaginaActual($nodo['if_logged']);
        } else {
            if (seteada('error')) {
                $pagActual = new PaginaActual($lookFor, $nodo['dir'], true, obtener('error'));
            } else {
                $pagActual = new PaginaActual($lookFor, $nodo['dir']);
            }
            return $pagActual;
        }
    } else {
        return obtenerPaginaActual('inicio');
    }
}

/**
 * Incluye el contenido de la pagina actual indicada, esta pagina debe estar SIEMPRE seleccionada
 * mediante obtenerPaginaActual
 * @param $paginaActual PaginaActual Pagina actual seleccionada mediante la funcion obtenerPaginaActual
 */
function include_content($paginaActual) {
    if($paginaActual->error)
        echo $paginaActual->error_html;
    else
        include dirname(__FILE__)."/../contenido/".$paginaActual->dir;
}

/**
 * Escribe la inclusión en html del script indicado
 * @param $script array(src=>string,type=>string,async=>bool,defer=>bool) Los datos del script a incluír
 */
function script_include($script) {
    if(isset($script['src'])) $src = $script['src'];
    else return;
    if(isset($script['type'])) $type = $script['type'];
    else $type = 'text/javascript';
    if(isset($script['async'])) $async = $script['async'];
    else $async = true;
    if(isset($script['defer'])) $defer = $script['defer'];
    else $defer = false;

    echo "<script type='$type' src='$src' ".($async?"async":"")." ".($defer?"defer":"")."></script>\n";
}

function scripts_include() {
    $json = json_decode(file_get_contents(dirname(__FILE__)."/scripts.json"), true);
    if($json)
        foreach ($json as $script) {
            script_include($script);
        }
}

function link_include($link_info, $rel) {
    $href = null;
    if(isset($link_info['href']))
        $href = $link_info['href'];

    if(!($href && $rel)) return;

    $type = $media = $hreflang = $sizes = null;
    if(isset($link_info['type'])) $type = $link_info['type'];
    if(isset($link_info['media'])) $media = $link_info['media'];
    if(isset($link_info['hreflang'])) $hreflang = $link_info['hreflang'];
    if(isset($link_info['sizes'])) $sizes = $link_info['sizes'];

    echo "<link rel='$rel' href='$href' type='$type'";
    if($media)
        echo " media='$media'";
    if($hreflang)
        echo " hreflang='$hreflang'";
    if($sizes)
        echo " sizes='$sizes'";
    echo ">\n";
}

function links_include() {
    $json = json_decode(file_get_contents(dirname(__FILE__)."/links.json"), true);
    if($json)
        foreach ($json as $rel => $array) {
            foreach ($array as $name => $link) {
                link_include($link, $rel);
            }
        }
}

function meta_include($name, $content, $http_equiv=null) {
    echo "<meta name='$name' content='$content'";
    if($http_equiv)
        echo " http-equiv='$http_equiv'";
    echo ">\n";
}

function metas_include() {
    $json = json_decode(file_get_contents(dirname(__FILE__)."/metas.json"), true);
    if($json)
        foreach ($json as $name => $content) {
            meta_include($name, $content);
        }
}

/**
 * Muestra el error como una salida JSON
 * @param $error string Cadena que describe el error
 * @return string JSON resultante
 */
function errorJson($error) {
    $array = array( 'status' => 'ERROR', 'error' => $error);
    return json_encode($array);
}

/**
 * Muestra el exito como una salida JSON (increíble, ¿verdad? Nunca pensaste que verías el éxito, como tal,
 * representado en JSON).
 * @param $respuesta mixed Valor para el campo respuesta del JSON
 * @return string JSON resultante
 */
function exitoJson($respuesta) {
    $array = array( 'status' => 'OK', 'respuesta' => $respuesta);
    return json_encode($array);
}

/**
 * Comprueba si una variable ha sido seteada
 * @param $key string Clave de la variable
 * @return bool
 *
 * @deprecated Utilizar solo obtener
 */
function seteada($key) {
	return isset($_GET[$key]) || isset($_POST[$key]);
}

/**
 * Comprueba si una variable ha sido seteada y es numérica
 * @param $key string Clave de la variable
 * @return bool
 *
 */
function seteada_y_numerica($key) {
	return (isset($_GET[$key]) && is_numeric($_GET[$key])) ||
		(isset($_POST[$key]) && is_numeric($_POST[$key]));
}

/**
 * Obtiene la variable pasada por GET o POST y la filtra
 * @param $key string Clave de la variable
 * @param $filtro int Filtro a aplicar mediante filter_var
 * @return mixed|null El valor filtrado o null en caso de no encontrarse esa variable ni en GET ni en POST
 */
function obtener($key, $filtro=FILTER_UNSAFE_RAW) {
    if( isset($_GET[$key]) || isset($_POST[$key]) )
	    return filter_var(isset($_GET[$key])?$_GET[$key]:$_POST[$key], $filtro);
    else
        return null;
}

/**
 * Imprime un icono de la fuente de google, se puede elegir el color, fondo o tamaño
 * Para conocer los valores adecuados a los parametros consultar la documentación de
 * materializecss.
 * @param string $icon_name icon name
 * @param null|string $color color for the icon, without -text, i.e. 'amber'
 * @param null|string $background color for the background of the i element, i.e. 'red'
 * @param null|string $size size of the icon (see materializecss), i.e. 'small'
 */
function printIcon($icon_name, $color = null, $background = null, $size = null)
{
    if ($color) $color = ' ' . $color . '-text';
    else            $color = '';
    if ($background) $background = ' ' . $background;
    else            $background = '';
    if ($size) $size = ' ' . $size;
    else            $size = '';
    printf("<i class='material-icons%s%s%s'>%s</i>", $color, $background, $size, $icon_name);
}