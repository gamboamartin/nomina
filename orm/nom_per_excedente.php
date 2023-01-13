<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use PDO;

class nom_per_excedente extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_per_excedente';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();
        $campos_view = array();
        $campos_view['nom_conf_nomina_id']['type'] = 'selects';
        $campos_view['nom_conf_nomina_id']['model'] = (new nom_conf_nomina($link));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}