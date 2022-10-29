<?php

namespace models;

use base\orm\modelo;
use gamboamartin\empleado\models\em_abono_anticipo;
use gamboamartin\empleado\models\em_anticipo;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_csd;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\facturacion\models\fc_partida;
use gamboamartin\organigrama\models\org_sucursal;
use models\base\limpieza;
use PDO;
use stdClass;

class nom_nomina extends modelo
{
    public function __construct(PDO $link)
    {
        $tabla = __CLASS__;
        $columnas = array($tabla => false, 'dp_calle_pertenece' => $tabla, 'dp_calle' => 'dp_calle_pertenece',
            'dp_colonia_postal' => 'dp_calle_pertenece', 'dp_colonia' => 'dp_colonia_postal',
            'dp_cp' => 'dp_colonia_postal', 'dp_municipio' => 'dp_cp', 'dp_estado' => 'dp_municipio',
            'dp_pais' => 'dp_estado', 'em_empleado' => $tabla, 'fc_factura' => $tabla,'fc_csd' =>'fc_factura',
            'org_sucursal' => 'fc_csd','org_empresa'=> 'org_sucursal', 'cat_sat_periodicidad_pago_nom'=>$tabla,
            'im_registro_patronal'=>$tabla,'cat_sat_tipo_contrato_nom'=>$tabla, 'nom_periodo'=>$tabla,
            'cat_sat_tipo_nomina'=>$tabla,'cat_sat_tipo_jornada_nom'=>$tabla,
            'cat_sat_tipo_regimen_nom'=>'em_empleado','org_departamento'=>$tabla,'org_puesto'=>$tabla,
            'im_clase_riesgo'=>'im_registro_patronal','em_cuenta_bancaria'=>$tabla,
            'bn_sucursal'=>'em_cuenta_bancaria','bn_banco'=>'bn_sucursal');

        $campos_obligatorios = array('cat_sat_periodicidad_pago_nom_id', 'cat_sat_tipo_contrato_nom_id',
            'cat_sat_tipo_jornada_nom_id','cat_sat_tipo_nomina_id','dp_calle_pertenece_id', 'em_cuenta_bancaria_id',
            'fecha_inicial_pago', 'fecha_final_pago', 'im_registro_patronal_id', 'em_empleado_id','nom_periodo_id',
            'num_dias_pagados','org_departamento_id','org_puesto_id','im_clase_riesgo_id','em_cuenta_bancaria_id');

        $columnas_extra = array();
        $columnas_extra['nom_nomina_total_percepcion_gravado'] =
            "IFNULL ((SELECT SUM(nom_par_percepcion.importe_gravado) 
            FROM  nom_par_percepcion WHERE nom_par_percepcion.nom_nomina_id = nom_nomina.id),0)";

        $columnas_extra['nom_nomina_total_percepcion_exento'] =
            "IFNULL ((SELECT SUM(nom_par_percepcion.importe_exento) 
            FROM  nom_par_percepcion WHERE nom_par_percepcion.nom_nomina_id = nom_nomina.id), 0)";

        $columnas_extra['nom_nomina_total_percepcion_total'] =
            "IFNULL($columnas_extra[nom_nomina_total_percepcion_gravado] + $columnas_extra[nom_nomina_total_percepcion_exento],0)";

        $columnas_extra['nom_nomina_total_otro_pago_gravado'] =
            "IFNULL ((SELECT SUM(nom_par_otro_pago.importe_gravado) 
            FROM  nom_par_otro_pago WHERE nom_par_otro_pago.nom_nomina_id = nom_nomina.id),0)";

        $columnas_extra['nom_nomina_total_otro_pago_exento'] =
            "IFNULL((SELECT SUM(nom_par_otro_pago.importe_exento) 
            FROM  nom_par_otro_pago WHERE nom_par_otro_pago.nom_nomina_id = nom_nomina.id),0)";

        $columnas_extra['nom_nomina_total_otro_pago_total'] =
            "IFNULL($columnas_extra[nom_nomina_total_otro_pago_exento] + $columnas_extra[nom_nomina_total_otro_pago_gravado],0)";

        $columnas_extra['nom_nomina_total_deduccion_gravado'] =
            "IFNULL ((SELECT SUM(nom_par_deduccion.importe_gravado) 
            FROM  nom_par_deduccion WHERE nom_par_deduccion.nom_nomina_id = nom_nomina.id),0)";

        $columnas_extra['nom_nomina_total_deduccion_exento'] =
            "IFNULL ((SELECT SUM(nom_par_deduccion.importe_exento) 
            FROM  nom_par_deduccion WHERE nom_par_deduccion.nom_nomina_id = nom_nomina.id), 0)";

        $columnas_extra['nom_nomina_total_deduccion_total'] =
            "IFNULL($columnas_extra[nom_nomina_total_deduccion_exento] + $columnas_extra[nom_nomina_total_deduccion_gravado] ,0)";


        $columnas_extra['nom_nomina_total'] =
            "IFNULL($columnas_extra[nom_nomina_total_percepcion_total] + $columnas_extra[nom_nomina_total_otro_pago_total]- $columnas_extra[nom_nomina_total_deduccion_total],0)";

        $columnas_extra['nom_nomina_total_cuota'] =
            "IFNULL ((SELECT SUM(nom_concepto_imss.monto) 
            FROM  nom_concepto_imss WHERE nom_concepto_imss.nom_nomina_id = nom_nomina.id),0)";

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra);

        $this->NAMESPACE = __NAMESPACE__;
    }

    private function ajusta_otro_pago_sub_base(int $nom_nomina_id): array|stdClass
    {
        $r_nom_par_otro_pago = new stdClass();
        $existe_otro_pago_subsidio = $this->existe_otro_pago_subsidio(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion isr',data:  $existe_otro_pago_subsidio);
        }
        if(!$existe_otro_pago_subsidio){

            $r_nom_par_otro_pago = ($this->inserta_otro_pago_sub_base(nom_nomina_id: $nom_nomina_id));
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al dar de alta otro pago',data:  $r_nom_par_otro_pago);
            }

        }

        return $r_nom_par_otro_pago;
    }

    /**
     * @param int $em_empleado_id
     * @param int $nom_nomina_id
     * @return array
     */
    private function acciones_anticipo(int $em_empleado_id, int $nom_nomina_id): array
    {


        $anticipos = (new em_anticipo($this->link))->get_anticipos_empleado(em_empleado_id: $em_empleado_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los anticipos', data: $anticipos);
        }


        $abonos_aplicados = $this->inserta_deducciones_abonos_con_saldo(anticipos: $anticipos , nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dat de alta deduccion', data: $abonos_aplicados);
        }


        return $abonos_aplicados;
    }

    public function calcula_dia_festivo_laborado(int $dias_festivos_laborados, array $nom_par_percepcion,float $salario_diario): array
    {
        if($dias_festivos_laborados <= 0){
            return $this->error->error(mensaje: 'Error dias de prima no puede ser menor o igual a 0',
                data: $dias_festivos_laborados);
        }
        if($salario_diario <= 0){
            return $this->error->error(mensaje: 'Error salario_diario no puede ser menor o igual a 0',
                data: $salario_diario);
        }

        $im_uma = (new im_uma($this->link))->get_uma(fecha: date('Y-m-d'));
        if(errores::$error){
            return $this->error->error('Error al obtener registros de UMA', $im_uma);
        }
        if($im_uma->n_registros <= 0){
            return $this->error->error('Error no exsite registro de UMA', $im_uma);
        }
        if(!isset($im_uma->registros[0]['im_uma_monto'])){
            return $this->error->error('Error el uma no tiene monto asignado', $im_uma);
        }
        if(is_null($im_uma->registros[0]['im_uma_monto'])){
            return $this->error->error('Error el uma no tiene monto asignado', $im_uma);
        }

        $monto_uma = $im_uma->registros[0]['im_uma_monto'];

        $uma_5 = round($monto_uma * 5, 2);
        $monto_dfl = round($salario_diario * $dias_festivos_laborados,2);

        $nom_par_percepcion['importe_exento'] = round($monto_dfl,2);
        $nom_par_percepcion['importe_gravado'] = round($monto_dfl,2);

        if((float)$uma_5 < (float)$monto_dfl){
            $res = $monto_dfl - $uma_5;
            $nom_par_percepcion['importe_gravado'] = round($res + $monto_dfl,2);
            $nom_par_percepcion['importe_exento'] = round($monto_dfl,2);
        }

        return $nom_par_percepcion;
    }

    public function calcula_prima_dominical(int $dias_prima, array $nom_par_percepcion,float $salario_diario): array
    {
        if($dias_prima <= 0){
            return $this->error->error(mensaje: 'Error dias de prima no puede ser menor o igual a 0',
                data: $dias_prima);
        }
        if($salario_diario <= 0){
            return $this->error->error(mensaje: 'Error salario_diario no puede ser menor o igual a 0',
                data: $salario_diario);
        }

        $im_uma = (new im_uma($this->link))->get_uma(fecha: date('Y-m-d'));
        if(errores::$error){
            return $this->error->error('Error al obtener registros de UMA', $im_uma);
        }
        if($im_uma->n_registros <= 0){
            return $this->error->error('Error no exsite registro de UMA', $im_uma);
        }
        if(!isset($im_uma->registros[0]['im_uma_monto'])){
            return $this->error->error('Error el uma no tiene monto asignado', $im_uma);
        }
        if(is_null($im_uma->registros[0]['im_uma_monto'])){
            return $this->error->error('Error el uma no tiene monto asignado', $im_uma);
        }

        $monto_uma = $im_uma->registros[0]['im_uma_monto'];

        $monto_base = round($salario_diario * 0.25, 4);
        $prima_dominical = round($monto_base * $dias_prima,2);

        $nom_par_percepcion['importe_exento'] = round($prima_dominical,2);

        if((float)$monto_uma < (float)$prima_dominical){
            $res = $prima_dominical - $monto_uma;
            $nom_par_percepcion['importe_gravado'] = round($res,2);
            $nom_par_percepcion['importe_exento'] = round($monto_uma,2);
        }

        return $nom_par_percepcion;
    }

    public function calcula_vacaciones(int $dias_vacaciones, array $nom_par_percepcion,float $salario_diario): array
    {
        if($dias_vacaciones <= 0){
            return $this->error->error(mensaje: 'Error dias de prima no puede ser menor o igual a 0',
                data: $dias_vacaciones);
        }
        if($salario_diario <= 0){
            return $this->error->error(mensaje: 'Error salario_diario no puede ser menor o igual a 0',
                data: $salario_diario);
        }

        $monto_base = round($salario_diario * $dias_vacaciones, 2);

        $nom_par_percepcion['importe_gravado'] = round($monto_base,2);

        return $nom_par_percepcion;
    }

    public function calcula_prima_vacacional(int $dias_prima, array $nom_par_percepcion,float $salario_diario): array
    {
        if($dias_prima <= 0){
            return $this->error->error(mensaje: 'Error dias de prima no puede ser menor o igual a 0',
                data: $dias_prima);
        }
        if($salario_diario <= 0){
            return $this->error->error(mensaje: 'Error salario_diario no puede ser menor o igual a 0',
                data: $salario_diario);
        }
        $monto_base = round($salario_diario * 0.25, 4);

        $nom_par_percepcion['importe_gravado'] = round($monto_base * $dias_prima,2);

        return $nom_par_percepcion;
    }


    public function calcula_septimo_dia(int $dias_trabajados_reales, int $dias_septimo_dia,
                                        float $salario_diario): float|array
    {
        if($dias_trabajados_reales <= 0){
            return $this->error->error(mensaje: 'Error dias_trabajados_reales no puede ser menor o igual a 0',
                data: $dias_trabajados_reales);
        }
        if($dias_septimo_dia <= 0){
            return $this->error->error(mensaje: 'Error dias_septimo_dia no puede ser menor o igual a 0',
                data: $dias_septimo_dia);
        }
        if($salario_diario <= 0){
            return $this->error->error(mensaje: 'Error salario_diario no puede ser menor o igual a 0',
                data: $salario_diario);
        }
        $monto_base = round($salario_diario / $dias_septimo_dia, 4);
        return round($monto_base * $dias_trabajados_reales,2);
    }

    /**
     * @return array|stdClass
     */
    public function alta_bd(): array|stdClass
    {
        if(!isset($this->registro['cat_sat_tipo_contrato_nom_id'])){
            $this->registro['cat_sat_tipo_contrato_nom_id'] = 1;
        }

        $registros = $this->genera_registros_alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros', data: $registros);
        }

        $dias = $this->calculo_dias_pagados(nom_conf_empleado: $registros['nom_conf_empleado']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular los dias pagados', data: $dias);
        }

        $registros_factura = $this->genera_registro_factura(registros: $registros['fc_csd'],
            empleado_sucursal: $registros['nom_rel_empleado_sucursal'],cat_sat: $registros['nom_conf_empleado'],
            im_registro_patronal: $registros['im_registro_patronal']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de factura', data: $registros_factura);
        }

        $r_alta_factura = $this->inserta_factura(registro: $registros_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la factura', data: $r_alta_factura);
        }

        $registros_cfd_partida = $this->genera_registro_partida(fc_factura: $r_alta_factura,em_empleado: $registros['em_empleado'],
            conf_empleado: $registros['nom_conf_empleado']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de cfd partida', data: $registros_cfd_partida);
        }

        $r_alta_cfd_partida  = $this->inserta_partida(registro: $registros_cfd_partida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfd partida', data: $r_alta_cfd_partida);
        }

        $this->registro = $this->limpia_campos(registro: $this->registro,
            campos_limpiar: array('folio', 'fecha', 'descuento','nom_conf_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $this->registro);
        }

        $this->registro = $this->genera_registro_nomina(registros: $registros, fc_factura: $r_alta_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de nomina', data: $this->registro);
        }

        $r_alta_bd = parent::alta_bd(); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar nomina', data: $r_alta_bd);
        }

        $modelo = new nom_percepcion(link: $this->link);

        $r_nom_percepcion = $modelo->registro_estado_subsidio();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_percepcion);
        }

        $id_nom_percepcion = $modelo->id_registro_estado_subsidio($r_nom_percepcion);
        if (errores::$error || $id_nom_percepcion === -1) {
            return $this->error->error(mensaje: 'Error no existe una percepcion activa',data:  $id_nom_percepcion);
        }

        if($registros_cfd_partida['valor_unitario'] > 0) {
            $nom_par_percepcion_ins = array();
            $nom_par_percepcion_ins['nom_nomina_id'] = $r_alta_bd->registro_id;
            $nom_par_percepcion_ins['nom_percepcion_id'] = $id_nom_percepcion;
            $nom_par_percepcion_ins['importe_gravado'] = $registros_cfd_partida['valor_unitario'] * $registros_cfd_partida['cantidad'];

            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_ins);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
            }
        }

        $percepciones = $this->insertar_percepciones_configuracion(dias: $dias,
            nom_conf_nomina_id: $registros['nom_conf_empleado']->nom_conf_nomina_id,nom_nomina_id: $r_alta_bd->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar percepciones de configuracion', data: $percepciones);
        }

        if($this->registro['num_dias_pagados'] > 0) {
            $conceptos = (new nom_tipo_concepto_imss($this->link))->registros_activos();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registros de tipos de conceptos', data: $conceptos);
            }

            $calcula_cuota_obrero_patronal = new calcula_cuota_obrero_patronal();
            $calculos = $calcula_cuota_obrero_patronal->cuota_obrero_patronal(
                porc_riesgo_trabajo: $registros['im_registro_patronal']->im_clase_riesgo_factor,
                fecha: $this->registro['fecha_final_pago'],
                n_dias: $this->registro['num_dias_pagados'],
                sbc: $registros['em_empleado']->em_empleado_salario_diario_integrado, link: $this->link);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar calculos', data: $calculos);
            }

            $r_conceptos = $this->inserta_conceptos(conceptos: $conceptos, cuotas: $calcula_cuota_obrero_patronal->cuotas,
                nom_nomina_id: $r_alta_bd->registro_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error insertar conceptos', data: $r_conceptos);
            }
        }

        if($registros['nom_conf_empleado']->nom_conf_nomina_aplica_septimo_dia === 'activo'
            && $dias->dias_septimo_dia > 0 && $dias->dias_pagados_reales > 0){
            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_septimo_dia();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
            }

            $nom_par_percepcion_sep = array();
            $nom_par_percepcion_sep['nom_nomina_id'] = $r_alta_bd->registro_id;
            $nom_par_percepcion_sep['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];

            $septimo_dia = $this->calcula_septimo_dia(dias_trabajados_reales: $dias->dias_pagados_reales,
                dias_septimo_dia: $dias->dias_septimo_dia,
                salario_diario: $registros['em_empleado']->em_empleado_salario_diario);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al calcular septimo dia', data: $septimo_dia);
            }
            $nom_par_percepcion_sep['importe_gravado'] = $septimo_dia;

            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_sep);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
            }
        }

        if($registros['nom_conf_empleado']->nom_conf_nomina_aplica_prima_dominical === 'activo' &&
            $dias->dias_prima_dominical > 0){
            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_prima_dominical();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
            }

            $nom_par_percepcion_pri= array();
            $nom_par_percepcion_pri['nom_nomina_id'] = $r_alta_bd->registro_id;
            $nom_par_percepcion_pri['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];

            $nom_par_percepcion_pri = $this->calcula_prima_dominical(dias_prima: $dias->dias_prima_dominical,
                nom_par_percepcion: $nom_par_percepcion_pri,
                salario_diario: $registros['em_empleado']->em_empleado_salario_diario);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al calcular septimo dia', data: $nom_par_percepcion_pri);
            }

            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(
                registro: $nom_par_percepcion_pri);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
            }
        }

        if($registros['nom_conf_empleado']->nom_conf_nomina_aplica_dia_festivo_laborado === 'activo'
            && $dias->dias_festivos_laborados > 0){
            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_dia_festivo_laborado();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
            }

            $nom_par_percepcion_dfl= array();
            $nom_par_percepcion_dfl['nom_nomina_id'] = $r_alta_bd->registro_id;
            $nom_par_percepcion_dfl['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];

            $nom_par_percepcion_dfl = $this->calcula_dia_festivo_laborado(
                dias_festivos_laborados: $dias->dias_festivos_laborados,
                nom_par_percepcion: $nom_par_percepcion_dfl,
                salario_diario: $registros['em_empleado']->em_empleado_salario_diario);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al calcular septimo dia', data: $nom_par_percepcion_dfl);
            }

            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(
                registro: $nom_par_percepcion_dfl);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
            }
        }

        if($dias->dias_vacaciones > 0){
            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_vacaciones();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
            }

            $nom_par_percepcion_vac= array();
            $nom_par_percepcion_vac['nom_nomina_id'] = $r_alta_bd->registro_id;
            $nom_par_percepcion_vac['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];

            $nom_par_percepcion_vac = $this->calcula_vacaciones(dias_vacaciones: $dias->dias_vacaciones,
                nom_par_percepcion: $nom_par_percepcion_vac,
                salario_diario: $registros['em_empleado']->em_empleado_salario_diario);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al calcular septimo dia', data: $nom_par_percepcion_vac);
            }

            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(
                registro: $nom_par_percepcion_vac);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
            }
        }

        $abonos_aplicados = $this->acciones_anticipo(em_empleado_id: $this->registro['em_empleado_id'],nom_nomina_id: $r_alta_bd->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al ejecutar acciones de anticipo', data: $abonos_aplicados);
        }

        return $r_alta_bd;
    }

    /**
     * @param int $nom_nomina_id Nomina en proceso
     * @return bool|array
     */
    public function aplica_imss(int $nom_nomina_id): bool|array
    {
        $partidas = $this->partidas(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener partidas', data: $partidas);
        }

        $aplica_imss = $this->aplica_imss_base(partidas: $partidas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss);
        }


        return $aplica_imss;

    }

    public function recalcula_neto(array $registro, int $registro_id){
        $r_nom_nomina = $this->registro(registro_id: $registro_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener regsitro de nomina', data: $r_nom_nomina);
        }
        $nomina_bruto = 0;
        if((float)$registro['neto'] !== (float)$r_nom_nomina['nom_nomina_total']) {
            $nomina_bruto = (new calcula_nomina())->nomina_neto(
                cat_sat_periodicidad_pago_nom_id: $r_nom_nomina['cat_sat_periodicidad_pago_nom_id'],
                em_salario_diario: $r_nom_nomina['em_empleado_salario_diario'],
                em_empleado_salario_diario_integrado: $r_nom_nomina['em_empleado_salario_diario_integrado'],
                link: $this->link, nom_nomina_fecha_final_pago: $r_nom_nomina['nom_nomina_fecha_final_pago'],
                nom_nomina_num_dias_pagados: $r_nom_nomina['nom_nomina_num_dias_pagados'], total_neto: $registro['neto']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener neto', data: $nomina_bruto);
            }

            //if ((float)$nomina_bruto > (float)$r_nom_nomina['nom_nomina_total']) {

                $modelo = new nom_percepcion(link: $this->link);

                $r_nom_percepcion = $modelo->registro_estado_subsidio();
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_percepcion);
                }

                $id_nom_percepcion = $modelo->id_registro_estado_subsidio($r_nom_percepcion);
                if (errores::$error || $id_nom_percepcion === -1) {
                    return $this->error->error(mensaje: 'Error no existe una percepcion activa',data:  $id_nom_percepcion);
                }

                $filtro['nom_nomina.id'] = $r_nom_nomina['nom_nomina_id'];
                $filtro['nom_percepcion.id'] = $id_nom_percepcion;
                $nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener neto', data: $nomina_bruto);
                }

                $registro_per['importe_gravado'] = $nomina_bruto;
                $r_registro = (new nom_par_percepcion($this->link))->modifica_bd(registro:$registro_per,
                    id:$nom_par_percepcion->registros[0]['nom_par_percepcion_id']);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al ajustar percepcion', data: $r_registro);
                }

        }

        return $nomina_bruto;
    }

    /**
     * @param stdClass $partidas Partidas de nomina
     * @return bool|array
     */
    private function aplica_imss_base(stdClass $partidas): bool|array
    {
        $aplica_imss = $this->aplica_imss_percepcion(obj: 'percepciones', partidas: $partidas, tabla: 'nom_percepcion');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss);
        }

        if(!$aplica_imss) {

            $aplica_imss = $this->aplica_imss_percepcion(obj: 'otros_pagos', partidas: $partidas, tabla: 'nom_otro_pago');
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss);
            }

        }
        return $aplica_imss;
    }

    private function aplica_imss_bool(bool $es_imss_activo, bool $existe_key_imss): bool
    {
        return $existe_key_imss && $es_imss_activo;
    }

    /**
     * @param array $partida Partida a verificar
     * @param string $tabla
     * @return array|stdClass
     */
    private function aplica_imss_init(array $partida, string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        $existe_key_imss = $this->existe_key_imss(partida: $partida, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key imss', data: $existe_key_imss);
        }
        $es_imss_activo = $this->es_imss_activo(partida: $partida, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key imss', data: $es_imss_activo);
        }

        $data = new stdClass();
        $data->existe_key_imss = $existe_key_imss;
        $data->es_imss_activo = $es_imss_activo;

        return $data;
    }

    /**
     * @param string $obj Nombre del objeto a verificar
     * @param stdClass $partidas
     * @param string $tabla
     * @return bool|array
     */
    private function aplica_imss_percepcion(string $obj,stdClass $partidas, string $tabla): bool|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        $aplica_imss = false;
        foreach ($partidas->$obj as $partida){

            $aplica_imss_bool = $this->aplica_imss_val(partida: $partida, tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss_bool);
            }

            if($aplica_imss_bool){
                $aplica_imss = true;
                break;
            }
        }
        return $aplica_imss;
    }

    /**
     * @param array $partida Partida a verificar
     * @param string $tabla
     * @return bool|array
     */
    private function aplica_imss_val(array $partida, string $tabla): bool|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        $init = $this->aplica_imss_init(partida: $partida, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key imss', data: $init);
        }
        $aplica_imss_bool = $this->aplica_imss_bool(
            es_imss_activo:$init->es_imss_activo, existe_key_imss: $init->existe_key_imss);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss_bool);
        }

        return $aplica_imss_bool;
    }

    /**
     * Verifica si aplica subsidio percepcion
     * @param int $nom_nomina_id Nomina a validar
     * @return bool|array
     * @version 0.220.6
     */
    public function aplica_subsidio_percepcion(int $nom_nomina_id): bool|array
    {
        if($nom_nomina_id <= 0){
            return $this->error->error(mensaje: 'Error nom_nomina_id es menor a 1', data: $nom_nomina_id);
        }
        $filtro['nom_percepcion.aplica_subsidio'] = 'activo';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $existe = (new nom_par_percepcion($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe registro', data: $existe);
        }
        return $existe;
    }

    /**
     * Asigna el valor de un campo al row
     * @param array $registro Registro en proceso
     * @param string $campo Campo nuevo a integrar
     * @param array $campos_asignar Campos previos cargados
     * @return array
     * @version 0.366.20
     */
    private function asigna_campo(array $registro, string $campo, array $campos_asignar): array
    {
        $campo = trim($campo);
        if($campo === ''){
            return $this->error->error(mensaje: 'Error campo vacio', data: $campo);
        }
        if (!isset($registro[$campo])) {
            $valor_generado = $this->genera_valor_campo(campos_asignar: $campos_asignar);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar el campo', data: $valor_generado);
            }
            $registro[$campo] = $valor_generado;
        }
        return $registro;
    }

    private function asigna_codigo_nomina(array $registro): array
    {
        if (!isset($registro['codigo'])) {

            $codigo = $this->codigo_nomina(org_sucursal_id: $registro['org_sucursal_id'],
                registro: $registro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar codigo', data: $codigo);
            }

            $registro['codigo'] = $codigo;
        }
        return $registro;
    }

    private function asigna_descripcion_nomina(array $registro): array
    {
        if (!isset($registro['descripcion'])) {

            $descripcion = $this->descripcion_nomina(em_empleado_id: $registro['em_empleado_id'],
                registro: $registro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar descripcion', data: $descripcion);
            }
            $registro['descripcion'] = $descripcion;
        }
        return $registro;
    }


    public function calculo_dias_pagados(stdClass $nom_conf_empleado):stdClass|array{

        $keys = array('nom_conf_nomina_aplica_septimo_dia','nom_conf_nomina_aplica_prima_dominical',
            'nom_conf_nomina_aplica_dia_festivo_laborado');
        $valida = $this->validacion->valida_statuses(keys: $keys, registro: $nom_conf_empleado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nom_conf_empleado', data: $valida);
        }

        $dias = new stdClass();
        $dias_vacaciones = (new nom_incidencia($this->link))->total_dias_vacaciones(
            em_empleado_id: $this->registro['em_empleado_id'],nom_periodo_id: $this->registro['nom_periodo_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los dias de incidencia', data: $dias_vacaciones);
        }
        $dias->dias_vacaciones = $dias_vacaciones;

        $dias->dias_septimo_dia = 0;
        $dias->dias_pagados_periodo = $this->registro['num_dias_pagados'];
        if($nom_conf_empleado->nom_conf_nomina_aplica_septimo_dia === 'activo'){
            $this->registro['num_dias_pagados'] -= 1;
            $dias->dias_septimo_dia = $this->registro['num_dias_pagados'];
        }

        if($nom_conf_empleado->nom_conf_nomina_aplica_prima_dominical === 'activo'){
            $dias_incidencia = (new nom_incidencia($this->link))->total_dias_prima_dominical(
                em_empleado_id: $this->registro['em_empleado_id'],nom_periodo_id: $this->registro['nom_periodo_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener los dias de incidencia', data: $dias_incidencia);
            }
            $dias->dias_prima_dominical = $dias_incidencia;
        }

        if($nom_conf_empleado->nom_conf_nomina_aplica_dia_festivo_laborado === 'activo'){
            $dias_incidencia = (new nom_incidencia($this->link))->total_dias_aplica_dia_festivo_laborado(
                em_empleado_id: $this->registro['em_empleado_id'],nom_periodo_id: $this->registro['nom_periodo_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener los dias de incidencia', data: $dias_incidencia);
            }
            $dias->dias_festivos_laborados = $dias_incidencia;
        }

        $dias_incidencia = (new nom_incidencia($this->link))->total_dias_incidencias_n_dias(
            em_empleado_id: $this->registro['em_empleado_id'],nom_periodo_id: $this->registro['nom_periodo_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los dias de incidencia', data: $dias_incidencia);
        }

        $this->registro['num_dias_pagados'] -= $dias_incidencia;
        $dias->dias_pagados_periodo -= $dias_incidencia;
        $dias->dias_pagados_reales = $this->registro['num_dias_pagados'];

        return $dias;
    }

    public function calcula_monto_abono(array $anticipo,int $nom_nomina_id):float|array{
        $keys = array('em_anticipo_saldo');
        $valida = $this->validacion->valida_double_mayores_0(keys: $keys, registro: $anticipo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar $anticipo', data: $valida);
        }

        $keys = array('em_metodo_calculo_descripcion');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $anticipo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar $anticipo', data: $valida);
        }

        $descuento = round($anticipo['em_tipo_descuento_monto'],2);

        if($anticipo['em_metodo_calculo_descripcion'] === "porcentaje_monto_bruto"){

            $total_bruto = (new nom_nomina($this->link))->total_ingreso_bruto(nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener $total_bruto', data: $total_bruto);
            }
            $descuento =  round($total_bruto,2) * round($anticipo['em_tipo_descuento_monto'],2);
            $descuento =  round($descuento / 100,2);
        }

        $saldo =  round($anticipo['em_anticipo_saldo'],2) ;
        if($descuento > $saldo){
            $descuento = $saldo;
        }
        return $descuento;
    }

    private function codigo_nomina(int $org_sucursal_id, array $registro): array|string
    {

        $org_sucursal = (new org_sucursal($this->link))->registro(registro_id: $org_sucursal_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sucursal', data: $org_sucursal);
        }

        $codigo = $this->genera_codigo_nomina(org_sucursal: $org_sucursal, registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar codigo', data: $codigo);
        }
        return $codigo;
    }

    private function deduccion_isr(int $nom_nomina_id){

        $existe_deduccion_isr = $this->existe_deduccion_isr(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion isr',data:  $existe_deduccion_isr);
        }
        if(!$existe_deduccion_isr){
            return $this->error->error(mensaje: 'Error no existe la deduccion ISR',data:  $existe_deduccion_isr);
        }

        $filtro['nom_deduccion.es_isr']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $r_nom_par_deduccion);
        }
        if($r_nom_par_deduccion->n_registros > 1){
            return $this->error->error(mensaje: 'Error solo puede existir una deduccion de tipo ISR',
                data:  $r_nom_par_deduccion);
        }
        return $r_nom_par_deduccion->registros[0];


    }

    public function deduccion_isr_id(int $nom_nomina_id): array|int
    {

        $deduccion_isr = $this->deduccion_isr(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $deduccion_isr);
        }
        return (int)$deduccion_isr['nom_par_deduccion_id'];

    }

    /**
     * Obtiene todas las deducciones de una nomina
     * @param int $nom_nomina_id Identificador de nomina
     * @return array
     * @version 0.146.6
     */
    public function deducciones(int $nom_nomina_id): array
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $r_nom_par_deduccion);
        }
        return $r_nom_par_deduccion->registros;
    }

    private function del_data_modelo(stdClass $data): array
    {
        $dels_model = array();
        foreach ($data as $name_model=>$datas){
            $dels = $this->del_partidas(datas:$datas,name_model:  $name_model);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar datos', data: $dels);
            }
            $dels_model[] = $dels;
        }
        return $dels_model;
    }

    private function del_partida(string $name_model, array $row): array
    {
        $modelo = $this->genera_modelo(modelo: $name_model);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar modelo', data: $modelo);
        }
        $key_id = $name_model.'_id';
        $del = $modelo->elimina_bd(id: $row[$key_id]);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar registro', data: $del);
        }
        return $del;
    }

    private function del_partidas(array $datas, string $name_model): array
    {
        $dels = array();
        foreach ($datas as $row){
            $del = $this->del_partida(name_model: $name_model,row: $row);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar datos', data: $del);
            }
            $dels[] = $del;
        }
        return $dels;
    }

    private function descripcion_nomina(int $em_empleado_id, array $registro): array|string
    {
        $em_empleado = (new em_empleado($this->link))->registro(registro_id: $em_empleado_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener empleado', data: $em_empleado);
        }

        $descripcion = $this->genera_descripcion(em_empleado: $em_empleado, registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar descripcion', data: $descripcion);
        }
        return $descripcion;
    }

    public function elimina_bd(int $id): array|stdClass
    {
        $r_mom_momina = $this->registro(registro_id:  $id,columnas: array("nom_nomina_fc_factura_id"));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros de la nomina', data: $r_mom_momina);
        }

        $dels = $this->elimina_partidas(nom_nomina_id: $id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar datos', data: $dels);
        }

        $r_elimina_bd = parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar nomina', data: $r_elimina_bd);
        }

        $elimina_Factura = $this->elimina_factura(factura_id: $r_mom_momina['nom_nomina_fc_factura_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al eliminar la factura ligada a la monima', data: $elimina_Factura);
        }

        return $r_elimina_bd;
    }

    private function elimina_factura(int $factura_id) : array|stdClass{
        $elimina_bd = (new fc_factura(link: $this->link))->elimina_bd($factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al eliminar factira', data: $elimina_bd);
        }
        return $elimina_bd;
    }

    private function elimina_partidas(int $nom_nomina_id): array
    {

        $dels = array();



        $percepciones = (new nom_par_percepcion($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }

        foreach ($percepciones as $percepcion){
            $del = (new nom_par_percepcion($this->link))->elimina_bd(id:$percepcion['nom_par_percepcion_id']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar percepcion', data: $del);
            }
            $dels[] = $del;
        }

        $otros_pagos = (new nom_par_otro_pago($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $otros_pagos);
        }

        foreach ($otros_pagos as $otro_pago){
            $del = (new nom_par_otro_pago($this->link))->elimina_bd(id:$otro_pago['nom_par_otro_pago_id']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar otro pago', data: $del);
            }
            $dels[] = $del;
        }

        $deducciones = (new nom_par_deduccion($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $deducciones);
        }

        foreach ($deducciones as $deduccion){
            $del = (new nom_par_deduccion($this->link))->elimina_bd(id:$deduccion['nom_par_deduccion_id']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deduccion', data: $del);
            }
            $dels[] = $del;
        }


        return $dels;
    }

    private function es_imss_activo(array $partida, string $tabla): bool
    {
        return $partida[$tabla.'_aplica_imss'] === 'activo';
    }

    /**
     * Valida si existe una deduccion de tipo ISR
     * @param int $nom_nomina_id Nomina a validar
     * @return bool|array
     * @version 0.224.6
     */
    private function existe_deduccion_isr(int $nom_nomina_id): bool|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0',data:  $nom_nomina_id);
        }
        $filtro['nom_deduccion.es_isr']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $existe = (new nom_par_deduccion($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $existe);
        }
        return $existe;
    }

    /**
     * Verifica si existe o no el key de aplicacion de imss
     * @param array $partida Partida a verificar
     * @param string $tabla Tabla nom_percepcion o nom_deduccion etc
     * @return bool|array
     * @version 0.157.6
     */
    private function existe_key_imss(array $partida, string $tabla): bool|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        return isset($partida[$tabla.'_aplica_imss']);
    }

    /**
     * Verifica si existe un pago cargado para subsidio
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return bool|array
     * @version 0.281.8
     */
    private function existe_otro_pago_subsidio(int $nom_nomina_id): bool|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0',data:  $nom_nomina_id);
        }
        $filtro['nom_otro_pago.es_subsidio']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $existe = (new nom_par_otro_pago($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $existe);
        }
        return $existe;
    }

    private function genera_codigo_nomina(stdClass $org_sucursal, array $registro): string
    {
        $serie = $org_sucursal->org_sucursal_serie;
        $folio = $registro['folio'];
        return $org_sucursal->org_sucursal_id . $serie . $folio;
    }

    private function genera_descripcion(stdClass $em_empleado, array $registro): string
    {
        return $em_empleado->em_empleado_id .
            $em_empleado->em_empleado_nombre .
            $em_empleado->em_empleado_ap .
            $em_empleado->em_empleado_am .
            $em_empleado->em_empleado_rfc;
    }

    private function genera_nom_par_otro_pago_ins(int $nom_nomina_id): array
    {
        $otro_pago_subsidio_id = (new nom_otro_pago($this->link))->nom_otro_pago_subsidio_id();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener pago subsidio',data:  $otro_pago_subsidio_id);
        }

        $nom_par_otro_pago_ins = $this->nom_par_otro_pago_ins_init(nom_nomina_id: $nom_nomina_id,
            otro_pago_subsidio_id:$otro_pago_subsidio_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar otro pago',data:  $nom_par_otro_pago_ins);
        }
        return $nom_par_otro_pago_ins;
    }

    public function get_descuento_nomina(int $fc_factura_id): float
    {
        $descuento = (new fc_factura($this->link))->get_factura_descuento( fc_factura_id: $fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el descuento  de la partida',
                data: $descuento);
        }
        return $descuento;
    }

    private function get_partidas(int $nom_nomina_id): array|stdClass
    {
        $percepciones = (new nom_par_percepcion($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }
        $otros_pagos = (new nom_par_otro_pago($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $otros_pagos);
        }
        $otros_deducciones = (new nom_par_deduccion($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $otros_deducciones);
        }

        $data = new stdClass();
        $data->nom_par_percepcion = $percepciones;
        $data->nom_par_otro_pago = $otros_pagos;
        $data->nom_par_deduccion = $otros_deducciones;
        return $data;
    }

    private function get_percepciones(int $nom_nomina_id): array
    {
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro:$filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina_partida_percepcion', data: $r_nom_par_percepcion);
        }
        return $r_nom_par_percepcion->registros_obj;
    }

    public function get_sub_total_nomina(int $fc_factura_id): float|array
    {
        $subtotal = (new fc_factura($this->link))->get_factura_sub_total( fc_factura_id: $fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el subtotal de la partida',
                data: $subtotal);
        }
        return $subtotal;
    }

    /**
     * Obtiene el empleado ligado a una sucursal
     * @param int $em_empleado_id Identificador del empleado
     * @return array
     * @version 0.296.10
     */
    private function get_sucursal_by_empleado(int $em_empleado_id): array
    {
        if($em_empleado_id<=0){
            return $this->error->error(mensaje: 'Error $em_empleado_id debe ser mayor a 0 ', data: $em_empleado_id);
        }
        $filtro['em_empleado.id'] = $em_empleado_id;
        $nom_rel_empleado_sucursal = (new nom_rel_empleado_sucursal($this->link))->filtro_and( filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado sucursal ',
                data: $nom_rel_empleado_sucursal);
        }
        if((int)$nom_rel_empleado_sucursal->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe sucursal relacionada con empleado',
                data: $nom_rel_empleado_sucursal);
        }
        if((int)$nom_rel_empleado_sucursal->n_registros > 1){
            return $this->error->error(mensaje: 'Error de integridad solo puede existir un empleado por sucursal',
                data: $nom_rel_empleado_sucursal);
        }
        return $nom_rel_empleado_sucursal->registros[0];
    }

    /**
     * Obtiene los elementos basicos necesarios para la insersion de una nomina
     * @return array
     * @version 0.386.21
     */
    private function genera_registros_alta_bd(): array
    {
        $keys = array('im_registro_patronal_id','em_empleado_id','nom_conf_empleado_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $this->registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $im_registro_patronal = $this->registro_por_id(entidad: new im_registro_patronal(link: $this->link),
            id:  $this->registro['im_registro_patronal_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de registro patronal',
                data: $im_registro_patronal);
        }


        $fc_csd_id = $this->registro_por_id(entidad: new fc_csd($this->link),
            id: $im_registro_patronal->im_registro_patronal_fc_csd_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de fcd', data: $fc_csd_id);
        }

        $em_empleado = $this->registro_por_id(entidad: new em_empleado($this->link),
            id: $this->registro['em_empleado_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado ', data: $em_empleado);
        }

        $nom_conf_empleado = $this->registro_por_id(entidad: new nom_conf_empleado($this->link),
            id: $this->registro['nom_conf_empleado_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de conf factura',
                data: $nom_conf_empleado);
        }

        $nom_rel_empleado_sucursal = $this->get_sucursal_by_empleado(em_empleado_id: $this->registro['em_empleado_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sucursal de empleado para cfdi',
                data: $nom_rel_empleado_sucursal);
        }

        return array('im_registro_patronal' => $im_registro_patronal, 'em_empleado' => $em_empleado,
            'fc_csd' => $fc_csd_id, 'nom_rel_empleado_sucursal' => $nom_rel_empleado_sucursal,
            'nom_conf_empleado' => $nom_conf_empleado);
    }

    private function genera_registro_factura(mixed $registros, mixed $empleado_sucursal, mixed $cat_sat,
                                             stdClass $im_registro_patronal): array
    {
        $keys = array('folio','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $keys = array('com_sucursal_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $empleado_sucursal);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $folio = $this->registro['folio'];
        $serie = $registros->fc_csd_serie;
        $fecha = $this->registro['fecha'];
        $fc_csd_id = $registros->fc_csd_id;
        $com_sucursal_id = $empleado_sucursal['com_sucursal_id'];
        $cat_sat_forma_pago_id = $cat_sat->nom_conf_factura_cat_sat_forma_pago_id;
        $cat_sat_metodo_pago_id = $cat_sat->nom_conf_factura_cat_sat_metodo_pago_id;
        $cat_sat_moneda_id = $cat_sat->nom_conf_factura_cat_sat_moneda_id;
        $com_tipo_cambio_id = $cat_sat->nom_conf_factura_com_tipo_cambio_id;
        $cat_sat_uso_cfdi_id = $cat_sat->nom_conf_factura_cat_sat_uso_cfdi_id;
        $cat_sat_tipo_de_comprobante_id = $cat_sat->nom_conf_factura_cat_sat_tipo_de_comprobante_id;
        $dp_calle_pertenece_id = $im_registro_patronal->dp_calle_pertenece_id;

        return array('folio' => $folio, 'serie' => $serie, 'fecha' => $fecha, 'fc_csd_id' => $fc_csd_id,
            'com_sucursal_id' => $com_sucursal_id, 'cat_sat_forma_pago_id' => $cat_sat_forma_pago_id,
            'cat_sat_metodo_pago_id' => $cat_sat_metodo_pago_id, 'cat_sat_moneda_id' => $cat_sat_moneda_id,
            'com_tipo_cambio_id' => $com_tipo_cambio_id, 'dp_calle_pertenece_id'=>$dp_calle_pertenece_id,
            'cat_sat_uso_cfdi_id' => $cat_sat_uso_cfdi_id,
            'cat_sat_tipo_de_comprobante_id' => $cat_sat_tipo_de_comprobante_id);
    }

    private function genera_registro_partida(mixed $fc_factura, mixed $em_empleado, mixed $conf_empleado) : array{

        $keys = array('num_dias_pagados','descuento');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $keys = array('fc_factura_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $fc_factura->registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $keys = array('em_empleado_salario_diario');
        $valida = $this->validacion->valida_double_mayores_0(keys: $keys, registro: $em_empleado);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $codigo = $this->genera_valor_campo(array($fc_factura->registro['fc_factura_id'], $conf_empleado->em_empleado_id,
            $conf_empleado->em_cuenta_bancaria_id));
        $descripcion = $this->genera_valor_campo(array($fc_factura->registro['fc_factura_id'], $conf_empleado->em_empleado_id,
            $conf_empleado->em_cuenta_bancaria_id));
        $codigo_bis = $codigo;
        $descripcion_select = $descripcion;
        $alias = $codigo.$descripcion;
        $com_producto_id = $conf_empleado->nom_conf_factura_com_producto_id;
        $cantidad = 1;
        $valor_unitario= $this->registro['num_dias_pagados'] * $em_empleado->em_empleado_salario_diario;
        $descuento = $this->registro['descuento'];
        $fc_factura_id= $fc_factura->registro['fc_factura_id'];

        return array('codigo' => $codigo, 'descripcion' => $descripcion, 'descripcion_select' => $descripcion_select,
            'alias' => $alias, 'codigo_bis' => $codigo_bis,
            'com_producto_id' => $com_producto_id, 'cantidad' => $cantidad,
            'valor_unitario' => $valor_unitario, 'descuento' => $descuento,
            'fc_factura_id' => $fc_factura_id);
    }

    /**
     * @param mixed $registros
     * @param mixed $fc_factura
     * @return array
     */
    private function genera_registro_nomina(mixed $registros, mixed $fc_factura) : array{

        $asignar = array($registros['fc_csd']->org_sucursal_id,
            $fc_factura->registro['fc_factura_serie'], $fc_factura->registro['fc_factura_folio']);

        $registro = $this->asigna_campo(registro: $this->registro, campo: "codigo", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }
        $this->registro = $registro;

        $asignar = array($registros['em_empleado']->em_empleado_id, $registros['em_empleado']->em_empleado_nombre,
            $registros['em_empleado']->em_empleado_ap, $registros['em_empleado']->em_empleado_am,
            $registros['em_empleado']->em_empleado_rfc, $fc_factura->registro['fc_factura_codigo']);

        $registro = $this->asigna_campo(registro: $this->registro, campo: "descripcion", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $registro = $this->asigna_campo(registro: $this->registro, campo: "descripcion_select", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $asignar = array($fc_factura->registro['fc_factura_codigo'], $registros['em_empleado']->em_empleado_rfc);

        $registro = $this->asigna_campo(registro: $this->registro, campo: "alias", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $registro = $this->asigna_campo(registro: $this->registro, campo: "codigo_bis", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $this->registro['fc_factura_id'] = $fc_factura->registro_id;
        $this->registro['cat_sat_tipo_jornada_nom_id'] = $registros['em_empleado']->cat_sat_tipo_jornada_nom_id;
        $this->registro['dp_calle_pertenece_id'] = $registros['fc_csd']->dp_calle_pertenece_id;
        $this->registro['org_departamento_id'] = $registros['em_empleado']->org_departamento_id;
        $this->registro['org_puesto_id'] = $registros['em_empleado']->org_puesto_id;
        $this->registro['im_clase_riesgo_id'] = $registros['im_registro_patronal']->im_clase_riesgo_id;

        return $this->registro;
    }

    /**
     * Genera un registro para partida percepcion default
     * @param int $nom_nomina_id Nomina a identificar
     * @param array $percepcion Percepcion de configuracion
     * @return array|stdClass
     * @version 0.339.17
     */
    private function genera_registro_par_percepcion(int $nom_nomina_id, array $percepcion): array|stdClass
    {
        $keys = array('nom_percepcion_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro: $percepcion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al valida percepcion', data: $valida);
        }
        $keys = array('nom_conf_percepcion_importe_gravado','nom_conf_percepcion_importe_exento');
        $valida = $this->validacion->valida_double_mayores_igual_0(keys: $keys,registro: $percepcion);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al valida percepcion', data: $valida);
        }
        $nom_par_percepcion = array();
        $nom_par_percepcion['nom_nomina_id'] = $nom_nomina_id;
        $nom_par_percepcion['nom_percepcion_id'] = $percepcion['nom_percepcion_id'];
        $nom_par_percepcion['importe_gravado'] = $percepcion['nom_conf_percepcion_importe_gravado'];
        $nom_par_percepcion['importe_exento'] = $percepcion['nom_conf_percepcion_importe_exento'];

        return $nom_par_percepcion;
    }

    /**
     * Integra el valor de un campo
     * @param array $campos_asignar conjunto de campos a integrar
     * @return string
     * @version 0.364.20
     */
    private function genera_valor_campo(array $campos_asignar): string
    {
        return implode($campos_asignar);
    }

    public function get_salario_minimo(int $dp_cp_id, string $fecha ): float|array|stdClass
    {
        $filtro['dp_colonia_postal.dp_cp_id'] = $dp_cp_id;
        $filtro_especial[0][$fecha]['operador'] = '>=';
        $filtro_especial[0][$fecha]['valor'] = 'nom_nomina.fecha_inicial_pago';
        $filtro_especial[0][$fecha]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha]['operador'] = '<=';
        $filtro_especial[1][$fecha]['valor'] = 'nom_nomina.fecha_final_pago';
        $filtro_especial[1][$fecha]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha]['valor_es_campo'] = true;

        $order = array('em_empleado.salario_diario'=>'DESC');

        $salario_minimo = (new nom_nomina($this->link))->filtro_and(filtro: $filtro, filtro_especial: $filtro_especial, limit: 1, order: $order);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener el salario minimo', data: $salario_minimo);
        }

        if ($salario_minimo->n_registros === 0){
            return 0.0;
        }
        return $salario_minimo->registros[0]['em_empleado_salario_diario'];
    }

    public function inserta_conceptos(array $conceptos, stdClass $cuotas, int $nom_nomina_id): array
    {
        $r_conceptos = array();
        foreach($conceptos as $concepto){
            foreach ($cuotas as $campo => $cuota){
                if($concepto['nom_tipo_concepto_imss_alias'] === $campo){
                    $registro_concepto_imss = $this->maqueta_nom_comcepto(nom_nomina_id: $nom_nomina_id,
                        concepto: $concepto,monto: $cuota);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al maquetar registro',
                            data: $registro_concepto_imss);
                    }

                    $r_alta_nom_concepto_imss = (new nom_concepto_imss($this->link))->alta_registro(
                        registro: $registro_concepto_imss);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al generar insertar concepto',
                            data: $r_alta_nom_concepto_imss);
                    }
                    $r_conceptos[] = $r_alta_nom_concepto_imss;
                }
            }
        }

        return $r_conceptos;
    }

    private function inserta_deduccion_abono_con_saldo(array $anticipo, int $nom_nomina_id): array|stdClass
    {
        $alta_npd = new stdClass();
        $saldo = (new em_anticipo($this->link))->get_saldo_anticipo(em_anticipo_id: $anticipo['em_anticipo_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el saldo del anticipo', data: $saldo);
        }

        if ($saldo > 0.0) {
            $filtro['nom_conf_abono.em_tipo_anticipo_id'] = $anticipo['em_tipo_anticipo_id'];

            $conf_abono = (new nom_conf_abono($this->link))->filtro_and(filtro: $filtro, limit: 1);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener conf. abono', data: $conf_abono);
            }

            if ($conf_abono->n_registros > 0) {

                $alta_npd = (new nom_par_deduccion($this->link))->inserta_deduccion_anticipo(
                    anticipo: $anticipo, nom_nomina_id: $nom_nomina_id, nom_conf_abono: $conf_abono->registros[0]);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al dat de alta deduccion', data: $alta_npd);
                }

                $alta_em_abono_anticipo = $this->inserta_em_abono_anticipo(anticipo: $anticipo, deduccion: $alta_npd,
                    nom_conf_abono: $conf_abono->registros[0],nom_nomina_id: $nom_nomina_id);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al dat de alta abono', data: $alta_em_abono_anticipo);
                }

                $alta_nrda = $this->inserta_nom_rel_deduccion_abono(deduccion: $alta_npd,
                    abono: $alta_em_abono_anticipo, nom_nomina_id: $nom_nomina_id);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al dat de alta abono', data: $alta_nrda);
                }
            }
        }
        return $alta_npd;
    }

    private function inserta_deducciones_abonos_con_saldo(stdClass $anticipos, int $nom_nomina_id): array
    {
        $abonos_aplicados = array();
        foreach ($anticipos->registros as $anticipo){

            $alta = $this->inserta_deduccion_abono_con_saldo(anticipo: $anticipo , nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dat de alta deduccion', data: $alta);
            }
            $abonos_aplicados[] = $alta;

        }
        return $abonos_aplicados;
    }

    public function inserta_em_abono_anticipo(array $anticipo, array|stdClass $deduccion, array $nom_conf_abono,
                                              int $nom_nomina_id): array|stdClass
    {
        $r_abono = $this->maquetar_em_abono_anticipo(anticipo: $anticipo, deduccion: $deduccion,
            nom_conf_abono : $nom_conf_abono, nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar abono', data: $r_abono);
        }

        $alta_em_abono_anticiopo = (new em_abono_anticipo($this->link))->alta_registro($r_abono);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta abono', data: $alta_em_abono_anticiopo);
        }
        return $alta_em_abono_anticiopo;
    }

    private function inserta_factura(array $registro): array|stdClass
    {
        $r_alta_factura = (new fc_factura($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la factura', data: $r_alta_factura);
        }
        return $r_alta_factura;
    }

    private function inserta_nom_rel_deduccion_abono(stdClass $deduccion, stdClass $abono, int $nom_nomina_id): array|stdClass
    {

        $keys = array('registro');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $deduccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar deduccion', data: $valida);
        }

        $r_rel_deduccion_abono = $this->maquetar_nom_rel_deduccion_abono(deduccion: $deduccion, abono:  $abono,
            nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar rel deduccion abono', data: $r_rel_deduccion_abono);
        }

        $alta_rel_deduccion_abono = (new nom_rel_deduccion_abono($this->link))->alta_registro($r_rel_deduccion_abono);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta rel deduccion abono',
                data: $alta_rel_deduccion_abono);
        }
        return $alta_rel_deduccion_abono;
    }

    private function inserta_otro_pago_sub_base(int $nom_nomina_id): array|stdClass
    {
        $nom_par_otro_pago_ins = $this->genera_nom_par_otro_pago_ins(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al maquetar otro pago',data:  $nom_par_otro_pago_ins);
        }

        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->alta_registro(registro: $nom_par_otro_pago_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta otro pago',data:  $r_nom_par_otro_pago);
        }
        return $r_nom_par_otro_pago;
    }

    private function inserta_partida(array $registro): array|stdClass
    {
        $r_alta_partida = (new fc_partida($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfd partida', data: $r_alta_partida);
        }
        return $r_alta_partida;
    }

    private function insertar_percepciones_configuracion(stdClass $dias, int $nom_conf_nomina_id,
                                                         int $nom_nomina_id) : array|stdClass
    {
        $percepciones = $this->obtener_percepciones_por_configuracion(nom_conf_nomina_id: $nom_conf_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones por configuracion de nomina', data: $percepciones);
        }

        if ($percepciones->n_registros > 0) {
            foreach ($percepciones->registros as $percepcion) {
                $registros_par_percepcion = $this->genera_registro_par_percepcion(nom_nomina_id: $nom_nomina_id,percepcion: $percepcion);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al generar registros de percepcion', data: $registros_par_percepcion);
                }

                if($percepcion['nom_percepcion_aplica_despensa'] === 'activo'){
                    $registros_par_percepcion['importe_exento'] = round($dias->dias_pagados_periodo *
                        $percepcion['nom_conf_percepcion_importe_exento'],2);
                }

                $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $registros_par_percepcion);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al insertar percepcion', data: $r_alta_nom_par_percepcion);
                }

            }
        }
        return $percepciones;
    }

    private function limpia_campos(array $registro, array $campos_limpiar): array
    {
        foreach ($campos_limpiar as $valor) {
            if (isset($registro[$valor])) {
                unset($registro[$valor]);
            }
        }
        return $registro;
    }

    public function maqueta_nom_comcepto(int $nom_nomina_id, array $concepto, float $monto){
        $registro_concepto_imss['nom_nomina_id'] = $nom_nomina_id;
        $registro_concepto_imss['nom_tipo_concepto_imss_id'] = $concepto['nom_tipo_concepto_imss_id'];
        $registro_concepto_imss['monto'] = $monto;
        $registro_concepto_imss['descripcion'] = $nom_nomina_id."-".
            $concepto['nom_tipo_concepto_imss_alias'];
        $registro_concepto_imss['descripcion_select'] = $registro_concepto_imss['descripcion'];
        $registro_concepto_imss['alias'] = $registro_concepto_imss['descripcion'];
        $registro_concepto_imss['codigo'] = $nom_nomina_id."-".
            $concepto['nom_tipo_concepto_imss_alias']."-".rand();
        $registro_concepto_imss['codigo_bis'] = $registro_concepto_imss['codigo'];

        return $registro_concepto_imss;
    }

    public function maqueta_registros_excel(int $nom_nomina_id){

        $registro = $this->registro(registro_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros de la nomina',
                data: $registro);
        }

        $fi = (new em_empleado($this->link))->obten_factor(em_empleado_id: $registro['em_empleado_id'],
            fecha_inicio_rel: $registro['em_empleado_fecha_inicio_rel_laboral']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener FI',
                data: $registro);
        }

        $subsidio = $this->total_otros_pagos_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de otros pagos',
                data: $registro);
        }

        $septimo_dia = $this->total_percepciones_septimo_dia_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de percepciones',
                data: $septimo_dia);
        }    
        
        $prima_dominical = $this->total_percepciones_prima_dominical_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener prima dominical',
                data: $prima_dominical);
        }
        
        $vacaciones = $this->total_percepciones_vacaciones_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener vacaciones',
                data: $vacaciones);
        }

        $despensa = $this->total_percepciones_despensa(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de percepciones',
                data: $registro);
        }

        $suma_percepcion =$this->total_percepciones_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de percepciones',
                data: $registro);
        }

        $suma_deduccion =$this->total_deducciones_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de deducciones',
                data: $registro);
        }

        $suma_base_gravable =$this->total_percepciones_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de percepciones',
                data: $registro);
        }

        $suma_base_gravable += $this->total_otros_pagos_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de otros pagos',
                data: $registro);
        }

        $retencion_isr = $this->total_deducciones_isr_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de deducciones',
                data: $registro);
        }

        $retencion_imss = $this->total_deducciones_imss_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de deducciones',
                data: $registro);
        }

        $dias_incapacidad = (new nom_incidencia($this->link))->get_incidencias_incapacidad(
            em_empleado_id: $registro['em_empleado_id'], nom_periodo_id: $registro['nom_periodo_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $dias_incapacidad);
        }

        $datos = array();
        $datos['id_rem'] = $registro['em_empleado_codigo'];
        $datos['nss'] = $registro['em_empleado_nss'];
        $datos['nombre_completo'] = $registro['em_empleado_nombre'].' ';
        $datos['nombre_completo'] .= $registro['em_empleado_ap'].' ';
        $datos['nombre_completo'] .= $registro['em_empleado_am'];
        $datos['dias_laborados'] = $registro['nom_nomina_num_dias_pagados'];
        $datos['dias_x_incapacidad'] = 0;
        $datos['sd'] = $registro['em_empleado_salario_diario'];
        $datos['fi'] = $fi;
        $datos['sdi'] = $registro['em_empleado_salario_diario_integrado'];
        $datos['sueldo'] = $registro['nom_nomina_num_dias_pagados'] * $registro['em_empleado_salario_diario'];
        $datos['subsidio'] = $subsidio;
        $datos['prima_dominical'] = $prima_dominical;
        $datos['vacaciones'] = $vacaciones;
        $datos['septimo_dia'] = $septimo_dia;
        $datos['compensacion'] = 0;
        $datos['despensa'] = $despensa; //
        $datos['otros_ingresos'] = 0;
        $datos['devolucion_infonavit'] = 0;
        $datos['prima_vacacional_gravado'] = 0;
        $datos['prima_vacacional_exento'] = 0;
        $datos['gratificacion_gravado'] = 0;
        $datos['gratificacion_exento'] = 0;
        $datos['aguinaldo_gravado'] = 0;
        $datos['aguinaldo_exento'] = 0;
        $datos['dias_festivos_gravado'] = 0;
        $datos['dias_festivos_exento'] = 0;
        $datos['descanso_laborado_gravado'] = 0;
        $datos['descanso_laborado_exento'] = 0;
        $datos['horas_extras_gravado'] = 0;
        $datos['horas_extras_exento'] = 0;
        $datos['ptu_gravado'] = 0;
        $datos['ptu_exento'] = 0;
        $datos['indemnizacion'] = 0;
        $datos['prima_antiguedad'] = 0;
        $datos['suma_percepcion'] = $suma_percepcion;
        $datos['base_gravable'] = $suma_base_gravable;
        $datos['retencion_isr'] = $retencion_isr;
        $datos['retencion_imss'] = $retencion_imss;
        $datos['infonavit'] = 0; //
        $datos['fonacot'] = 0; //
        $datos['pension_alimencia'] = 0; //
        $datos['otros_descuentos'] = 0; //
        $datos['descuento_comedor'] = 0; //
        $datos['descuento_p_personal'] = 0; //
        $datos['suma_deduccion'] = $suma_deduccion;
        $datos['neto_a_pagar'] = $suma_percepcion - $suma_deduccion;
        $datos['cuenta'] = $registro['em_cuenta_bancaria_num_cuenta'];
        $datos['clabe'] = $registro['em_cuenta_bancaria_clabe'];
        $datos['banco'] = $registro['bn_banco_descripcion'];

        if ($dias_incapacidad->n_registros > 0){
            $datos['dias_x_incapacidad'] = $dias_incapacidad->registros[0]['nom_incidencia_n_dias'];
        }

        return $datos;
    }

    private function maquetar_em_abono_anticipo(array $anticipo, array|stdClass $deduccion, array $nom_conf_abono,
                                                int $nom_nomina_id):array{
        $descuento =  $this->calcula_monto_abono(anticipo: $anticipo, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular monto abono', data: $descuento);
        }

        $datos = (new limpieza())->maqueta_row_abono_base(anticipo: $anticipo, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integra base row', data: $datos);
        }

        $datos['em_tipo_abono_anticipo_id'] = $nom_conf_abono['em_tipo_abono_anticipo_id'];
        $datos['em_anticipo_id'] = $anticipo['em_anticipo_id'];
        $datos['cat_sat_forma_pago_id'] = $deduccion->registro['fc_factura_cat_sat_forma_pago_id'];
        $datos['monto'] = $descuento;
        $datos['fecha'] = date('Y-m-d');

        return $datos;
    }

    private function maquetar_nom_rel_deduccion_abono(array|stdClass $deduccion, array|stdClass $abono, int $nom_nomina_id):array{
        $datos['descripcion'] = $deduccion->registro['nom_par_deduccion_descripcion'];
        $datos['descripcion'] .= $abono->registro['em_abono_anticipo_descripcion'];
        $datos['codigo'] = $deduccion->registro['nom_par_deduccion_codigo'];
        $datos['codigo'] .= $abono->registro['em_abono_anticipo_codigo'].$nom_nomina_id;
        $datos['descripcion_select'] = strtoupper($datos['descripcion']);
        $datos['codigo_bis'] = strtoupper($datos['codigo']);
        $datos['alias'] = $datos['codigo'].$datos['descripcion'];
        $datos['nom_par_deduccion_id'] = $deduccion->registro_id;
        $datos['em_abono_anticipo_id'] = $abono->registro_id;

        return $datos;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $nom_nomina = $this->registro_por_id($this, $id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros de la nomina',
                data: $nom_nomina);
        }
        $fc_factura = array('folio' => $registro['folio'], 'fecha' => $registro['fecha']);


        $r_modifica_factura = (new fc_factura($this->link))->modifica_bd(registro: $fc_factura,id:
            $nom_nomina->nom_nomina_fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de modificar la factura', data: $r_modifica_factura);
        }

        $registro = $this->limpia_campos(registro: $registro,
            campos_limpiar: array('folio', 'fecha', 'descuento','nom_conf_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $registro);
        }

        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al modificar la nomina', data: $r_modifica_bd);
        }

        return $r_modifica_bd;
    }

    private function nom_par_otro_pago_ins_init(int $nom_nomina_id, int $otro_pago_subsidio_id): array
    {
        $nom_par_otro_pago_ins = array();
        $nom_par_otro_pago_ins['nom_nomina_id'] = $nom_nomina_id;
        $nom_par_otro_pago_ins['nom_otro_pago_id'] = $otro_pago_subsidio_id;
        $nom_par_otro_pago_ins['importe_gravado'] = 0;
        $nom_par_otro_pago_ins['importe_exento'] = 0;

        return $nom_par_otro_pago_ins;
    }

    private function obtener_percepciones_por_configuracion(int $nom_conf_nomina_id): array |stdClass
    {
        $filtro['nom_conf_percepcion.nom_conf_nomina_id']  = $nom_conf_nomina_id;
        $nom_conf_percepcion = (new nom_conf_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nom_conf_percepcion',data:  $nom_conf_percepcion);
        }

        return $nom_conf_percepcion;
    }

    private function otro_pago_subsidio(int $nom_nomina_id){


        $r_nom_par_otro_pago = $this->ajusta_otro_pago_sub_base(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta otro pago',data:  $r_nom_par_otro_pago);
        }

        $filtro['nom_otro_pago.es_subsidio']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otro pago',data:  $r_nom_par_otro_pago);
        }
        if($r_nom_par_otro_pago->n_registros > 1){
            return $this->error->error(mensaje: 'Error solo puede existir un otro pago de tipo SUBSIDIO',
                data:  $r_nom_par_otro_pago);
        }
        return $r_nom_par_otro_pago->registros[0];


    }

    public function otro_pago_subsidio_id(int $nom_nomina_id): array|int
    {

        $otro_pago_subsidio = $this->otro_pago_subsidio(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otro pago',data:  $otro_pago_subsidio);
        }
        return (int)$otro_pago_subsidio['nom_par_otro_pago_id'];

    }

    /**
     * Obtiene otros pagos de nomina
     * @param int $nom_nomina_id Nomina en proceso
     * @return array
     * @version 0.152.6
     */
    public function otros_pagos(int $nom_nomina_id): array
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }

        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $r_nom_par_otro_pago);
        }
        return $r_nom_par_otro_pago->registros;
    }

    /**
     * Obtiene las partidas de una nomina completa
     * @param int $nom_nomina_id Nomina en proceso
     * @return array|stdClass
     * @version 0.153.6
     */
    public function partidas(int $nom_nomina_id): array|stdClass
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }
        $percepciones = $this->percepciones(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }
        $deducciones = $this->deducciones(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $deducciones);
        }
        $otros_pagos = $this->otros_pagos(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener $otros_pagos', data: $otros_pagos);
        }

        $data = new stdClass();
        $data->percepciones = $percepciones;
        $data->deducciones = $deducciones;
        $data->otros_pagos = $otros_pagos;

        return $data;

    }

    /**
     * Obtiene todas las percepciones de una nomina
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return array
     * @version 1.144.6
     */
    public function percepciones(int $nom_nomina_id): array
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }
        return $r_nom_par_percepcion->registros;
    }

    public function total_deducciones_isr_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_deduccion.es_isr']  = 'activo';
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $r_nom_par_deduccion);
        }

        $total = 0.0;

        if ($r_nom_par_deduccion->n_registros == 0){
            return $total;
        }

        foreach ($r_nom_par_deduccion->registros as $registro){
            $total += ($registro['nom_par_deduccion_importe_gravado'] + $registro['nom_par_deduccion_importe_exento']);
        }

        return round($total,2);
    }

    public function total_deducciones_imss_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_deduccion.es_imss']  = 'activo';
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $r_nom_par_deduccion);
        }

        $total = 0.0;

        if ($r_nom_par_deduccion->n_registros == 0){
            return $total;
        }

        foreach ($r_nom_par_deduccion->registros as $registro){
            $total += ($registro['nom_par_deduccion_importe_gravado'] + $registro['nom_par_deduccion_importe_exento']);
        }

        return round($total,2);
    }

    private function total_deducciones_exento(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_exento'] = 'nom_par_deduccion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $r_nom_par_deduccion);
        }

        return round($r_nom_par_deduccion['total_importe_exento'],2);

    }

    private function total_deducciones_gravado(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_deduccion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $r_nom_par_deduccion);
        }

        return round($r_nom_par_deduccion['total_importe_gravado'],2);

    }

    public function total_deducciones_monto(int $nom_nomina_id): float|array
    {

        $total_deducciones_gravado = $this->total_deducciones_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado ded', data: $total_deducciones_gravado);
        }
        $total_deducciones_exento = $this->total_deducciones_exento(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total exento ded', data: $total_deducciones_exento);
        }

        $total_deducciones = $total_deducciones_gravado + $total_deducciones_exento;
        return round($total_deducciones,2);
    }

    /**
     * Obtiene el total gravado de una nomina
     * @param int $nom_nomina_id Nomina a verificar
     * @return float|array
     * @version 0.67.1
     */
    public function total_gravado(int $nom_nomina_id): float|array
    {


        $total_percepciones_gravado = $this->total_percepciones_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_percepciones_gravado);
        }

        $filtro['nom_nomina.id'] = $nom_nomina_id;

        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_otro_pago.importe_gravado';
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $r_nom_par_otro_pago);
        }

        $total_percepciones = round($total_percepciones_gravado,2);
        $total_otros_pagos = round($r_nom_par_otro_pago['total_importe_gravado'],2);


        $total_gravado = $total_percepciones + $total_otros_pagos;

        return round($total_gravado, 2);

    }

    public function total_impuestos_retenidos_exento(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_exento'] = 'nom_par_deduccion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_deduccion.es_impuesto_retenido'] = 'activo';
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $r_nom_par_deduccion);
        }

        return round($r_nom_par_deduccion['total_importe_exento'],2);
    }

    public function total_impuestos_retenidos_gravado(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_deduccion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_deduccion.es_impuesto_retenido'] = 'activo';
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $r_nom_par_deduccion);
        }

        return round($r_nom_par_deduccion['total_importe_gravado'],2);
    }

    public function total_impuestos_retenidos_monto(int $nom_nomina_id): float|array
    {

        $total_deducciones_exento = $this->total_impuestos_retenidos_exento(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total exento', data: $total_deducciones_exento);
        }
        $total_deducciones_gravado = $this->total_impuestos_retenidos_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_deducciones_gravado);
        }

        $total_deducciones = $total_deducciones_exento + $total_deducciones_gravado;
        return round($total_deducciones,2);
    }

    public function total_ingreso_bruto(int $nom_nomina_id): float|array
    {
        $total_percepciones = $this->total_percepciones_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el total de percepciones', data: $total_percepciones);
        }

        $total_otros_pagos = $this->total_otros_pagos_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el total de otros pagos', data: $total_otros_pagos);
        }

        return round($total_percepciones + $total_otros_pagos,2);

    }

    public function total_otras_deducciones_exento(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_exento'] = 'nom_par_deduccion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_deduccion.es_otra_deduccion'] = 'activo';
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_deduccion);
        }

        return round($r_nom_par_deduccion['total_importe_exento'],2);
    }

    public function total_otras_deducciones_gravado(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_deduccion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_deduccion.es_otra_deduccion'] = 'activo';
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_deduccion);
        }

        return round($r_nom_par_deduccion['total_importe_gravado'],2);
    }

    public function total_otras_deducciones_monto(int $nom_nomina_id): float|array
    {

        $total_deducciones_gravado = $this->total_otras_deducciones_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_deducciones_gravado);
        }
        $total_deducciones_exento = $this->total_otras_deducciones_exento(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_deducciones_gravado);
        }

        $total_deducciones = $total_deducciones_exento + $total_deducciones_gravado;
        return round($total_deducciones,2);
    }

    public function total_otros_pagos_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_otro_pago.es_subsidio']  = 'activo';
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otro pago',data:  $r_nom_par_otro_pago);
        }

        $total = 0.0;

        if ($r_nom_par_otro_pago->n_registros == 0){
            return $total;
        }

        foreach ($r_nom_par_otro_pago->registros as $registro){
            $total += ($registro['nom_par_otro_pago_importe_gravado'] + $registro['nom_par_otro_pago_importe_exento']);
        }

        return round($total,2);
    }

    public function total_otros_pagos_exento(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_exento'] = 'nom_par_otro_pago.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $r_nom_par_otro_pago);
        }

        return round($r_nom_par_otro_pago['total_importe_exento'],2);

    }

    public function total_otros_pagos_gravado(int $nom_nomina_id): float|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }

        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_otro_pago.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_otro_pago);
        }

        return round($r_nom_par_otro_pago['total_importe_gravado'],2);

    }

    public function total_otros_pagos_monto(int $nom_nomina_id): float|array
    {

        $total_otros_pagos_gravado = $this->total_otros_pagos_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_otros_pagos_gravado);
        }
        $total_otros_pagos_exento = $this->total_otros_pagos_exento(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total exento', data: $total_otros_pagos_exento);
        }

        $total_otros_pagos = $total_otros_pagos_gravado + $total_otros_pagos_exento;
        return round($total_otros_pagos,2);
    }

    /**
     * Obtiene el total de percepciones exento
     * @param int $nom_nomina_id Nomina ID
     * @return float|array

     */
    public function total_percepciones_exento(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_exento'] = 'nom_par_percepcion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }

        return round($r_nom_par_percepcion['total_importe_exento'],2);

    }

    /**
     * Obtiene el total de percepciones gravado
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return float|array
     * @version 0.372.20
     */
    public function total_percepciones_gravado(int $nom_nomina_id): float|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }

        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_percepcion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }

        return round($r_nom_par_percepcion['total_importe_gravado'],2);

    }

    public function total_percepciones_monto(int $nom_nomina_id): float|array
    {

        $total_percepciones_gravado = $this->total_percepciones_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_percepciones_gravado);
        }
        $total_percepciones_exento = $this->total_percepciones_exento(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_percepciones_exento);
        }

        $total_percepciones = $total_percepciones_gravado + $total_percepciones_exento;
        return round($total_percepciones,2);
    }

    public function total_percepciones_septimo_dia_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_septimo_dia']  = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $total = 0.0;

        if ($r_nom_par_percepcion->n_registros == 0){
            return $total;
        }

        foreach ($r_nom_par_percepcion->registros as $registro){
            $total += ($registro['nom_par_percepcion_importe_gravado'] + $registro['nom_par_percepcion_importe_exento']);
        }

        return round($total,2);
    }

    public function total_percepciones_prima_dominical_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_prima_dominical']  = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $total = 0.0;

        if ($r_nom_par_percepcion->n_registros == 0){
            return $total;
        }

        foreach ($r_nom_par_percepcion->registros as $registro){
            $total += ($registro['nom_par_percepcion_importe_gravado'] + $registro['nom_par_percepcion_importe_exento']);
        }

        return round($total,2);
    }
    
    public function total_percepciones_vacaciones_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_vacaciones']  = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $total = 0.0;

        if ($r_nom_par_percepcion->n_registros == 0){
            return $total;
        }

        foreach ($r_nom_par_percepcion->registros as $registro){
            $total += ($registro['nom_par_percepcion_importe_gravado'] + $registro['nom_par_percepcion_importe_exento']);
        }

        return round($total,2);
    }

    public function total_percepciones_despensa(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['cat_sat_tipo_percepcion_nom.codigo']  = '029';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $total = 0.0;

        if ($r_nom_par_percepcion->n_registros == 0){
            return $total;
        }

        foreach ($r_nom_par_percepcion->registros as $registro){
            $total += ($registro['nom_par_percepcion_importe_gravado'] + $registro['nom_par_percepcion_importe_exento']);
        }

        return round($total,2);
    }

    private function total_sueldos_exento(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_sueldos_exento'] = 'nom_par_percepcion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_sueldos'] = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }

        return round($r_nom_par_percepcion['total_sueldos_exento'],2);

    }

    private function total_sueldos_gravado(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_sueldos_gravado'] = 'nom_par_percepcion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_sueldos'] = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }

        return round($r_nom_par_percepcion['total_sueldos_gravado'],2);

    }

    public function total_sueldos_monto(int $nom_nomina_id): float|array
    {
        $total_sueldos_gravado = $this->total_sueldos_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_sueldos_gravado);
        }
        $total_sueldos_exento = $this->total_sueldos_exento(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $total_sueldos_exento);
        }

        $total_sueldos = $total_sueldos_gravado + $total_sueldos_gravado;
        return round($total_sueldos,2);
    }




}