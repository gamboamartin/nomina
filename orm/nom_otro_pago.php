<?php

namespace models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use html\im_registro_patronal_html;
use PDO;
use stdClass;

class nom_otro_pago extends modelo
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