<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_detalle_layout extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();

        $campos_view = array();
        $campos_view['nom_nomina_id']['type'] = 'selects';
        $campos_view['nom_nomina_id']['model'] = (new nom_nomina($link));

        $campos_view['nom_layout_id']['type'] = 'selects';
        $campos_view['nom_layout_id']['model'] = (new nom_layout($link));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}