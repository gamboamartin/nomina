<?php

namespace models;

use PDO;


class nom_otro_pago extends nominas_confs
{

    public function __construct(PDO $link)
    {
        $tabla = __CLASS__;
        $columnas = array($tabla => false,'cat_sat_tipo_otro_pago_nom'=>$tabla);
        $campos_obligatorios = array();

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }



}