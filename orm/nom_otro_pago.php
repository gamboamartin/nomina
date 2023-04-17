<?php

namespace gamboamartin\nomina\models;

use gamboamartin\errores\errores;
use PDO;


class nom_otro_pago extends nominas_confs
{

    public function __construct(PDO $link)
    {
        $tabla = 'nom_otro_pago';
        $columnas = array($tabla => false,'cat_sat_tipo_otro_pago_nom'=>$tabla);
        $campos_obligatorios = array();

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    /**
     *
     * @return array|int
     */
    public function nom_otro_pago_subsidio_id(): array|int
    {
        $filtro['nom_otro_pago.es_subsidio'] = 'activo';
        $r_nom_otro_pago = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener pago subsidio',data:  $r_nom_otro_pago);
        }
        if($r_nom_otro_pago->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe subsidio configurado',data:  $r_nom_otro_pago);
        }
        if($r_nom_otro_pago->n_registros > 1){
            return $this->error->error(mensaje: 'Error existe subsidio mas de un subsidio',data:  $r_nom_otro_pago);
        }
        return (int)$r_nom_otro_pago->registros[0]['nom_otro_pago_id'];
    }



}