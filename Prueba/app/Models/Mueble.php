<?php

namespace App\Models;

use JsonSerializable;

class Mueble implements JsonSerializable
{
    private $id;

    private $nombre;
    private $categoria_id = [];
    private $descripcion;
    private $precio;
    private $stock;
    private $materiales;
    private $dimensiones;
    private $color_principal;
    private $destacado;
    private $imagenes = [];

    public function __construct(
        $id,
        $nombre,
        $categoria_id = [],
        $descripcion = null,
        $precio = 0,
        $stock = 0,
        $materiales = null,
        $dimensiones = null,
        $color_principal = null,
        $destacado = false,
        $imagenes = []
    )
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->categoria_id = $categoria_id;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->materiales = $materiales;
        $this->dimensiones = $dimensiones;
        $this->color_principal = $color_principal;
        $this->destacado = $destacado;
        $this->imagenes = $imagenes;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nombre'=> $this->nombre,
            'categoria_id' => $this->categoria_id,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'materiales' => $this->materiales,
            'dimensiones'=> $this->dimensiones,
            'color_principal'=> $this->color_principal,
            'destacado'=> $this->destacado,
            'imagenes'=> $this->imagenes
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getNombre()
    {
        return $this->nombre;
    }
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function getCategoria()
    {
        return $this->categoria_id;
    }

    /**
     * Set the value of categoria_id
     *
     * @return  self
     */
    public function setCategoria($categoria_id)
    {
        $this->categoria_id = $categoria_id;

        return $this;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set the value of descripcion
     *
     * @return  self
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set the value of stock
     *
     * @return  self
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    public function getMateriales()
    {
        return $this->materiales;
    }

    /**
     * Set the value of materiales
     *
     * @return  self
     */
    public function setMateriales($materiales)
    {
        $this->materiales = $materiales;

        return $this;
    }

    public function getDimensiones()
    {
        return $this->dimensiones;
    }

    /**
     * Set the value of dimensiones
     *
     * @return  self
     */
    public function setDimensiones($dimensiones)
    {
        $this->dimensiones = $dimensiones;

        return $this;
    }

    public function getColorPrincipal()
    {
        return $this->color_principal;
    }

    /**
     * Set the value of color
     *
     * @return  self
     */
    public function setColorPrincipal($color_principal)
    {
        $this->color_principal = $color_principal;

        return $this;
    }

    public function getDestacado()
    {
        return $this->destacado;
    }

    /**
     * Set the value of destacado
     *
     * @return  self
     */
    public function setDestacado($destacado)
    {
        $this->destacado = $destacado;

        return $this;
    }

    public function getImagenes()
    {
        return $this->imagenes;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setImagenes($imagenes)
    {
        $this->imagenes = $imagenes;

        return $this;
    }
}
