<?php

namespace gamboamartin\nomina\models;

use DateTime;
use gamboamartin\banco\models\bn_sucursal;
use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\errores\errores;
use gamboamartin\im_registro_patronal\models\im_conf_pres_empresa;
use gamboamartin\im_registro_patronal\models\im_detalle_conf_prestaciones;
use stdClass;
use Throwable;

class em_empleado extends \gamboamartin\empleado\models\em_empleado
{

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $registro = $this->registro;
        $keys_em_cta_banc = array('num_cuenta', 'clabe', 'em_empleado_id', 'bn_sucursal_id');
        $registro_em_cta_banc = array();

        foreach ($keys_em_cta_banc as $campo_cta) {
            if (isset($registro[$campo_cta])) {
                $registro_em_cta_banc[$campo_cta] = $registro[$campo_cta];
                unset($registro[$campo_cta]);
            }
        }
        $this->registro = $registro;

        $r_alta = parent::alta_bd($keys_integra_ds);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta empleado', data: $r_alta);
        }

        $id_predeterminado = (new bn_sucursal(link: $this->link))->id_predeterminado();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener una sucursal predeterminada para banco', data: $id_predeterminado);
        }

        $registro_em_cta_banc['codigo'] = $this->registro['codigo'];
        $registro_em_cta_banc['descripcion'] = $this->registro['descripcion'];
        $registro_em_cta_banc['em_empleado_id'] = $r_alta->registro_id;
        $registro_em_cta_banc['bn_sucursal_id'] = $id_predeterminado;
        $alta_em_cta_ban = (new em_cuenta_bancaria(link: $this->link))->alta_registro(registro: $registro_em_cta_banc);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cuenta bancaria', data: $alta_em_cta_ban);
        }

        return $r_alta;
    }


    public function calcula_sdi(int $em_empleado_id, string $fecha_inicio_rel, float $salario_diario): float|array
    {
        $factor = $this->obten_factor(em_empleado_id: $em_empleado_id, fecha_inicio_rel: $fecha_inicio_rel);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener factor', data: $factor);
        }

        $sdi = $salario_diario * $factor;

        return round($sdi, 2);
    }

    public function obten_conf(int $em_empleado_id): array
    {
        $imss_modelo = new im_conf_pres_empresa($this->link);
        $empresa = $imss_modelo->obten_configuraciones_empresa(em_empleado_id: $em_empleado_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener empresa perteneciente', data: $empresa);
        }

        if (count($empresa) <= 0){
            return $this->error->error(mensaje: "Error no existe una conf. de im_conf_pres_empresa para el empleado $em_empleado_id",
                data: $empresa);
        }

        $im_conf_prestaciones_id = $empresa[0]['im_conf_prestaciones_id'];
        $detalle_conf = (new im_detalle_conf_prestaciones($this->link))->obten_detalle_conf
        (im_conf_prestaciones_id: $im_conf_prestaciones_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el detalle de las conf de prestaciones',
                data: $detalle_conf);
        }

        return $detalle_conf;
    }

    public function obten_detalle(int $em_empleado_id, string $fecha_inicio_rel)
    {
        $detalles = $this->obten_conf(em_empleado_id: $em_empleado_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener detalle', data: $detalles);
        }

        $fecha_calculo = date('Y-m-d');
        $years = $this->obten_years(fecha_calculo: $fecha_calculo, fecha_inicio_rel: $fecha_inicio_rel);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener años de trabajo', data: $years);
        }

        $datos_sdi = array();
        foreach ($detalles as $detalle) {
            if ((int)$detalle['im_detalle_conf_prestaciones_n_year'] === (int)$years) {
                $datos_sdi = $detalle;
            }
        }

        return $datos_sdi;
    }

    public function obten_factor(int $em_empleado_id, string $fecha_inicio_rel): float|array
    {
        $detalle = $this->obten_detalle(em_empleado_id: $em_empleado_id, fecha_inicio_rel: $fecha_inicio_rel);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener detalle', data: $detalle);
        }

        $prima_vacacional = round((float)$detalle['im_detalle_conf_prestaciones_n_dias_vacaciones'] * .25, 4);
        $prima_mas_aguinaldo = round($prima_vacacional +
            (float)$detalle['im_detalle_conf_prestaciones_n_dias_aguinaldo'], 4);
        $dias_sdi = round($prima_mas_aguinaldo + 365, 4);

        return round($dias_sdi / 365, 4);
    }

    private function obten_years(string $fecha_calculo, string $fecha_inicio_rel): int|array
    {
        $fecha_calculo = trim($fecha_calculo);
        if ($fecha_calculo === '') {
            return $this->error->error("Error fecha calculo esta vacia", $fecha_calculo);
        }
        $fecha_inicio_rel = trim($fecha_inicio_rel);
        if ($fecha_inicio_rel === '') {
            return $this->error->error("Error fecha_inicio_rel esta vacia", $fecha_inicio_rel);
        }

        $valida = $this->validacion->valida_fecha($fecha_calculo);
        if (errores::$error) {
            return $this->error->error("Error al validar fecha_calculo", $valida);
        }

        $valida = $this->validacion->valida_fecha($fecha_inicio_rel);
        if (errores::$error) {
            return $this->error->error("Error al validar fecha_inicio_rel", $valida);
        }

        if ($fecha_inicio_rel > $fecha_calculo) {
            return $this->error->error("Error la fecha inicio rel laboral debe ser mas antigua que la fecha 
            calculada", $fecha_calculo);
        }

        try {
            $date1 = new DateTime($fecha_inicio_rel);
            $date2 = new DateTime($fecha_calculo);
            $diff = $date1->diff($date2);
        } catch (Throwable $e) {
            return $this->error->error(mensaje: "Error al calcular fecha", data: $e);
        }


        return $diff->y;
    }
}