<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use PDO;

class nom_nomina_etapa extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_nomina_etapa';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }
}