<?php

require_once dirname(__FILE__) . "/ComedorTO.php"; // ComedorTO
require_once dirname(__FILE__) . "/TipoMenuTO.php"; // TipoMenuTO

require_once dirname(__FILE__) . "/MysqliDAO.php"; // mysqli es el empleado actualmente

/**
 * Obtiene la fábrica DAO utilizada actualmente en la web
 */
function obtenerDAOFactory() {
    return new MysqliDAOFactory();
}

/**
 * Interfaz IDAOFactory, interfaz de la fábrica de DAOS
 */
interface IDAOFactory
{
    /**
     * @return IComedoresDAO Crea el DAO de acceso a los comedores de la base
     */
    public function obtenerComedoresDAO();

    /**
     * @return ITiposMenuDAO Crea el DAO de acceso a los tipos de menú de la base
     */
    public function obtenerTiposMenuDAO();
}

/**
 * Interface IComedoresDAO, interfaz para el acceso a la base en busca de Comedores
 */
interface IComedoresDAO
{
    /**
     * @param $id int Id del comedor a obtener de la base
     * @return ComedorTO|null Transfer object para el comedor de id dado o null en caso de error
     * @throws Exception si hay algún fallo obteniéndolo
     */
    public function obtenerComedorTO($id);

    /**
     * @param $to ComedorTO El TO con el que actualizar el comedor
     */
    public function actualizarComedorTO($to);
}

/**
 * Interface ITiposMenuDAO, interfaz para el acceso a la base por TiposDeMenu
 */
interface ITiposMenuDAO
{
    /**
     * Crea un nuevo tipo de menu en la base y devuelve su TO
     * @param int $id_comedor Id del comedor al que se encuentra asociado el menú
     * @param string $nombre Nombre del tipo de menú
     * @param float $precio Precio del tipo de menú
     * @throws Exception si no se puede crear el nuevo menú
     * @return TipoMenuTO El menú recién creado
     */
    public function nuevoTipoMenu($id_comedor, $nombre, $precio);

    /**
     * @param $id int Id del menu que se desea obtener
     * @return TipoMenuTO|null Transfer object para el tipo de menu de id dado o null en caso de error
     * @throws Exception si hay algún fallo obteniéndolo
     */
    public function obtenerTipoMenuTO($id);

    /**
     * Elimina de la base el TipoMenu identificado por el id dado
     * @param $id int id del tipo de menu a eliminar
     * @throws Exception en caso de error
     */
    public function eliminarTipoMenu($id);

    /**
     * Obtiene los menús del comedor indicado de la base
     * @param $id_comedor int Id del comedor del que se desean obtener los menús
     * @return TipoMenuTO[] Array de menús encontrados
     * @throws Exception Si no se puede realizar la consulta
     */
    public function obtenerMenus($id_comedor);

    /**
     * Obtiene el número de menús del comedor indicado
     * @param $id_comedor int Id del comedor del que se desean obtener el número de menús
     * @return int número de menús del comedor
     * @throws Exception Si no se puede realizar la consulta
     */
    public function countMenus($id_comedor);
}