<?php
/**
 * Modelo para la tabla TiposMenu
 * @author David Campos Rodríguez
 */

/**
 * Class TipoMenuTO, representa a un tipo de menú en la web, emplear el AbstractFactory+DAO para crear este
 * TO.
 */
class TipoMenuTO
{
    /**
     * TiposMenuTO constructor.
     * @param int $_id _id del menú
     * @param string $_nombre nombre del menú
     * @param float $_precio precio del menú
     * @param int $_idComedor id del comedor
     */
    public function __construct($_id, $_nombre, $_precio, $_idComedor) {
        $this->_id = $_id;
        $this->_nombre = $_nombre;
        $this->_precio = $_precio;
        $this->_idComedor = $_idComedor;
    }

    /**
     * Obtiene el nombre del tipo de menú
     * @return string
     */
    public function getNombre() {
        return $this->_nombre;
    }

    /**
     * Cambia el nombre del tipo de menú
     * @param string $nombre
     */
    public function setNombre($nombre) {
        if (!empty($nombre))
            $this->_nombre = $nombre;
    }

    /**
     * Obtiene el precio
     * @return float
     */
    public function getPrecio() {
        return $this->_precio;
    }

    /**
     * Fija el precio
     * @param float $precio
     */
    public function setPrecio($precio) {
        if (is_numeric($precio) && $precio >= 0)
            $this->_precio = $precio;
    }

    /**
     * Obtiene el _id del comedor al que se encuentra asociado el menú
     * @return int
     */
    public function getIdComedor() {
        return $this->_idComedor;
    }

    /**
     * Fija el _id del comedor al que se encuentra asociado el menú
     * @param int $idComedor
     */
    public function setIdComedor($idComedor) {
        if (is_numeric($idComedor) && $idComedor > 0)
            $this->_idComedor = $idComedor;
    }

    /**
     * Obtiene el _id del menú
     * @return int
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @var int _id que identifica al menú
     */
    private $_id;
    /**
     * @var string Nombre del menú
     */
    private $_nombre;
    /**
     * @var float precio del menú
     */
    private $_precio;
    /**
     * @var int id del comedor al que se encuentra asociado el menú
     */
    private $_idComedor;
}