<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_par_percepcion extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_nomina'=>$tabla, 'nom_percepcion'=>$tabla,
            'cat_sat_tipo_percepcion_nom'=>'nom_percepcion');
        $campos_obligatorios = array('nom_nomina_id');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    
}