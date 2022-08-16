<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_nomina extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'dp_calle_pertenece'=>$tabla, 'dp_calle' => 'dp_calle_pertenece',
            'dp_colonia_postal'=>'dp_calle_pertenece', 'dp_colonia'=>'dp_colonia_postal', 'dp_cp'=>'dp_colonia_postal',
            'dp_municipio'=>'dp_cp', 'dp_estado'=>'dp_municipio','dp_pais'=>'dp_estado',
            'em_empleado'=>$tabla);
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}