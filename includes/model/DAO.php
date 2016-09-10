<?php

require_once dirname(__FILE__) . "/ComedorTO.php"; // ComedorTO

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
