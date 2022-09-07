<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_rel_empleado_sucursal extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'em_empleado'=>$tabla,'com_sucursal'=>$tabla);
        $campos_obligatorios = array('em_empleado_id','com_sucursal_id');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}