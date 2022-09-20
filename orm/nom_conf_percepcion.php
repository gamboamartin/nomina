<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_conf_percepcion extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false , "nom_percepcion" => $tabla);
        $campos_obligatorios = array();
        $campos_view = array("nom_conf_nomina_id" => array("type" => "selects", "model" => new nom_conf_nomina(link: $link)),
            "nom_percepcion_id" => array("type" => "selects", "model" => new nom_percepcion(link: $link)));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view: $campos_view);
    }
}