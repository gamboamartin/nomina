<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_conf_nomina extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_conf_factura' => $tabla);
        $campos_obligatorios = array('cat_sat_periodicidad_pago_nom_id','cat_sat_tipo_nomina_id',
            'nom_conf_factura_id','descripcion_select');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}