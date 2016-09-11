<?php
/**
 * Archivo para la implementación para mysqli de los DAOs
 */

require_once dirname(__FILE__) . "/DAO.php";
require_once dirname(__FILE__) . "/ComedorTO.php"; // ComedorTO
require_once dirname(__FILE__) . "/TipoMenuTO.php"; // TipoMenuTO
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

    /**
     * @return ITiposMenuDAO Crea el DAO de acceso a los tipos de menú de la base
     */
    public function obtenerTiposMenuDAO() {
        return new MysqliTiposMenuDAO($this->_mysqli);
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

    /**
     * @param $to ComedorTO El TO con el que actualizar el comedor
     * @throws Exception Si no se puede realizar la actualización
     */
    public function actualizarComedorTO($to) {
        // Listo, procedemos a update
        $mysqli = $this->getMysqli();
        $apertura = $to->getApertura();
        $horario = $to->getHorarioComedor();
        $consulta = "UPDATE Comedores SET nombre=?,direccion=?,diaInicioApertura=?,diaFinApertura=?,hAperturaIni=?,
          hAperturaFin=?,nombreContacto=?,telefono=?,horaInicio=?,horaFin=?,promocion=? WHERE _id=? LIMIT 1";
        if ($stmt = $mysqli->prepare($consulta)) {
            $stmt->bind_param('sssssssssssi',
                $to->getNombre(), $to->getDireccion(),
                $apertura['dias'][0], $apertura['dias'][1],
                $apertura['horas'][0], $apertura['horas'][1],
                $to->getNombreContacto(), $to->getTlfn(),
                $horario[0], $horario[1], $to->getPromocion(),
                $to->getId());
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('Error al ejecutar actualizacion: ' . $mysqli->error);
            }
            $stmt->close();
            return;
        } else
            throw new Exception('ComedoresDAO->actualizarComedorTO:prepare: ' . $mysqli->error);
    }
}

/**
 * Class MysqliTiposMenuDAO, implementación del acceso a la base en busca de TiposMenu
 * mediante mysqli
 */
class MysqliTiposMenuDAO extends MysqliDAO implements ITiposMenuDAO
{

    /**
     * @param $id int Id del menu que se desea obtener
     * @return TipoMenuTO|null Transfer object para el tipo de menu de id dado o null en caso de error
     * @throws Exception si hay algún fallo obteniéndolo
     */
    public function obtenerTipoMenuTO($id) {
        $mysqli = $this->getMysqli();
        if ($stmt = $mysqli->prepare("SELECT _id,nombre,precio,id_comedor FROM TiposMenu WHERE _id = ?")) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($id, $nombre, $precio, $id_comedor);
            $menu = null;
            while ($stmt->fetch()) {
                $menu = new TipoMenuTO($id, $nombre, $precio, $id_comedor);
            }
            $stmt->close();
            return $menu;
        } else
            throw new Exception('prepare: ' . $mysqli->error);
    }

    /**
     * Obtiene los menús del comedor indicado de la base
     * @param $id_comedor int Id del comedor del que se desean obtener los menús
     * @return array(TipoMenuTO) Array de menús encontrados
     * @throws Exception Si no se puede realizar la consulta
     */
    public function obtenerMenus($id_comedor) {
        $mysqli = $this->getMysqli();
        if ($stmt = $mysqli->prepare("SELECT _id,nombre,precio FROM TiposMenu WHERE id_comedor = ?")) {
            $stmt->bind_param("i", $id_comedor);
            $stmt->execute();
            $stmt->bind_result($id, $nombre, $precio);
            $menus = array();
            while ($stmt->fetch()) {
                $menus[] = new TipoMenuTO($id, $nombre, $precio, $id_comedor);
            }
            $stmt->close();
            return $menus;
        } else
            throw new Exception('prepare: ' . $mysqli->error);
    }

    /**
     * Obtiene el número de menús del comedor indicado
     * @param $id_comedor int Id del comedor del que se desean obtener el número de menús
     * @return int número de menús del comedor
     * @throws Exception Si no se puede realizar la consulta
     */
    public function countMenus($id_comedor) {
        $mysqli = $this->getMysqli();
        if ($stmt = $mysqli->prepare("SELECT COUNT(_id) FROM TiposMenu WHERE id_comedor = ?")) {
            $stmt->bind_param("i", $id_comedor);
            $stmt->execute();
            $stmt->store_result();
            if (!$stmt->num_rows == 1) {
                $stmt->close();
                throw new Exception('countMenus: Increíblemente, la consulta no devolvió ninguna fila.');
            }
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            return $count;
        } else
            throw new Exception('prepare: ' . $mysqli->error);
    }

    /**
     * Crea un nuevo tipo de menu en la base y devuelve su TO
     * @param int $id_comedor Id del comedor al que se encuentra asociado el menú
     * @param string $nombre Nombre del tipo de menú
     * @param float $precio Precio del tipo de menú
     * @throws Exception si no se puede crear el nuevo menú
     * @return TipoMenuTO El menú recién creado
     */
    public function nuevoTipoMenu($id_comedor, $nombre, $precio) {
        $mysqli = $this->getMysqli();
        if ($stmt = $mysqli->prepare("INSERT INTO TiposMenu(`nombre`,`precio`,`id_comedor`) VALUES(?,?,?)")) {
            $stmt->bind_param("sdi", $nombre, $precio, $id_comedor);
            if (!($stmt->execute() && $stmt->affected_rows == 1)) {
                $stmt->close();
                throw new Exception('No se ha podido insertar el nuevo tipo de menú');
            }
            $id = $stmt->insert_id;
            $stmt->close();
            return $this->obtenerTipoMenuTO($id);
        } else
            throw new Exception('prepare: ' . $mysqli->error);
    }

    /**
     * Elimina de la base el TipoMenu identificado por el id dado
     * @param $id int id del tipo de menu a eliminar
     * @throws Exception en caso de error
     */
    public function eliminarTipoMenu($id) {
        $mysqli = $this->getMysqli();
        if ($stmt = $mysqli->prepare("DELETE FROM TiposMenu WHERE _id=?")) {
            $stmt->bind_param("i", $id);
            if (!($stmt->execute() && $stmt->affected_rows == 1)) {
                $stmt->close();
                throw new Exception('No se ha podido eliminar el tipo de menú. ' . $mysqli->error);
            }
            $stmt->close();
        } else
            throw new Exception('prepare: ' . $mysqli->error);
    }
}