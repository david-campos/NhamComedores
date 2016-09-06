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
    protected static function sp_aNumero($dia) {
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
    protected static function sp_enPlazo($dia, $numAp, $numCi) {
        if (!is_numeric($dia)) $dia = ComedorTO::sp_aNumero($dia);
        if (!is_numeric($numAp)) $numAp = ComedorTO::sp_aNumero($numCi);
        if (!is_numeric($numCi)) $numCi = ComedorTO::sp_aNumero($numCi);

        if ($numAp < $numCi) {
            return $numAp <= $dia && $dia <= $numCi;
        } else {
            return $numAp <= $dia || $dia <= $numCi;
        }
    }

    /**
     * Formatea la hora, retirando los segundos.
     *
     * @param string $hora La hora en formato 'hh:mm:ss'
     * @return string La hora en formato 'hh:mm'
     */
    protected static function sp_formatearHora($hora) {
        return substr($hora, 0, strrpos($hora, ':'));
    }

    // PARTE PÚBLICA
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
        return sprintf("<p>%s - %s</p>",
            ComedorTO::sp_formatearHora($this->_horarioComedor[0]),
            ComedorTO::sp_formatearHora($this->_horarioComedor[1]));
    }

    /**
     * Obtiene la url de la imagen del comedor
     * @return string La url para obtener la imagen del comedor
     */
    public function getImagen() {
        return "/api/imagenes/$this->_id/";
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

    // PARTE PRIVADA
    private function _getDiasAperturaEnHtml() {
        $dias = array('L', 'Ma', 'Mi', 'J', 'V', 'S', 'D');
        $ret = "<p>";
        for ($i = 0; $i < 7; $i++) {
            $ret .= "<span class='dia ";
            if (ComedorTO::sp_enPlazo($i, $this->_apertura['dias'][0], $this->_apertura['dias'][1])) {
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
            ComedorTO::sp_formatearHora($this->_apertura['horas'][0]),
            ComedorTO::sp_formatearHora($this->_apertura['horas'][1]));
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