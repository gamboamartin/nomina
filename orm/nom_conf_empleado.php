<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class nom_conf_empleado extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'em_empleado' => $tabla,'nom_conf_nomina' => $tabla,'nom_conf_factura' => 'nom_conf_nomina');
        $campos_obligatorios = array('em_empleado_id','nom_conf_nomina_id','descripcion_select');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function nom_conf_empleado(int $em_empleado_id, int $nom_conf_nomina_id){
        $filtro['em_empleado.id'] = $em_empleado_id;
        $filtro['nom_conf_nomina.id'] = $nom_conf_nomina_id;
        $r_nom_conf_empleado = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nom_conf_empleado',data:  $r_nom_conf_empleado);
        }
        if($r_nom_conf_empleado->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe nom_conf_empleado',data:  $r_nom_conf_empleado);
        }
        if($r_nom_conf_empleado->n_registros > 1){
            return $this->error->error(mensaje: 'Error existe mas de un nom_conf_empleado',data:  $r_nom_conf_empleado);
        }

        return $r_nom_conf_empleado->registros[0];

    }
}