<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;

class nominas extends modelo {

    private function asigna_codigo_partida(array $registro): array
    {
        $keys_registro = array('nom_nomina_id');
        $keys_row = array('cat_sat_periodicidad_pago_nom_id','em_empleado_rfc','im_registro_patronal_id');
        $modelo = new nom_nomina($this->link);
        $registro = $this->asigna_codigo(keys_registro: $keys_registro,keys_row:  $keys_row,
            modelo:  $modelo,registro:  $registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }
        return $registro;
    }

    private function asigna_importe_exento(array $registro): array
    {
        if(!isset($registro['importe_exento'])){

            $registro['importe_exento'] = 0;
        }
        return $registro;
    }

    private function asigna_importe_gravado(array $registro): array
    {
        if(!isset($registro['importe_gravado'])){

            $registro['importe_gravado'] = 0;
        }
        return $registro;
    }

    protected function asigna_importes(array $registro): array
    {
        $registro = $this->asigna_importe_gravado(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        $registro = $this->asigna_importe_exento(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }
        return $registro;
    }

    protected function asigna_registro_alta(modelo $modelo, array $registro): array
    {


        $registro = $this->base_alta_campos(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }
        $registro = $this->asigna_importes(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar importes', data: $registro);
        }


        return $registro;
    }

    private function base_alta_campos(modelo $modelo, array $registro): array
    {
        $registro = $this->asigna_codigo_partida(registro: $registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }

        $registro = $this->campos_base(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }
        return $registro;
    }

    private function campos_base(modelo $modelo, array $registro): array
    {
        $registro = $this->asigna_descripcion(modelo: $modelo, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }

        $registro = $this->asigna_descripcion_select(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        $registro = $this->asigna_alias(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        $registro = $this->asigna_codigo_bis(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        return $registro;
    }


}
