<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use PDO;

class nom_conf_deduccion extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_deduccion';
        $columnas = array($tabla=>false, "nom_deduccion" => $tabla);
        $campos_obligatorios = array();
        $campos_view = array("nom_conf_nomina_id" => array("type" => "selects", "model" => new nom_conf_nomina(link: $link)),
            "nom_deduccion_id" => array("type" => "selects", "model" => new nom_deduccion(link: $link)));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view: $campos_view);
    }
}