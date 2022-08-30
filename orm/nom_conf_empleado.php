<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_conf_empleado extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'em_empleado' => $tabla,'nom_conf_nomina' => $tabla,'nom_conf_factura' => 'nom_conf_nomina');
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}