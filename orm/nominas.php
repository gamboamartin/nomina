<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use JsonException;
use stdClass;

class nominas extends modelo {

    /**
     * @throws JsonException
     */
    protected function aplica_deduccion(float $monto, int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
    {
        $data_existe = $this->data_deduccion(monto: $monto, nom_deduccion_id: $nom_deduccion_id,
            nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }


        $transaccion = $this->transaccion_deduccion(data_existe: $data_existe,nom_par_deduccion_ins: $data_existe->row_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
        }

        return $transaccion;
    }

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

    private function asigna_importes(array $registro): array
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

    protected function data_deduccion(float $monto, int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
    {
        $data_existe = $this->existe_data_deduccion(nom_deduccion_id:$nom_deduccion_id, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }
        $nom_par_deduccion_ins = $this->nom_par_deduccion_aut(monto: $monto, nom_deduccion_id: $nom_deduccion_id,
            nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar deduccion', data: $nom_par_deduccion_ins);
        }

        $data_existe->row_ins = $nom_par_deduccion_ins;
        return $data_existe;
    }

    /**
     * @param int $nom_deduccion_id
     * @param int $nom_nomina_id
     * @return array|stdClass
     */
    private function existe_data_deduccion(int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
    {
        $filtro = $this->filtro_partida(id: $nom_deduccion_id, nom_nomina_id: $nom_nomina_id, tabla: 'nom_deduccion');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
        }

        $existe = (new nom_par_deduccion($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $existe);
        }
        $data = new stdClass();
        $data->filtro = $filtro;
        $data->existe = $existe;
        return $data;
    }

    /**
     *
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return array|stdClass
     */
    private function existe_data_deduccion_imss(int $nom_nomina_id): array|stdClass
    {


        $data = $this->existe_data_deduccion(nom_deduccion_id:2, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data);
        }


        return $data;
    }

    /**
     *
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return array|stdClass
     */
    protected function existe_data_deduccion_isr(int $nom_nomina_id): array|stdClass
    {


        $data = $this->existe_data_deduccion(nom_deduccion_id:1, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data);
        }

        return $data;
    }

    /**
     * Genera un filtro para una partida de nomina
     * @param int $id Id de la deduccion base
     * @param int $nom_nomina_id Id de la nomina
     * @param string $tabla Tabla de deduccion
     * @return array
     * @version 0.106.11
     */
    PUBLIC function filtro_partida(int $id, int $nom_nomina_id, string $tabla): array
    {
        if($id<=0){
            return $this->error->error(mensaje: 'Error id debe ser mayor a 0', data: $id);
        }
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error $tabla esta vacia', data: $tabla);
        }

        $filtro = array();
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro[$tabla.'.id'] = $id;

        return $filtro;
    }

    /**
     * @param int $registro_id
     * @return array
     */
    protected function imss(int $registro_id): array
    {
        $nom_partida = $this->registro(registro_id:$registro_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_partida);
        }


        return (new calcula_imss())->imss(
            cat_sat_periodicidad_pago_nom_id: $nom_partida->cat_sat_periodicidad_pago_nom_id,
            fecha:$nom_partida->nom_nomina_fecha_final_pago, n_dias: $nom_partida->nom_nomina_num_dias_pagados,
            sbc: $nom_partida->em_empleado_salario_diario_integrado, sd: $nom_partida->em_empleado_salario_diario);
    }

    /**
     * @throws JsonException
     */
    protected function modifica_deduccion(array $filtro, array $nom_par_deduccion_upd): array|\stdClass
    {

        $nom_par_deduccion_modelo = new nom_par_deduccion($this->link);

        $nom_par_deduccion = $nom_par_deduccion_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion', data: $nom_par_deduccion);
        }

        $r_modifica_nom_par_deduccion = $nom_par_deduccion_modelo->modifica_bd(
            registro:$nom_par_deduccion_upd, id: $nom_par_deduccion->registros[0]['nom_par_deduccion_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar deduccion', data: $r_modifica_nom_par_deduccion);
        }

        return $r_modifica_nom_par_deduccion;
    }

    private function nom_par_deduccion_aut(float $monto, int $nom_deduccion_id, int $nom_nomina_id): array
    {
        $nom_par_deduccion_ins = array();
        $nom_par_deduccion_ins['nom_nomina_id'] =$nom_nomina_id;
        $nom_par_deduccion_ins['nom_deduccion_id'] = $nom_deduccion_id;
        $nom_par_deduccion_ins['importe_gravado'] = $monto;
        $nom_par_deduccion_ins['importe_exento'] = 0.0;
        return $nom_par_deduccion_ins;
    }

    /**
     * @throws JsonException
     */
    private function transaccion_deduccion(stdClass $data_existe, array $nom_par_deduccion_ins): array|stdClass
    {
        $result = new stdClass();
        if($data_existe->existe){
            $r_modifica_nom_par_deduccion = $this->modifica_deduccion(
                filtro: $data_existe->filtro,nom_par_deduccion_upd:  $nom_par_deduccion_ins);
            if(errores::$error){
                return $this->error->error(
                    mensaje: 'Error al modificar deduccion', data: $r_modifica_nom_par_deduccion);
            }
            $result->data = $r_modifica_nom_par_deduccion;
            $result->transaccion = 'modifica';
        }
        else{
            $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(
                registro: $nom_par_deduccion_ins);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al registrar deduccion', data: $r_alta_nom_par_deduccion);
            }
            $result->data = $r_alta_nom_par_deduccion;
            $result->transaccion = 'alta';

        }
        return $result;


    }


}
