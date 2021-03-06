<?php
/**
 * Modelo de la tabla Comedores, empiezo a introducir la orientación a objetos, por lo que puede que solo
 * esto quede orientado a objetos por ahora, pero en un futuro la intención es cambiar everything a orientado a objetos.
 * Emplea patrón DAO con AbstractFactory
 * @author David Campos Rodríguez
 */

/**
 * Class ComedorTO, representación en la web de un Comedor de la base, emplear el abstract factory para
 * crear instancias de esta clase
 */
class ComedorTO
{
    /**
     * ComedorTO constructor.
     * @param int $_id
     * @param int $_universidad
     * @param string $_nombre
     * @param array $_horarioComedor
     * @param array $_coordenadas
     * @param string $_tlfn
     * @param null|string $_nombreContacto
     * @param string $_direccion
     * @param array $_apertura
     * @param string $_promocion
     * @param string $_codigo
     * @param string $_salt
     * @param string $_loginName
     */
    public function __construct($_id, $_universidad, $_nombre, array $_horarioComedor, array $_coordenadas, $_tlfn, $_nombreContacto, $_direccion, array $_apertura, $_promocion, $_codigo, $_salt, $_loginName) {
        $this->_id = $_id;
        $this->_universidad = $_universidad;
        $this->_nombre = $_nombre;
        $this->_horarioComedor = $_horarioComedor;
        $this->_coordenadas = $_coordenadas;
        $this->_tlfn = $_tlfn;
        $this->_nombreContacto = $_nombreContacto;
        $this->_direccion = $_direccion;
        $this->_apertura = $_apertura;
        $this->_promocion = $_promocion;
        $this->_codigo = $_codigo;
        $this->_salt = $_salt;
        $this->_loginName = $_loginName;
    }

    // PARTE ESTÁTICA
    /**
     * Convierte un número que represente un día de la semana a la numeración
     * que emplea la web para días de la semana
     * @param $dia int dia en numeración standard (de PHP, 0=domingo-6=sabado)
     * @return int dia en la numeración de la web (0=lunes-6=domingo)
     */
    public static function sEnMiNumeracion($dia) {
        return ($dia + 6) % 7;
    }

    /**
     * Convierte el día de la semana devuelto por la API a su número.
     *
     * Convierte una cadena de carácteres válida al número de día de la semana
     * correspondiente, comenzando en cero.
     * Las cadenas aceptadas son:
     * - lunes (0)
     * - martes (1)
     * - miercoles (2)
     * - jueves (3)
     * - viernes (4)
     * - sabado (5)
     * - domingo (6)
     * En caso de que la cadena no sea válida devolverá -1.
     *
     * @param string $dia Un día de la semana
     * @return integer Su número correspondiente, comenzando en 0, o -1 en caso de
     *     cadena inválida.
     */
    public static function sEnNumero($dia) {
        switch ($dia) {
            case 'lunes':
                return 0;
            case 'martes':
                return 1;
            case 'miercoles':
                return 2;
            case 'jueves':
                return 3;
            case 'viernes':
                return 4;
            case 'sabado':
                return 5;
            case 'domingo':
                return 6;
            default:
                return -1;
        }
    }

    /**
     * Comprueba si el día de la semana indicado se encuentra entre dos días
     * dados, de forma circular.
     *
     * Dados tres días de la semana, de forma numérica o en cadena, comprueba
     * si el primero se encuentra entre los otros dos.
     *
     * @see enPlazo Para las cadenas de entrada admitidas
     *
     * @param integer|string $dia Día de la semana a comparar con los otros dos
     * @param integer|string $numAp Día de la semana que inicia el intervalo de comparación
     * @param integer|string $numCi Día de la semana que finaliza el intervalo de comparación
     * @return bool TRUE si el día se encuentra entre los dos días dados, FALSE en caso contrario
     */
    public static function sEnPlazo($dia, $numAp, $numCi) {
        if (!is_numeric($dia)) $dia = ComedorTO::sEnNumero($dia);
        if (!is_numeric($numAp)) $numAp = ComedorTO::sEnNumero($numAp);
        if (!is_numeric($numCi)) $numCi = ComedorTO::sEnNumero($numCi);

        if ($numAp < $numCi) {
            return $numAp <= $dia && $dia <= $numCi;
        } else {
            return $numAp <= $dia || $dia <= $numCi;
        }
    }

    /**
     * Comprueba si una hora se encuentra entre otras dos dadas
     * @param $hora string hora a comparar en formato 'H:i:s'
     * @param $inicio string hora de inicio en formato 'H:i:s'
     * @param $fin string hora de fin en formato 'H:i:s'
     * @return bool
     */
    public static function sHoraEnPlazo($hora, $inicio, $fin) {
        $strpos = strpos($hora, ':');
        $h = intval(substr($hora, 0, $strpos));
        $m = intval(substr($hora, $strpos, strrpos($hora, ':')));

        $strpos = strpos($inicio, ':');
        $inicio_h = intval(substr($inicio, 0, $strpos));
        $inicio_m = intval(substr($inicio, $strpos, strrpos($inicio, ':')));

        $strpos = strpos($fin, ':');
        $fin_h = intval(substr($fin, 0, $strpos));
        $fin_m = intval(substr($fin, $strpos, strrpos($fin, ':')));

        return ($inicio_m * 60 + $inicio_h) <= ($m * 60 + $h) && ($m * 60 + $h) <= ($fin_m * 60 + $fin_h);
    }

