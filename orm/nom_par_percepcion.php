<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;
use Throwable;

class nom_par_percepcion extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_nomina'=>$tabla, 'nom_percepcion'=>$tabla,
            'cat_sat_tipo_percepcion_nom'=>'nom_percepcion','cat_sat_periodicidad_pago_nom'=>'nom_nomina',
            'em_empleado'=>'nom_nomina');
        $campos_obligatorios = array('nom_nomina_id','descripcion_select','alias','codigo_bis','nom_percepcion_id',
            'importe_gravado','importe_exento');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    /**
     * @throws JsonException
     */
    public function alta_bd(): array|stdClass
    {

        $keys = array('nom_nomina_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $registro = $this->asigna_registro_alta(registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }
        $this->registro = $registro;

        $total = $this->total_percepcion(registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total', data: $total);
        }

        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar percepcion', data: $r_alta_bd);
        }

        $isr = $this->calcula_isr_nomina(nom_par_percepcion_id: $r_alta_bd->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        if($isr>0.0){


            $filtro = array();
            $filtro['nom_nomina.id'] = $this->registro['nom_nomina_id'];
            $filtro['nom_deduccion.id'] = 1;

            $existe = (new nom_par_deduccion($this->link))->existe(filtro: $filtro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $existe);
            }


            $nom_par_deduccion_ins = $this->nom_par_deduccion_aut(monto: $isr, nom_deduccion_id: 1,
                nom_nomina_id: $this->registro['nom_nomina_id']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar deduccion', data: $nom_par_deduccion_ins);
            }

            if($existe){
                $nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al obtener deduccion', data: $nom_par_deduccion);
                }

                $r_modifica_nom_par_deduccion = (new nom_par_deduccion($this->link))->modifica_bd(
                    registro:$nom_par_deduccion_ins, id: $nom_par_deduccion->registros[0]['nom_par_deduccion_id']);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al modificar deduccion', data: $r_modifica_nom_par_deduccion);
                }
            }
            else{
                $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_ins);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al registrar deduccion', data: $r_alta_nom_par_deduccion);
                }

            }

            $nom_par_percepcion = $this->registro(registro_id:$r_alta_bd->registro_id, retorno_obj: true);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_par_percepcion);
            }



            $imss = (new calcula_imss())->imss(
                cat_sat_periodicidad_pago_nom_id: $nom_par_percepcion->cat_sat_periodicidad_pago_nom_id,
                fecha:$nom_par_percepcion->nom_nomina_fecha_final_pago, n_dias: $nom_par_percepcion->nom_nomina_num_dias_pagados,
                sbc: $nom_par_percepcion->em_empleado_salario_diario_integrado, sd: $nom_par_percepcion->em_empleado_salario_diario);

            if(errores::$error){
                return $this->error->error(mensaje: 'Error al calcular imss', data: $imss);
            }

            if((float)$imss['total']>0.0) {

                $filtro = array();
                $filtro['nom_nomina.id'] = $this->registro['nom_nomina_id'];
                $filtro['nom_deduccion.id'] = 2;

                $existe = (new nom_par_deduccion($this->link))->existe(filtro: $filtro);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $existe);
                }


                $nom_par_deduccion_ins = $this->nom_par_deduccion_aut(monto: (float)$imss['total'], nom_deduccion_id: 2,
                    nom_nomina_id: $this->registro['nom_nomina_id']);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al generar deduccion', data: $nom_par_deduccion_ins);
                }


                if($existe){
                    $nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
                    if(errores::$error){
                        return $this->error->error(mensaje: 'Error al obtener deduccion', data: $nom_par_deduccion);
                    }

                    $r_modifica_nom_par_deduccion = (new nom_par_deduccion($this->link))->modifica_bd(
                        registro:$nom_par_deduccion_ins, id: $nom_par_deduccion->registros[0]['nom_par_deduccion_id']);
                    if(errores::$error){
                        return $this->error->error(mensaje: 'Error al modificar deduccion', data: $r_modifica_nom_par_deduccion);
                    }
                }
                else {
                    $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $nom_par_deduccion_ins);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al registrar deduccion', data: $r_alta_nom_par_deduccion);
                    }
                }
            }
        }


        return $r_alta_bd;
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

    private function asigna_registro_alta(array $registro): array
    {

        $keys_registro = array('nom_nomina_id');
        $keys_row = array('cat_sat_periodicidad_pago_nom_id','em_empleado_rfc','im_registro_patronal_id');
        $modelo = new nom_nomina($this->link);
        $registro = $this->asigna_codigo(keys_registro: $keys_registro,keys_row:  $keys_row,
            modelo:  $modelo,registro:  $registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }

        $modelo = new nom_percepcion($this->link);
        $registro = $this->asigna_descripcion(modelo:$modelo, registro: $registro);
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

    /**
     * Calcula el isr de una nomina
     * @param int $nom_par_percepcion_id Identifcador del modelo
     * @return float|array
     * @version 0.67.1
     */
    private function calcula_isr_nomina(int $nom_par_percepcion_id): float|array
    {

        $nom_nomina = $this->registro(registro_id:$nom_par_percepcion_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }

        $isr = 0.0;
        $total_gravado = (new nom_nomina($this->link))->total_gravado(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular total gravado', data: $total_gravado);
        }


        if($total_gravado >0.0) {

            $isr = $this->isr_total_nomina_por_percepcion(
                nom_par_percepcion_id: $nom_par_percepcion_id, total_gravado: $total_gravado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
            }



        }

        return $isr;
    }



    private function isr_total_nomina_por_percepcion(int $nom_par_percepcion_id, string $total_gravado): float|array
    {
        $nom_par_percepcion = $this->registro(registro_id: $nom_par_percepcion_id, retorno_obj: true);
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

    private function nom_par_deduccion_aut(float $monto, int $nom_deduccion_id, int $nom_nomina_id): array
    {
        $nom_par_deduccion_ins = array();
        $nom_par_deduccion_ins['nom_nomina_id'] =$nom_nomina_id;
        $nom_par_deduccion_ins['nom_deduccion_id'] = $nom_deduccion_id;
        $nom_par_deduccion_ins['importe_gravado'] = $monto;
        $nom_par_deduccion_ins['importe_exento'] = 0.0;
        return $nom_par_deduccion_ins;
    }

    private function total_percepcion(array $registro): float|array
    {
        $total = $registro['importe_exento']+ $registro['importe_gravado'];
        $total = round($total,2);

        if($total<=0.0){
            return $this->error->error(mensaje: 'Error total es 0', data: $total);
        }
        return $total;
    }
}