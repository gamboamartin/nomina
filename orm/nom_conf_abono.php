<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;

use gamboamartin\administrador\models\adm_campo;
use gamboamartin\empleado\models\em_tipo_abono_anticipo;
use gamboamartin\empleado\models\em_tipo_anticipo;
use gamboamartin\errores\errores;

use PDO;
use stdClass;

class nom_conf_abono extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_abono';

        $columnas = array($tabla=>false, 'em_tipo_abono_anticipo'=>$tabla, 'em_tipo_anticipo'=>$tabla,
            'nom_deduccion'=>$tabla, 'adm_campo'=>$tabla);

        $campos_obligatorios = array('descripcion','codigo','descripcion_select','alias','codigo_bis',
            'em_tipo_abono_anticipo_id','nom_deduccion_id');

        $campos_view['em_tipo_abono_anticipo_id'] = array('type' => 'selects', 'model' => new em_tipo_abono_anticipo($link));
        $campos_view['nom_deduccion_id'] = array('type' => 'selects', 'model' => new nom_deduccion($link));
        $campos_view['em_tipo_anticipo_id'] = array('type' => 'selects', 'model' => new em_tipo_anticipo($link));
        $campos_view['adm_campo_id'] = array('type' => 'selects', 'model' => new adm_campo($link));
        $campos_view['codigo'] = array('type' => 'inputs');

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