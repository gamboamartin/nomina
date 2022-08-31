<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use JsonException;
use stdClass;

class nominas extends modelo {

    protected string $tabla_nom_conf = '';


    /**
     * @throws JsonException
     */
    public function alta_bd_percepcion(modelo $modelo): array|stdClass
    {

        $valida = $this->valida_registro_modelo(modelo: $modelo,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $registro = $this->asigna_registro_alta(modelo: $modelo, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar datos', data: $registro);
        }
        $this->registro = $registro;


        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar otro pago', data: $r_alta_bd);
        }

        $transacciones_deduccion = $this->integra_deducciones(nom_nomina_id: $this->registro['nom_nomina_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones', data: $transacciones_deduccion);
        }
        return $r_alta_bd;
    }

    /**
     * @throws JsonException
     */
    private function aplica_deduccion(float $monto, int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
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

    public function aplica_imss(int $registro_id): bool|array
    {

        $row = $this->registro(registro_id: $registro_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro' , data: $row);
        }

        $aplica_imss = false;
        $key_aplica = $this->tabla_nom_conf.'_aplica_imss';
        if(isset($row->$key_aplica) && $row->$key_aplica === 'activo'){
            $aplica_imss = true;
        }

        return $aplica_imss;

    }

    /**
     * @throws JsonException
     */
    private function aplica_imss_valor(int $nom_nomina_id, int $partida_percepcion_id): array|stdClass
    {
        $transaccion_aplicada = false;
        $transaccion = new stdClass();
        $imss = $this->imss(partida_percepcion_id: $partida_percepcion_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular imss', data: $imss);
        }
        if ((float)$imss['total'] > 0.0) {
            $transaccion = $this->aplica_deduccion(monto: (float)$imss['total'], nom_deduccion_id: 2,
                nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }
            $transaccion_aplicada = true;
        }
        $data = new stdClass();
        $data->imss = $imss;
        $data->transaccion_aplicada = $transaccion_aplicada;
        $data->transaccion = $transaccion;

        return $data;
    }

    private function aplica_imss_valor_por_nomina(int $nom_nomina_id): array|stdClass
    {
        $transaccion_aplicada = false;
        $transaccion = new stdClass();
        $imss = $this->imss_por_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular imss', data: $imss);
        }
        if ((float)$imss['total'] > 0.0) {
            $transaccion = $this->aplica_deduccion(monto: (float)$imss['total'], nom_deduccion_id: 2,
                nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }
            $transaccion_aplicada = true;
        }
        $data = new stdClass();
        $data->imss = $imss;
        $data->transaccion_aplicada = $transaccion_aplicada;
        $data->transaccion = $transaccion;

        return $data;
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
        $valida = $this->valida_registro_modelo(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

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
        $valida = $this->valida_registro_modelo(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

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

    /**
     * @param int $partida_percepcion_id otro pago o percepcion id
     * @return float|array
     */
    private function calcula_isr_nomina(int $partida_percepcion_id): float|array
    {
        $nom_partida = $this->registro(registro_id:$partida_percepcion_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_partida);
        }

        $isr = 0.0;
        $total_gravado = (new nom_nomina($this->link))->total_gravado(nom_nomina_id: $nom_partida->nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular total gravado', data: $total_gravado);
        }

        if($total_gravado >0.0) {
            $isr = $this->isr_total_nomina_por_percepcion(
                partida_percepcion_id: $partida_percepcion_id, total_gravado: $total_gravado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
            }
        }
        return $isr;
    }

    /**
     * @param int $nom_nomina_id
     * @return float|array
     */
    private function calcula_isr_por_nomina(int $nom_nomina_id): float|array
    {

        $isr = 0.0;
        $total_gravado = (new nom_nomina($this->link))->total_gravado(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular total gravado', data: $total_gravado);
        }

        if($total_gravado >0.0) {
            $isr = $this->isr_nomina(nom_nomina_id: $nom_nomina_id, total_gravado: $total_gravado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
            }
        }
        return $isr;
    }

    private function campos_base(modelo $modelo, array $registro): array
    {
        $valida = $this->valida_registro_modelo(modelo: $modelo,registro:  $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }
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

    private function data_deduccion(float $monto, int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
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
     * @throws JsonException
     */
    private function ejecuta_transaccion_imss(bool $aplica_imss, int $nom_nomina_id, int $partida_percepcion_id): array|stdClass
    {
        $data = new stdClass();
        $data->transaccion_aplicada = false;
        $data->transaccion = new stdClass();
        $data->dels = array();
        $data->imss = array();
        if($aplica_imss) {
            $aplicacion_imss = $this->aplica_imss_valor(nom_nomina_id: $nom_nomina_id,
                partida_percepcion_id: $partida_percepcion_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
            }
            $data->transaccion_aplicada = $aplicacion_imss->transaccion_aplicada;
            $data->transaccion = $aplicacion_imss->transaccion;
            $data->imss = $aplicacion_imss->imss;

        }
        else{
            $elimina_deducciones = $this->elimina_imss(nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }
            $data->dels = $elimina_deducciones;
        }
        return $data;
    }

    private function ejecuta_transaccion_imss_por_nomina(bool $aplica_imss, int $nom_nomina_id): array|stdClass
    {
        $data = new stdClass();
        $data->transaccion_aplicada = false;
        $data->transaccion = new stdClass();
        $data->dels = array();
        $data->imss = array();
        if($aplica_imss) {
            $aplicacion_imss = $this->aplica_imss_valor_por_nomina(nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
            }
            $data->transaccion_aplicada = $aplicacion_imss->transaccion_aplicada;
            $data->transaccion = $aplicacion_imss->transaccion;
            $data->imss = $aplicacion_imss->imss;

        }
        else{
            $elimina_deducciones = $this->elimina_imss(nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }
            $data->dels = $elimina_deducciones;
        }
        return $data;
    }

    public function elimina_bd(int $id): array
    {
        $nom_percepcion = $this->registro(registro_id:$id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $nom_percepcion);
        }

        $r_elimina_bd = parent::elimina_bd(id: $id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar registro', data: $r_elimina_bd);
        }

        $transacciones_deduccion = $this->transacciona_isr_por_nomina(nom_nomina_id: $nom_percepcion->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones', data: $transacciones_deduccion);
        }


        return $r_elimina_bd;
    }

    /**
     * @throws JsonException
     */
    private function elimina_deduccion(array $filtro): array
    {
        $r_nom_par_deduccion = (new nom_par_deduccion(link: $this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion', data: $r_nom_par_deduccion);
        }
        $dels = array();
        foreach ($r_nom_par_deduccion->registros as $par_deduccion) {
            $elimina_deduccion = (new nom_par_deduccion(link: $this->link))->elimina_bd(
                id: $par_deduccion['nom_par_deduccion_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar deduccion', data: $elimina_deduccion);
            }
            $dels[] = $elimina_deduccion;
        }
        return $dels;
    }

    /**
     * @throws JsonException
     */
    private function elimina_imss(int $nom_nomina_id): array
    {
        $elimina_deducciones = array();
        $data_existe = $this->existe_data_deduccion(nom_deduccion_id:2, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }
        if($data_existe->existe){
            $elimina_deducciones = $this->elimina_deduccion(filtro: $data_existe->filtro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }
        }
        return $elimina_deducciones;
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
    private function existe_data_deduccion_isr(int $nom_nomina_id): array|stdClass
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
    private function filtro_partida(int $id, int $nom_nomina_id, string $tabla): array
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
     * Calcula los datos de imss de una nomina
     * @param int $partida_percepcion_id Registro de deduccion, percepcion u otro pago
     * @return array
     */
    private function imss(int $partida_percepcion_id): array
    {
        $nom_partida = $this->registro(registro_id:$partida_percepcion_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_partida);
        }


        return (new calcula_imss())->imss(
            cat_sat_periodicidad_pago_nom_id: $nom_partida->cat_sat_periodicidad_pago_nom_id,
            fecha:$nom_partida->nom_nomina_fecha_final_pago, n_dias: $nom_partida->nom_nomina_num_dias_pagados,
            sbc: $nom_partida->em_empleado_salario_diario_integrado, sd: $nom_partida->em_empleado_salario_diario);
    }

    private function imss_por_nomina(int $nom_nomina_id): array
    {
        $nom_nomina = (new nom_nomina($this->link))->registro(registro_id:$nom_nomina_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }


        return (new calcula_imss())->imss(
            cat_sat_periodicidad_pago_nom_id: $nom_nomina->cat_sat_periodicidad_pago_nom_id,
            fecha:$nom_nomina->nom_nomina_fecha_final_pago, n_dias: $nom_nomina->nom_nomina_num_dias_pagados,
            sbc: $nom_nomina->em_empleado_salario_diario_integrado, sd: $nom_nomina->em_empleado_salario_diario);
    }

    /**
     * @throws JsonException
     */
    protected function integra_deducciones(int $nom_nomina_id): array|stdClass
    {

        $transaccion_isr = $this->transacciona_isr_por_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar isr', data: $transaccion_isr);
        }

        $transaccion_imss = $this->transacciona_imss_por_nomina(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transaccion imss', data: $transaccion_imss);
        }
        $data = new stdClass();
        $data->imss = $transaccion_imss;
        $data->isr = $transaccion_isr;
        return $data;
    }

    /**
     * @param int $nom_nomina_id Registro de nomina en ejecucion
     * @param string|float|int $total_gravado Monto gravable de nomina
     * @return float|array
     */
    private function isr_nomina(int $nom_nomina_id, string|float|int $total_gravado): float|array
    {
        $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }

        $isr = (new nom_nomina($this->link))->isr(
            cat_sat_periodicidad_pago_nom_id: $nom_nomina->cat_sat_periodicidad_pago_nom_id,
            monto: $total_gravado, fecha: $nom_nomina->nom_nomina_fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        return $isr;
    }

    /**
     * @param int $partida_percepcion_id otro pago o percepcion id
     * @param string|float|int $total_gravado Monto gravable de nomina
     * @return float|array
     */
    private function isr_total_nomina_por_percepcion(int $partida_percepcion_id, string|float|int $total_gravado): float|array
    {
        $nom_par_percepcion = $this->registro(registro_id: $partida_percepcion_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nom_par_percepcion', data: $nom_par_percepcion);
        }

        $isr = (new nom_nomina($this->link))->isr(
            cat_sat_periodicidad_pago_nom_id: $nom_par_percepcion->cat_sat_periodicidad_pago_nom_id,
            monto: $total_gravado, fecha: $nom_par_percepcion->nom_nomina_fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        return $isr;
    }

    /**
     * @throws JsonException
     */
    public function modifica_bd_percepcion(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $nom_percepcion = $this->registro(registro_id:$id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $nom_percepcion);
        }

        $r_modifica_bd = parent::modifica_bd(registro: $registro,id:  $id,reactiva:  $reactiva); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al modificar registro', data: $r_modifica_bd);
        }

        $transacciones = $this->transacciones_por_nomina(nom_nomina_id: $nom_percepcion->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones', data: $transacciones);
        }

        return $r_modifica_bd;
    }

    /**
     * @throws JsonException
     */
    private function modifica_deduccion(array $filtro, array $nom_par_deduccion_upd): array|\stdClass
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

    protected function total_percepcion(array $registro): float|array
    {
        $total = $registro['importe_exento']+ $registro['importe_gravado'];
        $total = round($total,2);

        if($total<=0.0){
            return $this->error->error(mensaje: 'Error total es 0', data: $total);
        }
        return $total;
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


    /**
     * Transacciona la deduccion de imss
     * @param int $nom_nomina_id Nomina para transaccionar imss
     * @param int $partida_percepcion_id Registro de deduccion, percepcion u otro pago
     * @return array|stdClass
     * @throws JsonException
     */
    protected function transacciona_imss(int $nom_nomina_id, int $partida_percepcion_id): array|stdClass
    {

        $aplica_imss = (new nom_nomina($this->link))->aplica_imss(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si aplica imss', data: $aplica_imss);
        }

        $aplicacion_imss = $this->ejecuta_transaccion_imss(aplica_imss: $aplica_imss,
            nom_nomina_id:  $nom_nomina_id, partida_percepcion_id: $partida_percepcion_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
        }
        return $aplicacion_imss;

    }

    protected function transacciona_imss_por_nomina(int $nom_nomina_id): array|stdClass
    {

        $aplica_imss = (new nom_nomina($this->link))->aplica_imss(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si aplica imss', data: $aplica_imss);
        }

        $aplicacion_imss = $this->ejecuta_transaccion_imss_por_nomina(aplica_imss: $aplica_imss,
            nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
        }
        return $aplicacion_imss;

    }


    /**
     * @param int $partida_percepcion_id Identificador ya sea otto_pago o percepcion
     * @throws JsonException
     */
    private function transacciona_isr(int $nom_nomina_id, int $partida_percepcion_id): float|array
    {
        $isr = $this->calcula_isr_nomina(partida_percepcion_id: $partida_percepcion_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        if($isr>0.0){

            $transaccion = $this->aplica_deduccion(monto: (float)$isr, nom_deduccion_id: 1,
                nom_nomina_id:  $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }


        }
        return $isr;
    }

    /**
     * @param int $nom_nomina_id
     * @return float|array
     * @throws JsonException
     */
    protected function transacciona_isr_por_nomina(int $nom_nomina_id): float|array
    {
        $isr = $this->calcula_isr_por_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }
        if($isr>0.0){
            $transaccion = $this->aplica_deduccion(monto: (float)$isr, nom_deduccion_id: 1,
                nom_nomina_id:  $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }
        }
        elseif($isr<=0.0){
            $data_existe = $this->existe_data_deduccion(nom_deduccion_id:1, nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
            }
            if($data_existe->existe){
                $elimina_deducciones = $this->elimina_deduccion(filtro: $data_existe->filtro);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
                }
            }

        }
        return $isr;
    }

    /**
     * @throws JsonException
     */
    private function transacciones_por_nomina(int $nom_nomina_id): array|stdClass
    {
        $transacciones_deduccion_isr = $this->transacciona_isr_por_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones isr',
                data: $transacciones_deduccion_isr);
        }
        $transacciones_deduccion_imss = $this->transacciona_imss_por_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones imss',
                data: $transacciones_deduccion_imss);
        }
        $data = new stdClass();
        $data->isr = $transacciones_deduccion_isr;
        $data->imss = $transacciones_deduccion_imss;
        return $data;
    }




}
