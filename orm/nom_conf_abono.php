<?php
namespace models;
use base\orm\modelo;

use gamboamartin\empleado\models\em_tipo_abono_anticipo;
use gamboamartin\errores\errores;

use PDO;
use stdClass;

class nom_conf_abono extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_abono';
        $columnas = array($tabla=>false, 'em_tipo_abono_anticipo'=>$tabla, 'nom_deduccion'=>$tabla);
        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis',
            'em_tipo_abono_anticipo_id','nom_deduccion_id');
        $campos_view = array(
            'em_tipo_abono_anticipo_id' => array('type' => 'selects', 'model' => new em_tipo_abono_anticipo($link)),
            'nom_deduccion_id' => array('type' => 'selects', 'model' => new nom_deduccion($link)),
            'id' => array('type' => 'inputs'),'codigo' => array('type' => 'inputs'));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;

    }

    public function alta_bd(): array|stdClass
    {

        if (!isset($this->registro['descripcion_select'])) {
            $this->registro['descripcion_select'] = $this->registro['descripcion'];
        }

        if (!isset($this->registro['codigo_bis'])) {
            $this->registro['codigo_bis'] = $this->registro['codigo'];
        }

        if (!isset($this->registro['alias'])) {
            $this->registro['alias'] = $this->registro['codigo'];
            $this->registro['alias'] .= $this->registro['descripcion'];
        }

        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta conf abono',data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

}