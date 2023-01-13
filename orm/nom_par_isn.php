<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use gamboamartin\cat_sat\models\cat_sat_isn;
use PDO;

class nom_par_isn extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_par_isn';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();

        $campos_view = array();
        $campos_view['nom_nomina_id']['type'] = 'selects';
        $campos_view['nom_nomina_id']['model'] = (new nom_nomina($link));

        $campos_view['cat_sat_isn_id']['type'] = 'selects';
        $campos_view['cat_sat_isn_id']['model'] = (new cat_sat_isn($link));

        $campos_view['monto']['type'] = 'inputs';

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}