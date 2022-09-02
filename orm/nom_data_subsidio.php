<?php

namespace models;

use gamboamartin\errores\errores;
use PDO;
use stdClass;


class nom_data_subsidio extends nominas_confs
{

    public function __construct(PDO $link)
    {
        $tabla = __CLASS__;
        $columnas = array($tabla => false);
        $campos_obligatorios = array('descripcion','codigo','alias','codigo_bis','nom_par_deduccion_id',
            'nom_par_otro_pago_id','monto_isr_bruto','monto_subsidio_bruto');

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function alta_bd(): array|stdClass
    {

        $keys = array('nom_nomina_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
        }

        $keys_registro = array();
        $keys_row = array();

        $modelo = new nom_nomina($this->link);

        $registro = $this->asigna_codigo(keys_registro: $keys_registro, keys_row: $keys_row, modelo: $modelo,
            registro:  $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }


        $this->registro = $registro;

        $registro = $this->asigna_descripcion(modelo: $modelo, registro: $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }

        $this->registro = $registro;

        $registro = $this->asigna_alias(registro: $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }

        $this->registro = $registro;

        $registro = $this->asigna_codigo_bis(registro: $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }

        $this->registro = $registro;

        $r_alta_bd = parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta data subsidio',data:  $r_alta_bd);
        }
        return $r_alta_bd;
    }


}