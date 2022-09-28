<?php

namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class calcula_cuota_obrero_patronal{

    private errores $error;
    private validacion $validacion;
    public float $porc_riesgo_trabajo = 0;
    public float $porc_enf_mat_cuota_fija = 20.4;
    public float $porc_enf_mat_cuota_adicional = 1.1;
    public float $porc_enf_mat_gastos_medicos = 1.05;
    public float $porc_enf_mat_pres_dinero = 0.7;
    public float $porc_invalidez_vida = 1.75;
    public float $porc_guarderia_prestaciones_sociales = 1;
    public float $porc_retiro = 2;
    public float $porc_ceav = 3.15;
    public float $porc_credito_vivienda = 5;

    public array $salario_minimo = array(2020=>123.22,2021=>141.70,2022=>172.87);
    public array $uma = array(2020=>86.88,2021=>89.62,2022=>96.22);

    public string $year = '';

    public float $monto_uma = 0.0;
    public float $n_dias = 0.0;
    public float $sbc = 0.0;
    public float $sd= 0.0;
    public float $uma_3v = 0.0;
    public float $dif_uma_sbc = 0.0;

    public float $cuota_riesgo_trabajo = 0.0;
    public float $cuota_enf_mat_cuota_fija = 0.0;
    public float $cuota_enf_mat_cuota_adicional = 0.0;
    public float $cuota_enf_mat_gastos_medicos = 0.0;
    public float $cuota_enf_mat_pres_dinero = 0.0;
    public float $cuota_invalidez_vida = 0.0;
    public float $cuota_guarderia_prestaciones_sociales = 0.0;
    public float $cuota_retiro = 0.0;
    public float $cuota_ceav = 0.0;
    public float $cuota_credito_vivienda = 0.0;

    public float $total= 0.0;


    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();

    }


    private function calcula(): bool|array
    {
        $valida = $this->valida_parametros();
        if(errores::$error){
            return $this->error->error('Error al validar exedente', $valida);
        }

        $riesgo_de_trabajo = $this->riesgo_de_trabajo();
        if(errores::$error){
            return $this->error->error('Error al obtener riesgo_de_trabajo', $riesgo_de_trabajo);
        }

        return true;
    }

    private function riesgo_de_trabajo(){
        if($this->porc_riesgo_trabajo <= 0.0){
            return $this->error->error("Error el factor debe ser menor a 0", $this->porc_riesgo_trabajo);
        }
        if($this->sbc <= 0.0){
            return $this->error->error("Error salario base de cotizacion debe ser menor a 0",
                $this->sbc);
        }
        if($this->n_dias <= 0.0){
            return $this->error->error("Error los dias trabajados no debe ser menor a 0",
                $this->n_dias);
        }

        $cuota_diaria =  round($this->sbc * $this->n_dias ,2);
        $res = round($cuota_diaria * $this->porc_riesgo_trabajo,2);
        $this->cuota_riesgo_trabajo = round($res/100,2);

        return $this->cuota_riesgo_trabajo;
    }

    private function valida_parametros(){
        if($this->porc_riesgo_trabajo<=0){
            return $this->error->error('Error riesgo de trabajo debe ser mayor a 0', $this->porc_riesgo_trabajo);
        }
        if($this->sbc<=0){
            return $this->error->error('Error sbc debe ser mayor a 0', $this->sbc);
        }
        if($this->n_dias<=0){
            return $this->error->error('Error n_dias debe ser mayor a 0', $this->n_dias);
        }

        return true;
    }
}
