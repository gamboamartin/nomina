<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use html\fc_partida_html;
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

        $transacciones = (new transaccion_fc())->transacciones_por_nomina(
            mod_nominas: $this, nom_nomina_id: $this->registro['nom_nomina_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones', data: $transacciones);
        }



        return $r_alta_bd;
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
     * Asigna el codigo a una partida
     * @param array $registro Registro en alta
     * @return array
     * @version 0.196.6
     */
    private function asigna_codigo_partida(array $registro): array
    {
        $keys_registro = array('nom_nomina_id');
        $keys_row = array('cat_sat_periodicidad_pago_nom_id','em_empleado_rfc','im_registro_patronal_id');

        $valida = $this->validacion->valida_ids(keys: $keys_registro, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $nom_nomina_modelo = new nom_nomina($this->link);
        $nom_nomina = $nom_nomina_modelo->registro(registro_id: $registro['nom_nomina_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }
        $nom_nomina_modelo->registro = $nom_nomina;

        $registro = $this->asigna_codigo(keys_registro: $keys_registro,keys_row:  $keys_row,
            modelo:  $nom_nomina_modelo,registro:  $registro);

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

        if((float)$registro['importe_gravado'] <= 0.0 && (float)$registro['importe_exento']<=0.0){
            return $this->error->error(mensaje: 'Error ingrese un importe valido exento o gravado mayor a 0',
                data: $registro);
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
     * Genera la base para el calculo de impuestos
     * @param int $partida_percepcion_id Partida de nomina
     * @return array|stdClass
     * @version 0.181.6
     */
    public function base_calculo_impuesto(int $partida_percepcion_id): array|stdClass
    {
        if($partida_percepcion_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $partida_percepcion_id debe ser mayor a 0',
                data: $partida_percepcion_id);
        }
        $nom_par_percepcion = $this->registro(registro_id: $partida_percepcion_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nom_par_percepcion', data: $nom_par_percepcion);
        }

        $keys = array('cat_sat_periodicidad_pago_nom_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $nom_par_percepcion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar $nom_par_percepcion', data: $valida);
        }
        $keys = array('nom_nomina_fecha_final_pago');
        $valida = $this->validacion->fechas_in_array(data: $nom_par_percepcion, keys: $keys);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar $nom_par_percepcion', data: $valida);
        }

        return $nom_par_percepcion;
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

    /**
     * Obtiene los datos de una deduccion
     * @param float $monto
     * @param int $nom_deduccion_id
     * @param int $nom_nomina_id
     * @return array|stdClass
     */
    public function data_deduccion(float $monto, int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
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

    public function data_otro_pago(float $monto, int $nom_otro_pago_id, int $nom_nomina_id): array|stdClass
    {
        $data_existe = $this->existe_data_otro_pago(nom_otro_pago_id:$nom_otro_pago_id, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }
        $nom_par_deduccion_ins = $this->nom_par_otro_pago_aut(monto: $monto, nom_otro_pago_id: $nom_otro_pago_id,
            nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar deduccion', data: $nom_par_deduccion_ins);
        }

        $data_existe->row_ins = $nom_par_deduccion_ins;
        return $data_existe;
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

        $transacciones = (new transaccion_fc())->transacciones_por_nomina(mod_nominas: $this, nom_nomina_id: $nom_percepcion->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones', data: $transacciones);
        }


        return $r_elimina_bd;
    }


    /**
     * Verifica si existe una deduccion1
     * @param int $nom_deduccion_id Deduccion a verificar
     * @param int $nom_nomina_id Nomina a verificar
     * @return array|stdClass
     */
    public function existe_data_deduccion(int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
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

    public function existe_data_otro_pago(int $nom_otro_pago_id, int $nom_nomina_id): array|stdClass
    {
        $filtro = $this->filtro_partida(id: $nom_otro_pago_id, nom_nomina_id: $nom_nomina_id, tabla: 'nom_otro_pago');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar filtro', data: $filtro);
        }

        $existe = (new nom_par_otro_pago($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $existe);
        }
        $data = new stdClass();
        $data->filtro = $filtro;
        $data->existe = $existe;
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
    public function imss(int $partida_percepcion_id): array
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

    public function imss_por_nomina(int $nom_nomina_id): array
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
    public function modifica_bd_percepcion(array $registro, int $id, bool $reactiva = false, $es_subsidio = false): array|stdClass
    {
        $nom_percepcion = $this->registro(registro_id:$id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $nom_percepcion);
        }

        $r_modifica_bd = parent::modifica_bd(registro: $registro,id:  $id,reactiva:  $reactiva); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al modificar registro', data: $r_modifica_bd);
        }

        if(!$es_subsidio) {
            $transacciones = (new transaccion_fc())->transacciones_por_nomina(
                mod_nominas: $this, nom_nomina_id: $nom_percepcion->nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al integrar deducciones', data: $transacciones);
            }
        }

        $aplica_subsidio = (new nom_nomina($this->link))->aplica_subsidio_percepcion(
            nom_nomina_id: $nom_percepcion->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si aplica subsidio registro', data: $aplica_subsidio);
        }

        if($aplica_subsidio){
            $deduccion_isr_id = (new nom_nomina($this->link))->deduccion_isr_id(nom_nomina_id: $nom_percepcion->nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener deduccion isr id', data: $deduccion_isr_id);
            }

            $otro_pago_subsidio_id = (new nom_nomina($this->link))->otro_pago_subsidio_id(nom_nomina_id: $nom_percepcion->nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener deduccion subsidio id', data: $otro_pago_subsidio_id);
            }

            $impuestos = (new calcula_nomina())->calculos(link:$this->link, nom_nomina_id: $nom_percepcion->nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener impuestos', data: $impuestos);
            }

            $nom_data_subsidio = array();
            $nom_data_subsidio['nom_par_deduccion_id'] = $deduccion_isr_id;
            $nom_data_subsidio['nom_par_otro_pago_id'] = $otro_pago_subsidio_id;
            $nom_data_subsidio['monto_isr_bruto'] = $impuestos->isr;
            $nom_data_subsidio['monto_subsidio_bruto'] = $impuestos->subsidio;

            /*
            $r_alta_nom_par_data_subsidio = (new nom_data_subsidio(link: $this->link))->alta_registro(
                registro:$nom_data_subsidio);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar nom_par_data_subsidio',
                    data: $r_alta_nom_par_data_subsidio);
            }
            */


        }

        return $r_modifica_bd;
    }



    private function nom_nomina_id(int $nom_par_id): int|array
    {
        $nom_nomina = $this->registro(registro_id: $nom_par_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina',data:  $nom_nomina);
        }
        return (int)$nom_nomina->noim_nomina_id;
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

    private function nom_par_otro_pago_aut(float $monto, int $nom_otro_pago_id, int $nom_nomina_id): array
    {
        $nom_par_otro_pago_ins = array();
        $nom_par_otro_pago_ins['nom_nomina_id'] =$nom_nomina_id;
        $nom_par_otro_pago_ins['nom_otro_pago_id'] = $nom_otro_pago_id;
        $nom_par_otro_pago_ins['importe_gravado'] = 0.0;
        $nom_par_otro_pago_ins['importe_exento'] = $monto;
        return $nom_par_otro_pago_ins;
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



}
