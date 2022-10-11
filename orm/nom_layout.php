<?php
namespace models;
use base\orm\modelo;
use PDO;

class nom_layout extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();

        $campos_view = array();
        $campos_view['bn_sucursal_id']['type'] = 'selects';
        $campos_view['bn_sucursal_id']['model'] = (new bn_sucursal($link));

        $campos_view['nom_periodo_id']['type'] = 'selects';
        $campos_view['nom_periodo_id']['model'] = (new nom_periodo($link));

        $campos_view['fecha_pago']['type'] = 'inputs';

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}