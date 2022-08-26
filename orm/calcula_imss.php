<?php

namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class calcula_imss{

    private errores $error;
    private validacion $validacion;
    public float $porc_prestaciones_en_dinero_trabajador = .0025;
    public float $porc_pensionados_beneficiarios = .00375;
    public float $porc_invalidez_vida = .00625;
    public float $porc_cesantia = .01125;
    public float $porc_exc = .004;
    public array $salario_minimo = array('2020'=>123.22,'2021'=>141.70,'2022'=>141.70);
    public array $uma = array('2020'=>86.88,'2021'=>89.62,'2022'=>89.62);
    public string $year = '';
    public float $monto_uma = 0.0;
    public float $n_dias = 0.0;
    public float $sbc = 0.0;
    public float $sd= 0.0;
    public float $uma_3v = 0.0;
    public float $dif_uma_sbc = 0.0;
    public float $excedente = 0.0;
    public float $total_percepciones = 0.0;
    public float $prestaciones_en_dinero = 0.0;
    public float $pensionados_beneficiarios= 0.0;
    public float $invalidez_vida= 0.0;
    public float $cesantia= 0.0;
    public float $total= 0.0;
    public int $n_dias_mes= 0;
    public array $dias_vacaciones = array(6,8,10,12,14,14,14,14,14,16,16,16,16,16,18,18,18,18,18,20,20,20,20,20,
        22,22,22,22,22);


    public function __construct(){
        $this->error = new errores();

    }

    public function calcula(): bool|array
    {

        $valida = $this->valida_exedente();
        if(errores::$error){
            return $this->error->error('Error al validar exedente', $valida);
        }

        $excedente = $this->excedente_imss();
        if(errores::$error){
            return $this->error->error('Error al obtener exedente', $excedente);
        }
        $total_percepciones = $this->total_percepciones();
        if(errores::$error){
            return $this->error->error('Error al obtener percepciones', $total_percepciones);
        }
        $prestaciones_en_dinero = $this->prestaciones_en_dinero();
        if(errores::$error){
            return $this->error->error('Error', $prestaciones_en_dinero);
        }
        $pensionados_beneficiarios = $this->pensionados_beneficiarios();
        if(errores::$error){
            return $this->error->error('Error', $pensionados_beneficiarios);
        }
        $invalidez_vida = $this->invalidez_vida();
        if(errores::$error){
            return $this->error->error('Error', $invalidez_vida);
        }
        $cesantia = $this->cesantia();
        if(errores::$error){
            return $this->error->error('Error', $cesantia);
        }
        $total = $this->total();
        if(errores::$error){
            return $this->error->error('Error', $total);
        }
        return true;
    }

    public function cesantia(): float|array
    {
        if($this->total_percepciones <=0.0){
            return $this->error->error("Error total_percepciones debe ser mayor a 0", $this->total_percepciones);
        }
        $this->cesantia = $this->total_percepciones * $this->porc_cesantia;
        $this->cesantia = round($this->cesantia,2);
        return $this->cesantia;
    }

    public function data_array(): array
    {
        $data['prestaciones_en_dinero_trabajador'] = $this->prestaciones_en_dinero;
        $data['pensionados_beneficiarios'] = $this->pensionados_beneficiarios;
        $data['invalidez_vida'] = $this->invalidez_vida;
        $data['cesantia'] = $this->cesantia;
        $data['excedente'] = $this->excedente;
        $data['total'] = $this->total;
        $data['n_dias_mes'] = $this->n_dias_mes;
        $data['n_dias'] = $this->n_dias;
        return $data;
    }

    public function dias_quincena(string $fecha): float|array
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error al validar fecha", $valida);
        }
        $dias_mes = (int)date('t',strtotime($fecha));
        $this->n_dias_mes = $dias_mes;
        $this->n_dias = $dias_mes/2;
        $this->n_dias = round($this->n_dias,2);
        return $this->n_dias;
    }

    public function excedente_imss(): float|array
    {

        $valida = $this->valida_exedente();
        if(errores::$error){
            return $this->error->error('Error al validar exedente', $valida);
        }

        $this->uma_3v = round($this->monto_uma  * 3,2);
        $this->dif_uma_sbc = round($this->sbc - $this->uma_3v,2);


        $this->excedente = 0.0;
        if($this->dif_uma_sbc >0.0){
            $this->excedente = $this->dif_uma_sbc * $this->porc_exc;
            $this->excedente = round($this->excedente * $this->n_dias,2);
        }
        return $this->excedente;
    }

    private function genera_imss(int $cat_sat_periodicidad_pago_nom_id, string $fecha, float $n_dias, float $sbc,
                                 float $sd): array|float
    {
        $valida = $this->valida_imss(fecha: $fecha,n_dias:  $n_dias, sbc: $sbc,sd:  $sd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar', data: $valida);
        }
        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $cat_sat_periodicidad_pago_nom_id en menor a 0',
                data: $cat_sat_periodicidad_pago_nom_id);
        }

        $init = $this->init_data_base($fecha, $n_dias, $sbc, $sd, $cat_sat_periodicidad_pago_nom_id);
        if(errores::$error){
            return $this->error->error('Error al inicializar', $init);
        }

        if($this->sd > (float)$this->salario_minimo[$this->year]){
            $calcula = $this->calcula();
            if(errores::$error){
                return $this->error->error('Error al calcular', $calcula);
            }
        }
        return $this->total;
    }

    public function imss(int $cat_sat_periodicidad_pago_nom_id, string $fecha, float $n_dias, float $sbc, float $sd): array
    {
        $valida = $this->valida_imss(fecha: $fecha,n_dias:  $n_dias,sbc:  $sbc,sd:  $sd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar imss', data: $valida);
        }
        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $cat_sat_periodicidad_pago_nom_id en menor a 0',
                data:  $cat_sat_periodicidad_pago_nom_id);
        }


        $init = $this->genera_imss($fecha, $n_dias, $sbc, $sd, $cat_sat_periodicidad_pago_nom_id);
        if(errores::$error){
            return $this->error->error('Error al generar imss', $init);
        }
        $data = $this->data_array();
        if(errores::$error){
            return $this->error->error('Error al asignar datos', $data);
        }
        return $data;
    }

    public function init_base(int  $cat_sat_periodicidad_pago_nom_id, string $fecha, float $n_dias, float $sbc, float $sd): stdClass|array
    {
        $valida = $this->valida_imss(fecha: $fecha,n_dias:  $n_dias, sbc: $sbc,sd:  $sd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar imss', data: $valida);
        }
        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $sat_nomina_periodicidad_pago_id en menor a 0',
                data:  $cat_sat_periodicidad_pago_nom_id);
        }

        $this->monto_uma = $this->uma[2022];

        $dias = $this->n_dias($n_dias, $cat_sat_periodicidad_pago_nom_id, $fecha);
        if(errores::$error) {
            return $this->error->error("Error al obtener dias", $dias);
        }


        $this->sbc = round($sbc,2);
        $this->sd = round($sd,2);

        $data = new stdClass();
        $data->year = $this->year;
        $data->monto_uma = $this->monto_uma;
        $data->n_dias = $this->n_dias;
        $data->sbc = $this->sbc;
        $data->sd = $this->sd;

        return $data;
    }

    public function init_data(): stdClass
    {
        $this->prestaciones_en_dinero = 0.0;
        $this->pensionados_beneficiarios = 0.0;
        $this->invalidez_vida = 0.0;
        $this->cesantia = 0.0;
        $this->total = 0.0;

        $data = new stdClass();
        $data->prestaciones_en_dinero = $this->prestaciones_en_dinero;
        $data->pensionados_beneficiarios = $this->pensionados_beneficiarios;
        $data->invalidez_vida = $this->invalidez_vida;
        $data->cesantia = $this->cesantia;
        $data->total = $this->total;

        return $data;
    }

    public function init_data_base(int $cat_sat_periodicidad_pago_nom_id, string $fecha, float $n_dias, float $sbc,
                                   float $sd): array|stdClass
    {
        $valida = $this->valida_imss(fecha: $fecha,n_dias:  $n_dias, sbc: $sbc,sd:  $sd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar',data:  $valida);
        }
        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $cat_sat_periodicidad_pago_nom_id en menor a 0',
                data:  $cat_sat_periodicidad_pago_nom_id);
        }

        $base = $this->init_base($fecha, $n_dias, $sbc, $sd,$cat_sat_periodicidad_pago_nom_id);
        if(errores::$error){
            return $this->error->error('Error al inicializar', $base);
        }
        $init = $this->init_data();
        if(errores::$error){
            return $this->error->error('Error al inicializar', $init);
        }


        $data = new stdClass();
        $data->base = $base;
        $data->init = $init;

        return $data;
    }

    public function invalidez_vida(): float|array
    {
        if($this->total_percepciones <=0.0){
            return $this->error->error("Error total_percepciones debe ser mayor a 0", $this->total_percepciones);
        }
        $this->invalidez_vida = $this->total_percepciones * $this->porc_invalidez_vida;
        $this->invalidez_vida = round($this->invalidez_vida,2);
        return $this->invalidez_vida;
    }

    public function n_dias(float $n_dias, int $sat_nomina_periodicidad_pago_id, string $fecha): float|array
    {
        if($n_dias<=0){
            return $this->error->error("Error n_dias en menor a 0", $n_dias);
        }
        if($sat_nomina_periodicidad_pago_id<=0){
            return $this->error->error('Error $sat_nomina_periodicidad_pago_id en menor a 0', $sat_nomina_periodicidad_pago_id);
        }
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error("Error al validar fecha", $valida);
        }

        $this->n_dias = round($n_dias,2);

        if($sat_nomina_periodicidad_pago_id=== 1 && $n_dias===15.0){
            $dias = $this->dias_quincena($fecha);
            if(errores::$error){
                return $this->error->error("Error al obtener dias", $dias);
            }
        }

        return $this->n_dias;
    }

    public function pensionados_beneficiarios(): float|array
    {
        if($this->total_percepciones <=0.0){
            return $this->error->error("Error total_percepciones debe ser mayor a 0", $this->total_percepciones);
        }
        $this->pensionados_beneficiarios = $this->total_percepciones * $this->porc_pensionados_beneficiarios;
        $this->pensionados_beneficiarios = round($this->pensionados_beneficiarios,2);
        return $this->pensionados_beneficiarios;
    }

    public function prestaciones_en_dinero(): float|array
    {
        if($this->total_percepciones <=0.0){
            return $this->error->error("Error total_percepciones debe ser mayor a 0", $this->total_percepciones);
        }
        $this->prestaciones_en_dinero = $this->total_percepciones * $this->porc_prestaciones_en_dinero_trabajador;
        $this->prestaciones_en_dinero = round($this->prestaciones_en_dinero,2);
        return $this->prestaciones_en_dinero;
    }

    public function total(): float|array
    {
        if($this->prestaciones_en_dinero<=0.0){
            return $this->error->error('Error $this->prestaciones_en_dinero debe ser mayor a 0', $this->prestaciones_en_dinero);
        }
        if($this->pensionados_beneficiarios<=0.0){
            return $this->error->error('Error $this->pensionados_beneficiarios debe ser mayor a 0', $this->pensionados_beneficiarios);
        }
        if($this->invalidez_vida<=0.0){
            return $this->error->error('Error $this->invalidez_vida debe ser mayor a 0', $this->invalidez_vida);
        }
        if($this->cesantia<=0.0){
            return $this->error->error('Error $this->cesantia debe ser mayor a 0', $this->cesantia);
        }
        if($this->excedente<0.0){
            return $this->error->error('Error $this->excedente debe ser mayor a 0', $this->excedente);
        }

        $this->total = $this->prestaciones_en_dinero + $this->pensionados_beneficiarios + $this->invalidez_vida +
            $this->cesantia + $this->excedente;
        $this->total = round($this->total,2);
        return $this->total;
    }


    public function total_percepciones(): float|array
    {
        if($this->sbc<=0){
            return $this->error->error('Error sbc debe ser mayor a 0', $this->sbc);
        }
        if($this->n_dias<=0){
            return $this->error->error('Error n dias debe ser mayor a 0', $this->n_dias);
        }

        $this->total_percepciones = round($this->sbc * $this->n_dias,2);
        return $this->total_percepciones;
    }

    public function valida_exedente(): bool|array
    {
        if($this->monto_uma<=0){
            return $this->error->error('Error uma debe ser mayor a 0', $this->monto_uma);
        }
        if($this->sbc<=0){
            return $this->error->error('Error sbc debe ser mayor a 0', $this->sbc);
        }
        if($this->n_dias<=0){
            return $this->error->error('Error n_dias debe ser mayor a 0', $this->n_dias);
        }
        return true;
    }

    public function valida_imss(string $fecha, float $n_dias, float $sbc, float $sd): bool|array
    {
        $valida = (new validacion())->valida_fecha(fecha: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fecha', data: $valida);
        }

        if($n_dias<=0.0){
            return $this->error->error(mensaje: 'Error al validar n_dias', data: $n_dias);
        }
        if($sbc<=0.0){
            return $this->error->error(mensaje: 'Error al validar sbc', data: $sbc);
        }
        if($sd<=0.0){
            return $this->error->error(mensaje: 'Error al validar $sd', data: $sd);
        }
        return true;
    }
}