    /**
     * Formatea la hora, retirando los segundos.
     *
     * @param string $hora La hora en formato 'hh:mm:ss'
     * @return string La hora en formato 'hh:mm'
     */
    public static function sFormatearHora($hora) {
        return substr($hora, 0, strrpos($hora, ':'));
    }

    // PARTE PÚBLICA
    /**
     * Indica si el comedor se encuentra en horario de comedor actualmente
     */
    public function enHorarioDeComedor() {
        $apertura = $this->getApertura();
        $horario = $this->getHorarioComedor();
        $diaEnPlazo = ComedorTO::sEnPlazo(
            ComedorTO::sEnMiNumeracion(date('w')), $apertura['dias'][0], $apertura['dias'][1]);
        $horaEnPlazo = ComedorTO::sHoraEnPlazo(date('H:i:s'), $horario[0], $horario[1]);

        return $diaEnPlazo && $horaEnPlazo;
    }

    /**
     * Obtiene la apertura del comedor en HTML en un formato legible para cualquiera
     * @return string El html que explica la apertura
     */
    public function getAperturaEnHtml() {
        return $this->_getDiasAperturaEnHtml() . $this->_getHorasAperturaEnHtml();
    }

    /**
     * Obtiene el horario de comedor en HTML, en un formato legible
     * @return string Html que indica el horario de comedor
     */
    public function getHorarioComedorEnHtml() {
        return sprintf("%s - %s",
            ComedorTO::sFormatearHora($this->_horarioComedor[0]),
            ComedorTO::sFormatearHora($this->_horarioComedor[1]));
    }

    /**
     * Comprueba si el comedor dispone de imagen
     * @return bool TRUE si tiene miniatura, FALSE si no
     */
    public function tieneImagen() {
        return file_exists(dirname(__FILE__) .
            '/../../public_html/api/imagenes/detail_' . $this->getId() . '.png');
    }

    /**
     * Obtiene la url de la imagen del comedor
     * @return string La url para obtener la imagen del comedor
     */
    public function getImagen() {
        return "/api/imagenes/$this->_id/";
    }

    /**
     * Comprueba si el comedor dispone de miniatura
     * @return bool TRUE si tiene miniatura, FALSE si no
     */
    public function tieneMiniatura() {
        return file_exists(dirname(__FILE__) .
            '/../../public_html/api/miniaturas/mini_' . $this->getId() . '.png');
    }

