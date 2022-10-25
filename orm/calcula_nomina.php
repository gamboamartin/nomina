<?php

namespace models;
use DateTime;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;
use Throwable;

class calcula_nomina{

    private errores $error;
    private validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();

    }

    /**
     * Calcula en semanas para cfdi antiguedad
     * @param string $fecha_final_pago Fecha final de pago de nomina
     * @param string $fecha_inicio_rel_laboral Fecha de inicio de relacion laboral del empleado
     * @return array|string
     * @version 0.292.9
     */
    public function antiguedad_empleado(string $fecha_final_pago, string $fecha_inicio_rel_laboral): array|string
    {
        $fecha_final_pago = trim($fecha_final_pago);
        if($fecha_final_pago === ''){
            return $this->error->error(mensaje: 'Error $fecha_final_pago esta vacia',data: $fecha_final_pago);
        }
        $fecha_inicio_rel_laboral = trim($fecha_inicio_rel_laboral);
        if($fecha_inicio_rel_laboral === ''){
            return $this->error->error(mensaje: 'Error $fecha_inicio_rel_laboral esta vacia',
                data: $fecha_inicio_rel_laboral);
        }
        
        $valida = (new validacion())->valida_fecha(fecha: $fecha_final_pago);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error $fecha_final_pago invalida',data: $valida);
        }

        $valida = (new validacion())->valida_fecha(fecha: $fecha_inicio_rel_laboral);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error $fecha_inicio_rel_laboral invalida',data: $valida);
        }

        if($fecha_inicio_rel_laboral>$fecha_final_pago){
            return $this->error->error(
                mensaje: 'Error $fecha_inicio_rel_laboral es mayor a $fecha_final_pago',data: $valida);
        }

        try {
            $fecha_inicio = new DateTime($fecha_inicio_rel_laboral);
            $fecha_fin = new DateTime($fecha_final_pago);
            $diferencia = $fecha_inicio->diff($fecha_fin);
            $n_dias = (int)$diferencia->days;
            $semanas = $n_dias / 7;
            $semanas = (int)$semanas;
            $antiguedad_empleado = "P$semanas" . "W";
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al calcular semanas',data: $e);
        }
        return $antiguedad_empleado;


    }

    public function calcula_impuestos_netos_por_nomina(PDO $link, int $nom_nomina_id): array|stdClass
    {
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }
        $impuestos = $this->calculos(link:$link, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener impuestos', data: $impuestos);
        }


        $data = $this->calcula_montos_isr_sub(monto_isr: round($impuestos->isr,2),
            monto_subsidio: round($impuestos->subsidio,2));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular isr y subsidio', data: $data);
        }


        return $data;
    }

    private function calcula_montos_isr_sub(float $monto_isr, float $monto_subsidio){

        $monto_isr_neto = $monto_isr;
        $monto_subsidio_neto = $monto_subsidio;


        if($monto_isr >= $monto_subsidio){
            $monto_isr_neto = round($monto_isr-$monto_subsidio,2);
            $monto_subsidio_neto = 0;

        }
        if($monto_isr < $monto_subsidio){
            $monto_isr_neto = 0;
            $monto_subsidio_neto = round($monto_subsidio-$monto_isr,2);
        }
        $data = new stdClass();
        $data->isr_neto = $monto_isr_neto;
        $data->subsidio_neto = $monto_subsidio_neto;

        return $data;
    }

    /**
     * Obtiene los calculos de isr y subsidio por nomina
     * @param PDO $link Conexion a la base de datos
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return array|stdClass
     * @version 0.287.9
     */
    public function calculos(PDO $link, int $nom_nomina_id): array|stdClass
    {
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }
        $isr = (new calculo_isr())->calcula_isr_por_nomina(link: $link,nom_nomina_id:  $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener isr',data:  $isr);
        }

        $subsidio = (new calculo_subsidio())->calcula_subsidio_por_nomina(link: $link,nom_nomina_id:  $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener subsidio',data:  $subsidio);
        }

        $data = new stdClass();
        $data->isr = $isr;
        $data->subsidio = $subsidio;

        return $data;
    }

    public function nomina_descuentos(int $cat_sat_periodicidad_pago_nom_id, float $em_salario_diario,
                                      float $em_empleado_salario_diario_integrado,
                                      PDO $link, string $nom_nomina_fecha_final_pago,
                                      int $nom_nomina_num_dias_pagados, float $total_gravado): float|array
    {

        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $cat_sat_periodicidad_pago_nom_id debe ser mayor a 0',
                data: $cat_sat_periodicidad_pago_nom_id);
        }
        if($total_gravado<0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $total_gravado);
        }

        if($nom_nomina_fecha_final_pago === ''){
            $nom_nomina_fecha_final_pago = date('Y-m-d');
        }

        $isr = 0.0;
        $subsidio = 0.0;
        $imss['total'] = 0.0;

        if($total_gravado > 0.0) {
            $isr = (new calculo_isr())->isr(
                cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id, link: $link,
                monto: $total_gravado, fecha: $nom_nomina_fecha_final_pago);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
            }

            $subsidio = (new calculo_subsidio())->subsidio(
                cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id, link: $link, monto: $total_gravado,
                fecha: $nom_nomina_fecha_final_pago);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener $subsidio', data: $subsidio);
            }

            $imss = (new calcula_imss())->imss(cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id,
                fecha: $nom_nomina_fecha_final_pago, n_dias: $nom_nomina_num_dias_pagados,
                sbc: $em_empleado_salario_diario_integrado, sd: $em_salario_diario);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener imss', data: $imss);
            }
        }

        return round((round($isr,2) + round($imss['total'],2)) - round( $subsidio,2),2);

    }

    public function nomina_neto(int $cat_sat_periodicidad_pago_nom_id, float $em_salario_diario,
                                      float $em_empleado_salario_diario_integrado,
                                      PDO $link, string $nom_nomina_fecha_final_pago,
                                      int $nom_nomina_num_dias_pagados, float $total_neto): float|array
    {
        $importe_deducciones = 0;
        $total_gravado = $total_neto;

        $importe_deducciones = $this->nomina_descuentos($cat_sat_periodicidad_pago_nom_id, $em_salario_diario,
            $em_empleado_salario_diario_integrado, $link, $nom_nomina_fecha_final_pago, $nom_nomina_num_dias_pagados, $total_gravado);
        if(errores::$error){
            return  $this->error->error(mensaje: 'Error al obtener deducciones', data: $importe_deducciones);
        }

        $calculado = false;
        if($importe_deducciones < 0 ){
            while (!$calculado){
                $importe_deducciones = $this->nomina_descuentos($cat_sat_periodicidad_pago_nom_id, $em_salario_diario,
                    $em_empleado_salario_diario_integrado, $link, $nom_nomina_fecha_final_pago, $nom_nomina_num_dias_pagados, $total_gravado);
                if(errores::$error){
                    return  $this->error->error(mensaje: 'Error al obtener deducciones', data: $importe_deducciones);
                }

                $total_neto_calculado = $total_gravado - round($importe_deducciones,2);

                if((float)round($total_neto_calculado,2) === (float)round($total_neto,2)) {
                    $calculado = true;
                } else{
                    $total_gravado = round(round($total_gravado,2) - .01,2);
                }
            }
        }else{
            while (!$calculado){
                $importe_deducciones = $this->nomina_descuentos($cat_sat_periodicidad_pago_nom_id, $em_salario_diario,
                    $em_empleado_salario_diario_integrado, $link, $nom_nomina_fecha_final_pago, $nom_nomina_num_dias_pagados, $total_gravado);
                if(errores::$error){
                    return  $this->error->error(mensaje: 'Error al obtener deducciones', data: $importe_deducciones);
                }

                $total_neto_calculado = $total_gravado - round($importe_deducciones,2);

                if((float)round($total_neto_calculado,2)  === (float)round($total_neto,2)) {
                    $calculado = true;
                }else{
                    $total_gravado = round(round($total_gravado,2) + .01,2);
                }
            }
        }

        return $total_gravado;



    }


}
