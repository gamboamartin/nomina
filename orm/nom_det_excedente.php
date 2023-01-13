<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use PDO;

class nom_det_excedente extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_det_excedente';
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();
        $campos_view = array();
        $campos_view['nom_per_excedente_id']['type'] = 'selects';
        $campos_view['nom_per_excedente_id']['model'] = (new nom_per_excedente($link));

        $campos_view['nom_percepcion_id']['type'] = 'selects';
        $campos_view['nom_percepcion_id']['model'] = (new nom_percepcion($link));

        $campos_view['porcentaje']['type'] = 'inputs';

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}