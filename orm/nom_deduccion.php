<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;

class nom_deduccion extends nominas_confs {

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function nom_deduccion_imss_id(): array|int
    {
        $filtro['nom_deduccion.es_imss'] = 'activo';
        $r_nom_deduccion = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al buscar deduccion imss', data: $r_nom_deduccion);
        }
        if($r_nom_deduccion->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe el tipo de deduccion', data: $r_nom_deduccion);
        }
        if($r_nom_deduccion->n_registros > 1 ){
            return $this->error->error(mensaje: 'Error existe mas de una deduccion de este tipo',
                data: $r_nom_deduccion);
        }
        return (int)$r_nom_deduccion->registros[0]['nom_deduccion_id'];
    }


}