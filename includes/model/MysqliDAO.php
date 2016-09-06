<?php
/**
 * Archivo para la implementación para mysqli de los DAOs
 */

require_once dirname(__FILE__) . "/DAO.php";
require_once dirname(__FILE__) . "/Comedores.php"; // ComedorTO
require_once dirname(__FILE__) . "/../db_connect.php"; // Obtiene mysqli

/**
 * Class MysqliDAOFactory, implementa DAOFactory para mysqli
 */
class MysqliDAOFactory implements IDAOFactory
{
    /**
     * @var mysqli Link de mysqli para la conexión a la base
     */
    private $_mysqli;

    /**
     * mysqliDAOFactory constructor.
     */
    public function __construct() {
        global $mysqli;
        $this->_mysqli = $mysqli;
    }

    /**
     * @return IComedoresDAO DAO para el manejo de Comedores en la base
     */
    public function obtenerComedoresDAO() {
        return new MysqliComedoresDAO($this->_mysqli);
    }
}

/**
 * Clase abstracta para todos los DAO de mysqli
 */
abstract class MysqliDAO
{
    /**
     * @var mysqli Link de mysqli para la conexión a la base
     */
    private $_mysqli;

    /**
     * mysqliDAOFactory constructor.
     * @param $link mysqli Link mysqli de conexión a la base
     */
    public function __construct($link) {
        $this->_mysqli = $link;
    }

    /**
     * @return mysqli
     */
    public function getMysqli() {
        return $this->_mysqli;
    }
}


/**
 * Class MysqliComedoresDAO, implementación del acceso a la base en busca de comedores
 * mediante mysqli
 */
class MysqliComedoresDAO extends MysqliDAO implements IComedoresDAO
{
    /**
     * Obtiene el transfer object para el Comedor identificado por su id
     * @param int $id Id del comedor cuyo transfer object se desea obtener
     * @return ComedorTO|null Transfer object para el comedor deseado o null, si el comedor no existe
     * @throws Exception si no se puede obtener el transfer object
     */
    public function obtenerComedorTO($id) {
        $mysqli = $this->getMysqli();
        if ($stmt = $mysqli->prepare(
            "SELECT nombre, universidad, horaInicio, horaFin, coordLat, coordLon, telefono, nombreContacto,
			direccion, hAperturaIni, hAperturaFin, diaInicioApertura, diaFinApertura, promocion, codigo, salt, loginName
	        FROM Comedores
	        WHERE _id = ?"
        )
        ) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($nombre, $universidad, $horaInicio, $horaFin, $coordLat,
                $coordLon, $telefono, $nombreContacto, $direccion, $hAperturaIni,
                $hAperturaFin, $diaInicioApertura, $diaFinApertura, $promocion, $codigo, $sal, $login);
            if ($stmt->fetch()) {
                $comedorTO = new ComedorTO(
                    $id,
                    $universidad,
                    $nombre,
                    array($horaInicio, $horaFin),
                    array($coordLat, $coordLon),
                    $telefono,
                    $nombreContacto,
                    $direccion,
                    array(
                        "dias" => array($diaInicioApertura, $diaFinApertura),
                        "horas" => array($hAperturaIni, $hAperturaFin)),
                    $promocion,
                    $codigo,
                    $sal,
                    $login
                );
                $stmt->close();
                return $comedorTO;
            } else {
                $stmt->close();
                return null;
            }

        } else
            throw new Exception('ComedoresDAO->obtenerComedorTO:prepare: ' . $mysqli->error);
    }
}
