<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_rel_deduccion_abono extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'nom_par_deduccion'=>$tabla,'em_abono_anticipo'=>$tabla);
        $campos_obligatorios = array('nom_par_deduccion_id','em_abono_anticipo_id');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}