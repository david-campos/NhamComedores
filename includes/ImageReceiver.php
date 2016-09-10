<?php

/**
 * Clase ImageMachine, que maneja las imágenes que se suben a la web
 * @author David Campos R.
 */
class ImageReceiver
{
    const IMAGENES = 0;
    const MINIATURAS = 1;

    /**
     * ImageReceiver constructor.
     * @param $_imgName
     */
    public function __construct($_imgName) {
        if (isset($_FILES[$_imgName])) {
            $this->_img = $_FILES[$_imgName];
            $this->_imgName = basename($this->_img['name']);
            if ($this->_img['size'] > 11) {
                $this->_imgSize = getimagesize($this->_img['tmp_name']);
                $this->_imgType = $this->_imgSize['mime'];
            } else
                $this->_img = null;
        } else
            $this->_img = null;
    }

    /**
     * @return bool Indica si la imagen está disponible
     */
    public function imagenDisponible() {
        return $this->_img !== null;
    }

    /**
     * @return string el nombre de la imagen
     * @throws Exception si no está disponible la imagen
     */
    public function nombreImagen() {
        if ($this->imagenDisponible())
            return $this->_imgName;
        else
            throw new Exception('Imagen no disponible');
    }

    /**
     * @return mixed la extensión de la imagen
     * @throws Exception si no está disponible la imagen
     */
    public function extension() {
        if ($this->imagenDisponible())
            return pathinfo($this->_imgName, PATHINFO_EXTENSION);
        else
            throw new Exception('Imagen no disponible');
    }


    /**
     * @return array el valor de getimagesize para la imagen
     * @throws Exception si no está disponible la imagen
     */
    public function imageSize() {
        if ($this->imagenDisponible())
            return $this->_imgSize;
        else
            throw new Exception('Imagen no disponible');
    }

    /**
     * @return int el tipo de imagen
     * @throws Exception si no está disponible la imagen
     */
    public function tipoImagen() {
        if ($this->imagenDisponible())
            return $this->_imgType;
        else
            throw new Exception('Imagen no disponible');
    }

    /**
     * @param $ubicacion int ImagenReceiver::IMAGENES o ImagenReceiver::MINIATURAS
     * @param $id int id del comedor asociado a la imagen
     * @throws Exception si no se puede guardar el archivo
     */
    public function guardarEn($ubicacion, $id) {
        switch ($ubicacion) {
            case ImageReceiver::IMAGENES:
                $target_file = sprintf(dirname(__FILE__) . "/../public_html/api/imagenes/detail_%d.png", $id);
                break;
            case ImageReceiver::MINIATURAS:
                $target_file = sprintf(dirname(__FILE__) . "/../public_html/api/miniaturas/mini_%d.png", $id);
                break;
            default:
                return;
        }

        if (!move_uploaded_file($this->_img["tmp_name"], $target_file))
            throw new Exception('No se pudo guardar el archivo.');
    }

    private $_img;
    private $_imgName;
    private $_imgSize;
    private $_imgType;
}