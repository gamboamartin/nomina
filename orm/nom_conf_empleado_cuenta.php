<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class nom_conf_empleado_cuenta extends nominas_confs {

    public function __construct(PDO $link){
        $tabla = 'nom_conf_empleado_cuenta';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}