    /**
     * Obtiene la url de la miniatura del comedor
     * @return string La url para obtener la miniatura del comedor
     */
    public function getMiniatura() {
        return "/api/miniaturas/$this->_id/";
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @return int
     */
    public function getUniversidad() {
        return $this->_universidad;
    }

    /**
     * @return string
     */
    public function getNombre() {
        return $this->_nombre;
    }

    /**
     * @return array
     */
    public function getHorarioComedor() {
        return $this->_horarioComedor;
    }

    /**
     * @return array
     */
    public function getCoordenadas() {
        return $this->_coordenadas;
    }

    /**
     * @return string
     */
    public function getTlfn() {
        return $this->_tlfn;
    }

    /**
     * @return null|string
     */
    public function getNombreContacto() {
        return $this->_nombreContacto;
    }

    /**
     * @return string
     */
    public function getDireccion() {
        return $this->_direccion;
    }

    /**
     * @return array
     */
    public function getApertura() {
        return $this->_apertura;
    }

    /**
     * @return string
     */
    public function getPromocion() {
        return $this->_promocion;
    }

    /**
     * @return string
     */
    public function getCodigo() {
        return $this->_codigo;
    }

    /**
     * @return string
     */
    public function getSalt() {
        return $this->_salt;
    }

    /**
     * @return string
     */
    public function getLoginName() {
        return $this->_loginName;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre) {
        if ($nombre !== "")
            $this->_nombre = $nombre;
    }

    /**
     * @param array $horarioComedor
     */
    public function setHorarioComedor($horarioComedor) {
        if ($this->_horaValida($horarioComedor[0]) && $this->_horaValida($horarioComedor[1]))
            $this->_horarioComedor = $horarioComedor;
    }

    /**
     * @param string $tlfn
     */
    public function setTlfn($tlfn) {
        if (preg_match("/\d{9}/", $tlfn) === 1)
            $this->_tlfn = $tlfn;
    }

    /**
     * @param null|string $nombreContacto
     */
    public function setNombreContacto($nombreContacto) {
        if ($nombreContacto === "")
            $this->_nombreContacto = null;
        else
            $this->_nombreContacto = $nombreContacto;
    }

    /**
     * @param string $direccion
     */
    public function setDireccion($direccion) {
        if ($direccion !== "")
            $this->_direccion = $direccion;
    }

    /**
     * @param array $apertura
     */
    public function setApertura($apertura) {
        if ($this->_diaValido($apertura['dias'][0]) && $this->_diaValido($apertura['dias'][1])
            && $this->_horaValida($apertura['horas'][0]) && $this->_horaValida($apertura['horas'][1])
        )
            $this->_apertura = $apertura;
    }

    /**
     * Devuelve un array que representa el objeto JSON.
     * No devuelve el código ni el salt, por motivos de seguridad.
     * @return array array asociativo de propiedadades
     */
    public function toArray() {
        $ap = $this->getApertura();
        $ho = $this->getHorarioComedor();
        $co = $this->getCoordenadas();
        return array(
            "_id" => $this->getId(),
            "diaInicioApertura" => $ap['dias'][0],
            "diaFinApertura" => $ap['dias'][1],
            "hAperturaIni" => $ap['horas'][0],
            "hAperturaFin" => $ap['horas'][1],
            "universidad" => $this->getUniversidad(),
            "nombre" => $this->getNombre(),
            "horaInicio" => $ho[0],
            "horaFin" => $ho[1],
            "coordLat" => $co[0],
            "coordLon" => $co[1],
            "telefono" => $this->getTlfn(),
            "nombreContacto" => $this->getNombreContacto(),
            "direccion" => $this->getDireccion(),
            "promocion" => $this->getPromocion(),
            "loginName" => $this->getLoginName()
        );
    }

    /**
     * @param $dia string Dia a comprobar
     * @return bool true si el dia es válido, false si no es válido para el SET de la base de datos
     */
    private function _diaValido($dia) {
        return preg_match("/lunes|martes|miercoles|jueves|viernes|sabado|domingo/", $dia) === 1;
    }

    /**
     * @param $hora string Hora a comprobar
     * @return bool true si la hora es válida, false si no es válida
     */
    private function _horaValida($hora) {
        if (preg_match("/\d{1,2}:\d{1,2}/", $hora) !== 1) return false;
        preg_match_all("/(\d{1,2})/", $hora, $values);
        if (intval($values[1][0]) > 23 || intval($values[1][0]) < 0) return false;
        if (intval($values[1][1]) > 59 || intval($values[1][1]) < 0) return false;
        return true;
    }

    /**
     * @param string $promocion
     */
    public function setPromocion($promocion) {
        $this->_promocion = $promocion;
    }

    // PARTE PRIVADA
    private function _getDiasAperturaEnHtml() {
        $dias = array('L', 'Ma', 'Mi', 'J', 'V', 'S', 'D');
        $ret = "<p>";
        for ($i = 0; $i < 7; $i++) {
            $ret .= "<span class='dia ";
            if (ComedorTO::sEnPlazo($i, $this->_apertura['dias'][0], $this->_apertura['dias'][1])) {
                $ret .= "abierto";
            } else {
                $ret .= "cerrado";
            }
            $ret .= "'>" . $dias[$i] . "</span>";
        }
        return $ret . "</p>";
    }

    private function _getHorasAperturaEnHtml() {
        return sprintf("<p>%s - %s</p>",
            ComedorTO::sFormatearHora($this->_apertura['horas'][0]),
            ComedorTO::sFormatearHora($this->_apertura['horas'][1]));
    }

    // ATRIBUTOS
    /**
     * @var int Número que identifica el comedor, se corresponde con el _id en la base
     */
    private $_id;
    /**
     * @var int Número que identifica la universidad a la cual se halla asociado este comedor
     */
    private $_universidad;
    /**
     * @var string Nombre del comedor
     */
    private $_nombre;
    /**
     * @var array(string) Inicio y fin del horario del servicio de comida
     */
    private $_horarioComedor;
    /**
     * @var array(float) Latitud y longitud del comedor
     */
    private $_coordenadas;
    /**
     * @var string Número de teléfono del comedor
     */
    private $_tlfn;
    /**
     * @var string|null Nombre del contacto que responde por el comedor al teléfono
     */
    private $_nombreContacto;
    /**
     * @var string Dirección del comedor (calle, número, etc.)
     */
    private $_direccion;
    /**
     * @var array(array) Días de apertura y cierre y horas de apertura y cierre del comedor
     */
    private $_apertura;
    /**
     * @var string Texto de promoción escogido por el comedor
     */
    private $_promocion;
    /**
     * @var string Código de acceso al servicio online del comedor (encriptado)
     */
    private $_codigo;
    /**
     * @var string Sal para el login del comedor en el servicio online
     */
    private $_salt;
    /**
     * @var string Nombre de login para el acceso al servicio online por parte del comedor
     */
    private $_loginName;
}