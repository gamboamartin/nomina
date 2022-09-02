<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class nom_periodo extends nominas_confs {

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, );
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function get_empleados(int $im_registro_patronal_id){
        $filtro['im_registro_patronal.id'] = $im_registro_patronal_id;

        $r_empleados = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros', data: $r_empleados);
        }

        return $r_empleados->registros;
    }

}