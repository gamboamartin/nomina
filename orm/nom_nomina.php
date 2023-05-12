<?php

namespace gamboamartin\nomina\models;

use base\orm\modelo;
use config\generales;
use DateTime;
use gamboamartin\documento\models\doc_documento;
use gamboamartin\documento\models\doc_extension_permitido;
use gamboamartin\empleado\models\em_abono_anticipo;
use gamboamartin\empleado\models\em_anticipo;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\empleado\models\em_registro_patronal;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_cfdi_sellado;
use gamboamartin\facturacion\models\fc_csd;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\facturacion\models\fc_factura_documento;
use gamboamartin\facturacion\models\fc_partida;
use gamboamartin\im_registro_patronal\models\calcula_cuota_obrero_patronal;
use gamboamartin\nomina\controllers\xml_nom;
use gamboamartin\organigrama\models\org_empresa;
use gamboamartin\organigrama\models\org_sucursal;
use gamboamartin\plugins\files;
use gamboamartin\xml_cfdi_4\timbra;
use gamboamartin\nomina\models\base\limpieza;
use gamboamartin\im_registro_patronal\models\im_uma;
use Mpdf\Mpdf;
use PDO;
use SoapClient;
use SoapFault;
use stdClass;
use Throwable;
use ZipArchive;

class nom_nomina extends modelo
{
    public function __construct(PDO $link)
    {
        $tabla = 'nom_nomina';
        $columnas = array($tabla => false, 'dp_calle_pertenece' => $tabla, 'dp_calle' => 'dp_calle_pertenece',
            'dp_colonia_postal' => 'dp_calle_pertenece', 'dp_colonia' => 'dp_colonia_postal',
            'dp_cp' => 'dp_colonia_postal', 'dp_municipio' => 'dp_cp', 'dp_estado' => 'dp_municipio',
            'dp_pais' => 'dp_estado', 'em_empleado' => $tabla, 'fc_factura' => $tabla,'fc_csd' =>'fc_factura',
            'org_sucursal' => 'fc_csd','org_empresa'=> 'org_sucursal', 'cat_sat_regimen_fiscal'=>'fc_factura',
            'cat_sat_periodicidad_pago_nom'=>$tabla, 'em_registro_patronal'=>$tabla,'cat_sat_tipo_contrato_nom'=>$tabla,
            'nom_periodo'=>$tabla, 'cat_sat_tipo_nomina'=>$tabla,'cat_sat_tipo_jornada_nom'=>$tabla,
            'cat_sat_tipo_regimen_nom'=>'em_empleado','org_departamento'=>$tabla,'org_puesto'=>$tabla,
            'em_clase_riesgo'=>'em_registro_patronal','em_cuenta_bancaria'=>$tabla,
            'bn_sucursal'=>'em_cuenta_bancaria','bn_banco'=>'bn_sucursal');

        $campos_obligatorios = array('cat_sat_periodicidad_pago_nom_id', 'cat_sat_tipo_contrato_nom_id',
            'cat_sat_tipo_jornada_nom_id','cat_sat_tipo_nomina_id','dp_calle_pertenece_id', 'em_cuenta_bancaria_id',
            'fecha_inicial_pago', 'fecha_final_pago', 'em_registro_patronal_id', 'em_empleado_id','nom_periodo_id',
            'num_dias_pagados','org_departamento_id','org_puesto_id','em_clase_riesgo_id','em_cuenta_bancaria_id',
            'fecha_pago');

        $columnas_extra = array();
        $columnas_extra['em_empleado_nombre_completo'] = 'CONCAT (IFNULL(em_empleado.nombre,"")," ",IFNULL(em_empleado.ap, "")," ",IFNULL(em_empleado.am,""))';

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

        $columnas_extra['nom_nomina_total_deduccion_retenido_gravado'] =
            "IFNULL ((SELECT SUM(nom_par_deduccion.importe_gravado) FROM  nom_par_deduccion 
            INNER JOIN nom_deduccion ON nom_par_deduccion.nom_deduccion_id = nom_deduccion.id 
            AND nom_deduccion.es_impuesto_retenido = 'activo' AND nom_par_deduccion.nom_nomina_id = nom_nomina.id), 0)";

        $columnas_extra['nom_nomina_total_deduccion_retenido_exento'] =
            "IFNULL ((SELECT SUM(nom_par_deduccion.importe_exento) FROM  nom_par_deduccion 
            INNER JOIN nom_deduccion ON nom_par_deduccion.nom_deduccion_id = nom_deduccion.id 
            AND nom_deduccion.es_impuesto_retenido = 'activo' AND nom_par_deduccion.nom_nomina_id = nom_nomina.id), 0)";

        $columnas_extra['nom_nomina_total_deduccion_retenido'] =
            "IFNULL($columnas_extra[nom_nomina_total_deduccion_retenido_gravado] + $columnas_extra[nom_nomina_total_deduccion_retenido_exento] ,0)";

        $columnas_extra['nom_nomina_total_deduccion_descuento_gravado'] =
            "IFNULL ((SELECT SUM(nom_par_deduccion.importe_gravado) FROM  nom_par_deduccion 
            INNER JOIN nom_deduccion ON nom_par_deduccion.nom_deduccion_id = nom_deduccion.id 
            AND nom_deduccion.es_otra_deduccion = 'activo' AND nom_par_deduccion.nom_nomina_id = nom_nomina.id), 0)";

        $columnas_extra['nom_nomina_total_deduccion_descuento_exento'] =
            "IFNULL ((SELECT SUM(nom_par_deduccion.importe_exento) FROM  nom_par_deduccion 
            INNER JOIN nom_deduccion ON nom_par_deduccion.nom_deduccion_id = nom_deduccion.id 
            AND nom_deduccion.es_otra_deduccion = 'activo' AND nom_par_deduccion.nom_nomina_id = nom_nomina.id), 0)";

        $columnas_extra['nom_nomina_total_deduccion_descuento'] =
            "IFNULL($columnas_extra[nom_nomina_total_deduccion_descuento_gravado] + $columnas_extra[nom_nomina_total_deduccion_descuento_exento] ,0)";

        $columnas_extra['nom_nomina_total'] =
            "IFNULL($columnas_extra[nom_nomina_total_percepcion_total] + $columnas_extra[nom_nomina_total_otro_pago_total]- $columnas_extra[nom_nomina_total_deduccion_total],0)";

        $columnas_extra['nom_nomina_total_cuota'] =
            "IFNULL ((SELECT SUM(nom_concepto_imss.monto) 
            FROM  nom_concepto_imss WHERE nom_concepto_imss.nom_nomina_id = nom_nomina.id),0)";

        $tipo_campos['fecha_pago'] = 'fecha';

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra, tipo_campos: $tipo_campos);

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

        if($uma_5 < $monto_dfl){
            $res = $monto_dfl - $uma_5;
            $nom_par_percepcion['importe_gravado'] = round($res + $monto_dfl,2);
            $nom_par_percepcion['importe_exento'] = round($monto_dfl,2);
        }

        return $nom_par_percepcion;
    }

    public function calcula_aplica_dia_descanso(int $dias_descanso, array $nom_par_percepcion,float $salario_diario): array
    {
        if($dias_descanso <= 0){
            return $this->error->error(mensaje: 'Error dias de descanso no puede ser menor o igual a 0',
                data: $dias_descanso);
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
        $monto_dfl = round($salario_diario * $dias_descanso,2);

        $nom_par_percepcion['importe_exento'] = round($monto_dfl,2);
        $nom_par_percepcion['importe_gravado'] = round($monto_dfl,2);

        if($uma_5 < $monto_dfl){
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

        if((float)$monto_uma < $prima_dominical){
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
            em_registro_patronal: $registros['em_registro_patronal']);
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
                porc_riesgo_trabajo: $registros['em_registro_patronal']->em_clase_riesgo_factor,
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

        if($registros['nom_conf_empleado']->nom_conf_nomina_aplica_dia_descanso === 'activo'
            && $dias->dias_descanso > 0){
            $nom_percepcion = (new nom_percepcion($this->link))->get_aplica_dia_descanso();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error insertar conceptos', data: $nom_percepcion);
            }

            $nom_par_percepcion_dd= array();
            $nom_par_percepcion_dd['nom_nomina_id'] = $r_alta_bd->registro_id;
            $nom_par_percepcion_dd['nom_percepcion_id'] = $nom_percepcion['nom_percepcion_id'];

            $nom_par_percepcion_dd = $this->calcula_aplica_dia_descanso(
                dias_descanso: $dias->dias_descanso,
                nom_par_percepcion: $nom_par_percepcion_dd,
                salario_diario: $registros['em_empleado']->em_empleado_salario_diario);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al calcular septimo dia', data: $nom_par_percepcion_dd);
            }

            $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(
                registro: $nom_par_percepcion_dd);
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

    public function calculo_bruto(array $registro, int $registro_id){
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
            'nom_conf_nomina_aplica_dia_festivo_laborado','nom_conf_nomina_aplica_dia_descanso');
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
            $res = $this->registro['num_dias_pagados'] / 7;
            $this->registro['num_dias_pagados'] -= round($res);
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
        
        if($nom_conf_empleado->nom_conf_nomina_aplica_dia_descanso === 'activo'){
            $dias_incidencia = (new nom_incidencia($this->link))->total_dias_aplica_dia_descanso(
                em_empleado_id: $this->registro['em_empleado_id'],nom_periodo_id: $this->registro['nom_periodo_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener los dias de incidencia', data: $dias_incidencia);
            }
            $dias->dias_descanso = $dias_incidencia;
        }

        $dias_incidencia = (new nom_incidencia($this->link))->total_dias_incidencias_n_dias(
            em_empleado_id: $this->registro['em_empleado_id'],nom_periodo_id: $this->registro['nom_periodo_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los dias de incidencia', data: $dias_incidencia);
        }

        $this->registro['num_dias_pagados'] -= $dias_incidencia;
        $dias->dias_pagados_periodo -= $dias_incidencia;
        $dias->dias_pagados_reales = $this->registro['num_dias_pagados'] + $dias_vacaciones;

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

    public function crea_pdf_recibo_nomina(int $nom_nomina_id, Mpdf $pdf){
        $nomina = $this->registro(registro_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registro de nomina', data: $nomina);
        }

        $org_empresa = (new org_empresa($this->link))->registro(registro_id: $nomina['org_empresa_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro de empresa', data:  $org_empresa);
        }

        $filtro["fc_factura_id"] = $nomina['nom_nomina_fc_factura_id'];
        $cfdi_sellado = (new fc_cfdi_sellado($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener cfdi_sellado', data:  $cfdi_sellado);
        }

        $percepciones = (new nom_par_percepcion($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registro percepciones', data: $percepciones);
        }

        $otros_pagos = (new nom_par_otro_pago($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error('Error al obtener registros de otros pagos', $otros_pagos);
        }

        $deducciones = (new nom_par_deduccion($this->link))->get_by_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error('Error al obtener registros de deducciones', $deducciones);
        }

        $pdf->AddPage();
        $pdf->SetFont('Arial');
        $pdf->setSourceFile((new generales())->path_base . 'archivos/plantillas/nomina.pdf'); // Sin extensión
        $template = $pdf->importPage(1);
        $pdf->useTemplate($template);

        if(isset($nomina['nom_nomina_uuid_relacionado']) && (string)$nomina['nom_nomina_uuid_relacionado'] !== ''){
            $pdf->SetXY(18, 145);
            $pdf->MultiCell(w: 185, h: 3, txt: "Tipo Relacion: 04 Sustitución de los CFDI Previos", maxrows: 10);

            $pdf->SetXY(18, 150);
            $pdf->MultiCell(w: 185, h: 3, txt: "UUID: ".$nomina['nom_nomina_uuid_relacionado'], maxrows: 10);
        }

        $pdf->SetXY(18.7, 14);
        $pdf->Cell(0, 0, $nomina['org_empresa_razon_social']);

        $pdf->SetXY(165, 14);
        $pdf->Cell(0, 0, explode(' ', $nomina['nom_nomina_fecha_pago'])[0]);

        $pdf->SetXY(26, 19);
        $pdf->Cell(0, 0, $nomina['org_empresa_rfc']);

        $pdf->SetXY(77, 19);
        $pdf->Cell(0, 0, $nomina['em_registro_patronal_descripcion']);

        $pdf->SetXY(165, 17.5);
        $pdf->Cell(0, 0, date('h:i:s'));

        $pdf->SetXY(35, 22.6);
        $pdf->Cell(0, 0, $nomina['cat_sat_regimen_fiscal_descripcion']);

        $pdf->SetXY(47, 27.5);
        $pdf->Cell(0, 0, $org_empresa['dp_cp_descripcion']);

        $nombre_receptor = $nomina['em_empleado_nombre'] . ' ' . $nomina['em_empleado_ap'] . ' ' . $nomina['em_empleado_am'];

        $pdf->SetXY(18, 40);
        $pdf->Cell(0, 0, $nombre_receptor);

        $pdf->SetXY(25, 44);
        $pdf->Cell(0, 0, $nomina['em_empleado_rfc']);

        $pdf->SetXY(27, 47.5);
        $pdf->Cell(0, 0, $nomina['em_empleado_curp']);

        $pdf->SetXY(49, 52.5);
        $pdf->Cell(0, 0, $nomina['em_empleado_fecha_inicio_rel_laboral']);

        $pdf->SetXY(31, 57);
        $pdf->Cell(0, 0, $nomina['cat_sat_tipo_jornada_nom_descripcion']);

        $pdf->SetXY(25, 61);
        $pdf->Cell(0, 0, $nomina['em_empleado_nss']);

        $pdf->SetXY(127, 39.5);
        $pdf->Cell(0, 0, explode('-', $nomina['nom_nomina_fecha_final_pago'])[0]);

        $periodo = $nomina['cat_sat_periodicidad_pago_nom_descripcion'] . ' ' . $nomina['nom_nomina_fecha_inicial_pago'];
        $periodo .= ' a ' . $nomina['nom_nomina_fecha_final_pago'];

        $pdf->SetXY(125, 44.5);
        $pdf->Cell(0, 0, $periodo);

        $pdf->SetXY(131, 48.5);
        $pdf->Cell(0, 0, $nomina['nom_nomina_num_dias_pagados']);

        $pdf->SetXY(129, 52.5);
        $pdf->Cell(0, 0, $nomina['nom_nomina_fecha_pago']);

        $pdf->SetXY(125, 57);
        $pdf->Cell(0, 0, $nomina['org_puesto_descripcion']);

        $pdf->SetXY(125, 61);
        $pdf->Cell(0, 0, $nomina['org_departamento_descripcion']);

        $pdf->SetXY(125, 65);
        $pdf->Cell(0, 0, "$" . number_format($nomina['em_empleado_salario_diario'], 2));

        $pdf->SetFont('Arial', '', 6);

        $y = 87;
        foreach ($percepciones as $percepcion) {
            $pdf->SetXY(18, $y);
            $pdf->Cell(0, 0, $percepcion['cat_sat_tipo_percepcion_nom_codigo']);

            $y -= 1;
            $pdf->SetXY(30, $y);
            $pdf->MultiCell(w: 45, h: 2.5, txt: $percepcion['nom_percepcion_descripcion'], maxrows: 9);

            $y++;

            $pdf->SetXY(75, $y);
            $pdf->Cell(0, 0, "$" . number_format($percepcion['nom_par_percepcion_importe_gravado'], 2));

            $pdf->SetXY(95, $y);
            $pdf->Cell(0, 0, "$" . number_format($percepcion['nom_par_percepcion_importe_exento'], 2));

            $total = $percepcion['nom_par_percepcion_importe_gravado'] +
                $percepcion['nom_par_percepcion_importe_exento'];

            $pdf->SetXY(110, $y);
            $pdf->Cell(0, 0, "$" . number_format($total, 2));

            $y += 4;
        }

        foreach ($otros_pagos as $otros_pago) {
            $pdf->SetXY(18, $y);
            $pdf->Cell(0, 0, $otros_pago['cat_sat_tipo_otro_pago_nom_codigo']);

            $y -= 1;
            $pdf->SetXY(30, $y);
            $pdf->MultiCell(w: 50, h: 2.5, txt: $otros_pago['nom_otro_pago_descripcion'], maxrows: 10);

            $y++;

            $pdf->SetXY(75, $y);
            $pdf->Cell(0, 0, "$" . number_format($otros_pago['nom_par_otro_pago_importe_gravado'], 2));

            $pdf->SetXY(95, $y);
            $pdf->Cell(0, 0, "$" . number_format($otros_pago['nom_par_otro_pago_importe_exento'], 2));

            $total = $otros_pago['nom_par_otro_pago_importe_gravado'] +
                $otros_pago['nom_par_otro_pago_importe_exento'];

            $pdf->SetXY(110, $y);
            $pdf->Cell(0, 0, "$" . number_format($total, 2));

            $y += 4;
        }

        $y = 87;
        foreach ($deducciones as $deduccion) {
            // print_r($percepcion);exit;
            $pdf->SetXY(130, $y);
            $pdf->Cell(0, 0, $deduccion['cat_sat_tipo_deduccion_nom_codigo']);


            $y -= 1;
            $pdf->SetXY(150, $y);
            $pdf->MultiCell(w: 40, h: 2.5, txt: $deduccion['nom_deduccion_descripcion'], maxrows: 5);

            $y++;

            $total_deduccion = $deduccion['nom_par_deduccion_importe_gravado'] +
                $deduccion['nom_par_deduccion_importe_exento'];

            $pdf->SetXY(188, $y);
            $pdf->Cell(0, 0, "$" . number_format($total_deduccion, 2));

            $y += 5;
        }

        $pdf->SetXY(185, 134.5);
        $pdf->Cell(0, 0, "$" . number_format($nomina['nom_nomina_total_percepcion_total'], 2));

        $pdf->SetXY(185, 138.5);
        $pdf->Cell(0, 0, "$" . number_format($nomina['nom_nomina_total_deduccion_descuento'], 2));

        $pdf->SetXY(185, 142.3);
        $pdf->Cell(0, 0, "$" . number_format($nomina['nom_nomina_total_deduccion_retenido'], 2));

        $pdf->SetXY(185, 146);
        $pdf->Cell(0, 0, "$" . number_format($nomina['nom_nomina_total'], 2));

        $pdf->SetXY(110, 134.5);
        $pdf->Cell(0, 0, "$" . number_format($nomina['nom_nomina_total_percepcion_total'], 2));

        $total = $nomina['nom_nomina_total'];

        $total_letra = (new numero_texto())->to_word($total, 'MXN');

        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(130, 155);
        $pdf->Cell(0, 0, $total_letra);


        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(115, 177);
        $pdf->Cell(0, 0, '99 Por Definir');


        if($cfdi_sellado->n_registros > 0){
            $ruta_qr = (new nom_nomina_documento(link: $this->link))->get_nomina_documento(nom_nomina_id: $nom_nomina_id,
                tipo_documento: "qr_cfdi");
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener QR',data:  $ruta_qr);
            }

            $pdf->SetFont('Arial','',8);
            $pdf->SetXY( 145,205);
            $pdf->Cell(0,0,$cfdi_sellado->registros[0]['fc_cfdi_sellado_comprobante_no_certificado']);

            $pdf->SetXY( 145,208);
            $pdf->Cell(0,0,$cfdi_sellado->registros[0]['fc_cfdi_sellado_uuid']);

            $pdf->SetXY( 145,212);
            $pdf->Cell(0,0,$cfdi_sellado->registros[0]['fc_cfdi_sellado_complemento_tfd_no_certificado_sat']);

            $pdf->SetXY(145, 216);
            $pdf->Cell(0, 0,$cfdi_sellado->registros[0]['fc_cfdi_sellado_complemento_tfd_fecha_timbrado']);

            $pdf->SetFont('Arial', '', 6);
            $pdf->SetXY(18, 222);
            $pdf->MultiCell(w: 185, h: 3, txt: $cfdi_sellado->registros[0]['fc_cfdi_sellado_complemento_tfd_sello_cfd'], maxrows: 10);

            $pdf->SetXY(18, 238);
            $pdf->MultiCell(w: 185, h: 3, txt: $cfdi_sellado->registros[0]['fc_cfdi_sellado_complemento_tfd_sello_sat'], maxrows: 10);

            $pdf->SetFont('Arial', '', 4);
            $pdf->SetXY(18, 252);
            $pdf->MultiCell(w: 185, h: 3, txt: $cfdi_sellado->registros[0]['fc_cfdi_sellado_cadena_complemento_sat'], maxrows: 10);

            $pdf->Image("./".$ruta_qr, 18, 176, 40);
        }

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

    public function descarga_recibo_nomina(int $nom_nomina_id): bool|array
    {
        try {
            $temporales = (new generales())->path_base . "archivos/tmp/";
            $pdf = new Mpdf(['tempDir' => $temporales]);
        }
        catch (Throwable $e){
            return $this->error->error('Error al generar objeto de pdf', $e);
        }

        $doc_tipo_documento_id = $this->doc_tipo_documento_id(extension: "pdf");
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $doc_tipo_documento_id);
        }

        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['doc_tipo_documento.id'] = $doc_tipo_documento_id;
        $existe = (new nom_nomina_documento(link: $this->link))->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe documento', data: $existe);
        }

        $nomina = $this->registro(registro_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registro de nomina', data: $nomina);
        }
        $nombre_receptor = $nomina['em_empleado_nombre'] . ' ' . $nomina['em_empleado_ap'] . ' ' . $nomina['em_empleado_am'];

        if(!$existe) {
            $r_pdf = $this->crea_pdf_recibo_nomina(nom_nomina_id: $nom_nomina_id, pdf: $pdf);

            $pdf->Output($nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf', 'F');

            $file['name'] = $nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf';
            $file['tmp_name'] = $nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf';
            $doc_documento_ins['doc_tipo_documento_id'] = 5;

            $r_doc_documento = (new doc_documento(link: $this->link))->alta_documento(registro: $doc_documento_ins, file: $file);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al al insertar documento', data: $r_doc_documento);
            }

            $nom_nomina_documento = array();
            $nom_nomina_documento['nom_nomina_id'] = $nom_nomina_id;
            $nom_nomina_documento['doc_documento_id'] = $r_doc_documento->registro_id;

            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->alta_registro(registro: $nom_nomina_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta factura documento', data: $nom_nomina_documento);
            }

            $pdf->Output($nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf', 'D');
        }else{
            $r_nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: array('nom_nomina.id' => $nom_nomina_id,'doc_tipo_documento.id'=>$doc_tipo_documento_id));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener factura documento', data: $r_nom_nomina_documento);
            }

            if ($r_nom_nomina_documento->n_registros === 0) {
                return $this->error->error(mensaje: 'Error  debe existir al menos una factura_documento', data: $r_nom_nomina_documento);
            }
            $nom_nomina_documento = $r_nom_nomina_documento->registros[0];

            $r_pdf = $this->crea_pdf_recibo_nomina(nom_nomina_id: $nom_nomina_id, pdf: $pdf);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al maquetar doc', data: $r_pdf);
            }

            $pdf->Output($nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf', 'F');

            $registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $_FILES['name'] = $nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf';
            $_FILES['tmp_name'] = $nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf';
            $documento_mod = (new doc_documento(link: $this->link))->modifica_bd(registro: $registro, id: $nom_nomina_documento['doc_documento_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al modificar documento', data: $documento_mod);
            }

            $documento = (new doc_documento(link: $this->link))->registro(registro_id: $nom_nomina_documento['doc_documento_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al obtener documento', data: $documento);
            }

            $ruta_archivo = $documento['doc_documento_ruta_absoluta']; /** Ruta */

            $file_name = $nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'] . '.pdf';

            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$file_name");
            header("Content-Type: application/xml");
            header("Content-Transfer-Encoding: binary");

            readfile($ruta_archivo);

            exit;
        }

        return true;
    }

    public function genera_xml_temp(int $nom_nomina_id){

        $nom_nomina = $this->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }

        $xml = (new xml_nom())->xml(link: $this->link, nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar xml', data: $xml);
        }

        $ruta_archivos_tmp = $this->genera_ruta_archivo_tmp();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos_tmp);
        }

        $documento = array();
        $file = array();
        $file_xml_st = $ruta_archivos_tmp.'/'.$this->registro_id.'.nom.xml';
        file_put_contents($file_xml_st, $xml);

        $doc_tipo_documento_id = $this->doc_tipo_documento_id(extension: "xml");
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $doc_tipo_documento_id);
        }

        $existe = (new nom_nomina_documento(link: $this->link))->existe(array('nom_nomina.id' => $this->registro_id,
            'doc_tipo_documento.id'=>$doc_tipo_documento_id));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe documento', data: $existe);
        }

        if (!$existe) {

            $doc_documento_modelo = new doc_documento(link: $this->link);

            $file['name'] = $file_xml_st;
            $file['tmp_name'] = $file_xml_st;

            $doc_documento_modelo->registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $doc_documento_modelo->registro['descripcion'] = $ruta_archivos_tmp;

            $documento = $doc_documento_modelo->alta_bd(file: $file);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al guardar xml', data: $documento);
            }

            $nom_nomina_documento = array();
            $nom_nomina_documento['nom_nomina_id'] = $this->registro_id;
            $nom_nomina_documento['doc_documento_id'] = $documento->registro_id;

            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->alta_registro(registro: $nom_nomina_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta factura documento', data: $nom_nomina_documento);
            }
        } else {
            $r_nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: array('nom_nomina.id' => $this->registro_id));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener factura documento', data: $r_nom_nomina_documento);
            }

            if ($r_nom_nomina_documento->n_registros > 1) {
                return $this->error->error(mensaje: 'Error solo debe existir una factura_documento', data: $r_nom_nomina_documento);
            }
            if ($r_nom_nomina_documento->n_registros === 0) {
                return $this->error->error(mensaje: 'Error  debe existir al menos una factura_documento', data: $r_nom_nomina_documento);
            }
            $nom_nomina_documento = $r_nom_nomina_documento->registros[0];

            $doc_documento_id = $nom_nomina_documento['doc_documento_id'];

            $registro['descripcion'] = $ruta_archivos_tmp;
            $registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $_FILES['name'] = $file_xml_st;
            $_FILES['tmp_name'] = $file_xml_st;

            $documento = (new doc_documento(link: $this->link))->modifica_bd(registro: $registro, id: $doc_documento_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al modificar documento', data: $r_nom_nomina_documento);
            }

            $documento->registro = (new doc_documento(link: $this->link))->registro(registro_id: $documento->registro_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al obtener documento', data: $documento);
            }
        }

        $rutas = new stdClass();
        $rutas->file_xml_st = $file_xml_st;
        $rutas->doc_documento_ruta_absoluta = $documento->registro['doc_documento_ruta_absoluta'];

        return $rutas;
    }

    public function descarga_recibo_nomina_foreach(array|stdClass $nom_nominas): bool|array
    {
        try {
            $temporales = (new generales())->path_base . "archivos/tmp/";
            $pdf = new Mpdf(['tempDir' => $temporales]);
        } catch (Throwable $e) {
            return $this->error->error('Error al generar objeto de pdf', $e);
        }

        foreach ($nom_nominas->registros as $r_nomina) {
            $r_pdf = $this->crea_pdf_recibo_nomina(nom_nomina_id: $r_nomina['nom_nomina_id'] ,pdf: $pdf);
        }

        $nombre_archivo = "Nominas por periodo";
        $pdf->Output($nombre_archivo.'.pdf','D');

        return true;
    }

    public function descarga_recibo_nomina_zip(array|stdClass $nom_nominas)
    {
        $zip = new ZipArchive();
        $nombreZip = 'Recibos por periodo.zip';
        $zip->open($nombreZip, ZipArchive::CREATE);

        $contador = 1;

        foreach ($nom_nominas->registros as $nomina) {
            try {
                $temporales = (new generales())->path_base . "archivos/tmp/";
                $pdf = new Mpdf(['tempDir' => $temporales]);
            } catch (Throwable $e) {
                return $this->error->error('Error al generar objeto de pdf', $e);
            }

            $doc_tipo_documento_id = $this->doc_tipo_documento_id(extension: "pdf");
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar extension del documento', data: $doc_tipo_documento_id);
            }

            $filtro['doc_tipo_documento.id'] = $doc_tipo_documento_id;
            $filtro['nom_nomina.id'] = $nomina['nom_nomina_id'];
            $r_nom_nomina_documento_recibo = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener documento nomina', data: $r_nom_nomina_documento_recibo);
            }

            if($r_nom_nomina_documento_recibo->n_registros > 0){
                $r_pdf = $this->crea_pdf_recibo_nomina(nom_nomina_id: $nomina['nom_nomina_id'] ,pdf: $pdf);
                $archivo = $pdf->Output('','S');
                $nombre_receptor = $nomina['em_empleado_nombre'] . ' ' . $nomina['em_empleado_ap'] . ' ' . $nomina['em_empleado_am'];
                $zip->addFromString($nombre_receptor . '-' . $nomina['nom_nomina_fecha_final_pago'].$contador.'.pdf', $archivo);
            }

            $nom_nomina = $this->registro(registro_id:  $nomina['nom_nomina_id'], retorno_obj: true);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener registro de nomina', data: $nom_nomina);
            }

            $filtro_xml['doc_tipo_documento.id'] = '2';
            $filtro_xml['nom_nomina.id'] = $nomina['nom_nomina_id'];
            $r_nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: $filtro_xml);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener documento nomina', data: $r_nom_nomina_documento);
            }

            if($r_nom_nomina_documento->n_registros > 0){
                $ruta_archivo = $r_nom_nomina_documento->registros[0]['doc_documento_ruta_absoluta']; /** Ruta */
                if(file_exists($ruta_archivo)) {
                    $zip->addFromString($nom_nomina->nom_nomina_descripcion . '.xml', file_get_contents($ruta_archivo));
                }
            }

            $contador ++;
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $nombreZip);
        header('Content-Length: ' . filesize($nombreZip));
        readfile($nombreZip);

        unlink($nombreZip);
        exit;
    }

    public function descarga_recibo_xml(int $nom_nomina_id): bool|array
    {

        $nom_nomina = $this->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registro de nomina', data: $nom_nomina);
        }

        $filtro['doc_tipo_documento.id'] = '2';
        $filtro['nom_nomina.id'] = $this->registro_id;
        $r_nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
            filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener documento nomina', data: $r_nom_nomina_documento);
        }

        $ruta_archivo = $r_nom_nomina_documento->registros[0]['doc_documento_ruta_absoluta']; /** Ruta */

        $file_name = $nom_nomina->nom_nomina_descripcion.".xml";

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$file_name");
        header("Content-Type: application/xml");
        header("Content-Transfer-Encoding: binary");

        readfile($ruta_archivo);

        exit;
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
        $subtotal = (new fc_factura($this->link))->get_factura_sub_total(fc_factura_id: $fc_factura_id);
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
        $keys = array('em_registro_patronal_id','em_empleado_id','nom_conf_empleado_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $this->registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $em_registro_patronal = $this->registro_por_id(entidad: new em_registro_patronal(link: $this->link),
            id:  $this->registro['em_registro_patronal_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de registro patronal',
                data: $em_registro_patronal);
        }


        $fc_csd_id = $this->registro_por_id(entidad: new fc_csd($this->link),
            id: $em_registro_patronal->em_registro_patronal_fc_csd_id);
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

        return array('em_registro_patronal' => $em_registro_patronal, 'em_empleado' => $em_empleado,
            'fc_csd' => $fc_csd_id, 'nom_rel_empleado_sucursal' => $nom_rel_empleado_sucursal,
            'nom_conf_empleado' => $nom_conf_empleado);
    }

    private function genera_ruta_archivo_tmp(): array|string
    {
        $ruta_archivos = $this->ruta_archivos();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos);
        }

        $ruta_archivos_tmp = $this->ruta_archivos_tmp(ruta_archivos: $ruta_archivos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos_tmp);
        }
        return $ruta_archivos_tmp;
    }

    public function ruta_archivos(string $directorio = ""): array|string
    {
        $ruta_archivos = (new generales())->path_base . "archivos/$directorio";
        if (!file_exists($ruta_archivos)) {
            mkdir($ruta_archivos, 0777, true);
        }
        if (!file_exists($ruta_archivos)) {
            return $this->error->error(mensaje: "Error no existe $ruta_archivos", data: $ruta_archivos);
        }
        return $ruta_archivos;
    }
    private function ruta_archivos_tmp(string $ruta_archivos): array|string
    {
        $ruta_archivos_tmp = $ruta_archivos.'tmp';

        if(!file_exists($ruta_archivos_tmp)){
            mkdir($ruta_archivos_tmp,0777,true);
        }
        if(!file_exists($ruta_archivos_tmp)){
            return $this->error->error(mensaje: 'Error no existe '.$ruta_archivos_tmp, data: $ruta_archivos_tmp);
        }
        return $ruta_archivos_tmp;
    }
    public function genera_xml(int $nom_nomina_id){

        $nom_nomina = $this->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }

        $xml = (new xml_nom())->xml(link: $this->link, nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar xml', data: $xml);
        }

        $ruta_archivos_tmp = $this->genera_ruta_archivo_tmp();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos_tmp);
        }

        $documento = array();
        $file = array();
        $file_xml_st = $ruta_archivos_tmp.'/'.$this->registro_id.'.nom.xml';
        file_put_contents($file_xml_st, $xml);

        $doc_tipo_documento_id = $this->doc_tipo_documento_id(extension: "xml");
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $doc_tipo_documento_id);
        }

        $existe = (new nom_nomina_documento(link: $this->link))->existe(array('nom_nomina.id' => $this->registro_id,
            'doc_tipo_documento.id'=>$doc_tipo_documento_id));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si existe documento', data: $existe);
        }

        if (!$existe) {

            $doc_documento_modelo = new doc_documento(link: $this->link);

            $file['name'] = $file_xml_st;
            $file['tmp_name'] = $file_xml_st;

            $doc_documento_modelo->registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $doc_documento_modelo->registro['descripcion'] = $ruta_archivos_tmp;

            $documento = $doc_documento_modelo->alta_bd(file: $file);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al guardar xml', data: $documento);
            }

            $nom_nomina_documento = array();
            $nom_nomina_documento['nom_nomina_id'] = $this->registro_id;
            $nom_nomina_documento['doc_documento_id'] = $documento->registro_id;

            $nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->alta_registro(registro: $nom_nomina_documento);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta factura documento', data: $nom_nomina_documento);
            }
        } else {
            $r_nom_nomina_documento = (new nom_nomina_documento(link: $this->link))->filtro_and(
                filtro: array('nom_nomina.id' => $this->registro_id));
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener factura documento', data: $r_nom_nomina_documento);
            }

            if ($r_nom_nomina_documento->n_registros === 0) {
                return $this->error->error(mensaje: 'Error  debe existir al menos una factura_documento', data: $r_nom_nomina_documento);
            }
            $nom_nomina_documento = $r_nom_nomina_documento->registros[0];

            $doc_documento_id = $nom_nomina_documento['doc_documento_id'];

            $registro['descripcion'] = $ruta_archivos_tmp;
            $registro['doc_tipo_documento_id'] = $doc_tipo_documento_id;
            $_FILES['name'] = $file_xml_st;
            $_FILES['tmp_name'] = $file_xml_st;

            $documento = (new doc_documento(link: $this->link))->modifica_bd(registro: $registro, id: $doc_documento_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al modificar documento', data: $r_nom_nomina_documento);
            }

            $documento->registro = (new doc_documento(link: $this->link))->registro(registro_id: $documento->registro_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error  al obtener documento', data: $documento);
            }
        }

        $rutas = new stdClass();
        $rutas->file_xml_st = $file_xml_st;
        $rutas->doc_documento_ruta_absoluta = $documento->registro['doc_documento_ruta_absoluta'];

        return $rutas;
    }
    public function doc_tipo_documento_id(string $extension)
    {
        $filtro['doc_extension.descripcion'] = $extension;
        $existe_extension = (new doc_extension_permitido($this->link))->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $existe_extension);
        }
        if (!$existe_extension) {
            return $this->error->error(mensaje: "Error la extension: $extension no esta permitida", data: $existe_extension);
        }

        $r_doc_extension_permitido = (new doc_extension_permitido($this->link))->filtro_and(filtro: $filtro, limit: 1);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $r_doc_extension_permitido);
        }
        return $r_doc_extension_permitido->registros[0]['doc_tipo_documento_id'];
    }


    private function genera_registro_factura(mixed $registros, mixed $empleado_sucursal, mixed $cat_sat,
                                             stdClass $em_registro_patronal): array
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
        $exportacion = '01';
        $com_sucursal_id = $empleado_sucursal['com_sucursal_id'];
        $cat_sat_forma_pago_id = $cat_sat->nom_conf_factura_cat_sat_forma_pago_id;
        $cat_sat_metodo_pago_id = $cat_sat->nom_conf_factura_cat_sat_metodo_pago_id;
        $cat_sat_moneda_id = $cat_sat->nom_conf_factura_cat_sat_moneda_id;
        $com_tipo_cambio_id = $cat_sat->nom_conf_factura_com_tipo_cambio_id;
        $cat_sat_uso_cfdi_id = $cat_sat->nom_conf_factura_cat_sat_uso_cfdi_id;
        $cat_sat_tipo_de_comprobante_id = $cat_sat->nom_conf_factura_cat_sat_tipo_de_comprobante_id;
        $dp_calle_pertenece_id = $em_registro_patronal->dp_calle_pertenece_id;

        return array('exportacion'=>$exportacion,'folio' => $folio, 'serie' => $serie, 'fecha' => $fecha, 'fc_csd_id' => $fc_csd_id,
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
        $this->registro['em_clase_riesgo_id'] = $registros['em_registro_patronal']->em_clase_riesgo_id;


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

    public function maqueta_registros_excel(int $nom_nomina_id, stdClass $conceptos_nomina){

        $registro = $this->registro(registro_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros de la nomina',
                data: $registro);
        }

        /*$fi = (new em_empleado($this->link))->obten_factor(em_empleado_id: $registro['em_empleado_id'],
            fecha_inicio_rel: $registro['em_empleado_fecha_inicio_rel_laboral']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener FI',
                data: $registro);
        }*/

        $suma_imss = $this->obten_sumatoria_imss(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de imss',
                data: $registro);
        }

        $suma_infonavit = $this->obten_infonavit(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener infonavit',
                data: $registro);
        }

        $suma_rcv = $this->obten_rcv(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener la suma de rcv',
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
        
        $compensacion = $this->total_percepciones_compensacion_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener compensacion',
                data: $compensacion);
        }

        $prima_vacacional = $this->total_percepciones_prima_vacacional_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $prima_vacacional',
                data: $prima_vacacional);
        }

        $gratificacion = $this->total_percepciones_gratificacion_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $gratificacion',
                data: $gratificacion);
        }
        
        $gratificacion_especial = $this->total_percepciones_gratificacion_especial_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $gratificacion_especial',
                data: $gratificacion_especial);
        }      
        
        $premio_puntualidad = $this->total_percepciones_premio_puntualidad_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $premio_puntualidad',
                data: $premio_puntualidad);
        }

        $premio_asistencia = $this->total_percepciones_premio_asistencia_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $premio_asistencia',
                data: $premio_asistencia);
        }
                        
        $ayuda_transporte = $this->total_percepciones_ayuda_transporte_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $ayuda_transporte',
                data: $ayuda_transporte);
        }
        
        $horas_extras = $this->total_percepciones_horas_extras_activo(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $horas_extras',
                data: $horas_extras);
        }

        $dias_descanso = $this->total_percepciones_dias_descanso_laborados(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener montos de dias de descanso',
                data: $dias_descanso);
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
        
        $retencion_infonavit = $this->total_deducciones_infonavit_activo(nom_nomina_id: $nom_nomina_id);
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
        $datos['puesto'] = $registro['org_puesto_descripcion'];
        $datos['departamento'] = $registro['org_departamento_descripcion'];
        $datos['registro_patronal'] = $registro['em_registro_patronal_descripcion'];
        $datos['fecha_ingreso'] = $registro['em_empleado_fecha_inicio_rel_laboral'];
        $datos['dias_laborados'] = $registro['nom_nomina_num_dias_pagados'];
        $datos['dias_x_incapacidad'] = 0.0;
        $datos['sd'] = $registro['em_empleado_salario_diario'];
        //$datos['fi'] = $fi;
        $datos['sdi'] = $registro['em_empleado_salario_diario_integrado'];

        /*Percepciones*/

        foreach ($conceptos_nomina->percepciones as $nom_percepcion_id => $descripcion) {
            $percepcion_nom = (new nom_par_percepcion($this->link))->get_by_percepcion(nom_nomina_id: $nom_nomina_id,
                nom_percepcion_id: $nom_percepcion_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener percpcion', data: $percepcion_nom);
            }

            $descripcion_grav = $descripcion . ' Importe Gravado';
            $descripcion_exe = $descripcion . ' Importe Exento';

            $datos[$descripcion_grav] = 0;
            $datos[$descripcion_exe] = 0;
            if ($percepcion_nom->n_registros > 0) {
                $datos[$descripcion_grav] = $percepcion_nom->registros[0]['nom_par_percepcion_importe_gravado'];
                $datos[$descripcion_exe] = $percepcion_nom->registros[0]['nom_par_percepcion_importe_exento'];
            }
        }
        
        /*Percepciones*/
        $datos['suma_percepcion'] = $suma_percepcion;

        /*Otros pagos*/
        foreach ($conceptos_nomina->otros_pagos as $nom_otro_pago_id => $descripcion) {
            $otro_pago_nom = (new nom_par_otro_pago($this->link))->get_by_otro_pago(nom_nomina_id: $nom_nomina_id,
                nom_otro_pago_id: $nom_otro_pago_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener percpcion', data: $otro_pago_nom);
            }

            $descripcion_grav = $descripcion . ' Importe Gravado';
            $descripcion_exe = $descripcion . ' Importe Exento';

            $datos[$descripcion_grav] = 0;
            $datos[$descripcion_exe] = 0;
            if ($otro_pago_nom->n_registros > 0) {
                $datos[$descripcion_grav] = $otro_pago_nom->registros[0]['nom_par_otro_pago_importe_gravado'];
                $datos[$descripcion_exe] = $otro_pago_nom->registros[0]['nom_par_otro_pago_importe_exento'];
            }
        }
        /*Otros pagos*/

        $datos['base_gravable'] = $suma_base_gravable;

        /*Deducciones*/
        foreach ($conceptos_nomina->deducciones as $nom_deduccion_id => $descripcion) {
            $deduccion_nom = (new nom_par_deduccion($this->link))->get_by_deduccion(nom_nomina_id: $nom_nomina_id,
                nom_deduccion_id: $nom_deduccion_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener percpcion', data: $deduccion_nom);
            }

            $descripcion_grav = $descripcion . ' Importe Gravado';
            $descripcion_exe = $descripcion . ' Importe Exento';

            $datos[$descripcion_grav] = 0;
            $datos[$descripcion_exe] = 0;
            if ($deduccion_nom->n_registros > 0) {
                $datos[$descripcion_grav] = $deduccion_nom->registros[0]['nom_par_deduccion_importe_gravado'];
                $datos[$descripcion_exe] = $deduccion_nom->registros[0]['nom_par_deduccion_importe_exento'];
            }
        }

        /*Deducciones*/

        $datos['suma_deduccion'] = $suma_deduccion;
        $datos['neto_a_pagar'] = $suma_percepcion - $suma_deduccion;
        $datos['imss'] = $suma_imss;
        $datos['rcv'] = $suma_rcv;
        $datos['infonavit'] = $suma_infonavit;
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

    public function obten_conceptos_nominas(array $nominas){
        $conc_percepciones = (new nom_nomina($this->link))->obten_conceptos_percepciones(nominas: $nominas);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $conc_percepciones);
        }

        $conc_deducciones = (new nom_nomina($this->link))->obten_conceptos_deducciones(nominas: $nominas);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $conc_deducciones);
        }

        $conc_otros_pagos = (new nom_nomina($this->link))->obten_conceptos_otros_pagos(nominas: $nominas);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $conc_otros_pagos);
        }

        $conceptos = new stdClass();
        $conceptos->percepciones = $conc_percepciones;
        $conceptos->deducciones = $conc_deducciones;
        $conceptos->otros_pagos = $conc_otros_pagos;

        return $conceptos;
    }

    public function obten_conceptos_percepciones(array $nominas): array
    {
        $tipos_percepciones = array();
        foreach ($nominas as $nomina) {
            $percepciones_nom = (new nom_par_percepcion($this->link))->percepciones_by_nomina(nom_nomina_id: $nomina['nom_nomina_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $percepciones_nom);
            }

            foreach ($percepciones_nom->registros as $percepcion_nom){;
                if(!in_array($percepcion_nom['nom_percepcion_id'], $tipos_percepciones)){
                    $tipos_percepciones[$percepcion_nom['nom_percepcion_id']] = $percepcion_nom['nom_percepcion_descripcion'];
                }
            }
        }

        return $tipos_percepciones;
    }

    public function obten_conceptos_deducciones(array $nominas): array
    {
        $tipos_deducciones = array();
        foreach ($nominas as $nomina) {
            $deducciones_nom = (new nom_par_deduccion($this->link))->deducciones_by_nomina(nom_nomina_id: $nomina['nom_nomina_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $deducciones_nom);
            }

            foreach ($deducciones_nom->registros as $deduccion_nom){;
                if(!in_array($deduccion_nom['nom_deduccion_id'], $tipos_deducciones)){
                    $tipos_deducciones[$deduccion_nom['nom_deduccion_id']] = $deduccion_nom['nom_deduccion_descripcion'];
                }
            }
        }

        return $tipos_deducciones;
    }

    public function obten_conceptos_otros_pagos(array $nominas): array
    {
        $tipos_otros_pagos = array();
        foreach ($nominas as $nomina) {
            $otros_pagos_nom = (new nom_par_otro_pago($this->link))->otros_pagos_by_nomina(nom_nomina_id: $nomina['nom_nomina_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener nominas del periodo', data: $otros_pagos_nom);
            }

            foreach ($otros_pagos_nom->registros as $otro_pago_nom){;
                if(!in_array($otro_pago_nom['nom_otro_pago_id'], $tipos_otros_pagos)){
                    $tipos_otros_pagos[$otro_pago_nom['nom_otro_pago_id']] = $otro_pago_nom['nom_otro_pago_descripcion'];
                }
            }
        }

        return $tipos_otros_pagos;
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

    private function obten_infonavit(int $nom_nomina_id){
        $campos = array();
        $campos['total_infonavit'] = 'nom_concepto_imss.monto';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_tipo_concepto_imss.aplica_infonavit'] = 'activo';

        $r_nom_concepto_imss = (new nom_concepto_imss($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener infonavit', data: $r_nom_concepto_imss);
        }

        return round($r_nom_concepto_imss['total_infonavit'],2);

    }

    private function obten_rcv(int $nom_nomina_id){
        $campos = array();
        $campos['total_rcv'] = 'nom_concepto_imss.monto';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_tipo_concepto_imss.aplica_rcv'] = 'activo';

        $r_nom_concepto_imss = (new nom_concepto_imss($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sumatoria rcv', data: $r_nom_concepto_imss);
        }

        return round($r_nom_concepto_imss['total_rcv'],2);

    }

    private function obten_sumatoria_imss(int $nom_nomina_id){
        $campos = array();
        $campos['total_imss'] = 'nom_concepto_imss.monto';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_tipo_concepto_imss.aplica_sumatoria_imss'] = 'activo';

        $r_nom_concepto_imss = (new nom_concepto_imss($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sumatoria imss', data: $r_nom_concepto_imss);
        }

        return round($r_nom_concepto_imss['total_imss'],2);

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

    public function timbra_xml(int $nom_nomina_id): array|stdClass
    {
        $nom_nomina = $this->registro(registro_id: $nom_nomina_id,retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }

        $filtro['fc_factura.id'] = $nom_nomina->fc_factura_id;
        $timbrada = (new fc_cfdi_sellado($this->link))->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar si la factura esta timbrado', data: $timbrada);
        }

        if ($timbrada) {
            return $this->error->error(mensaje: 'Error: la factura ya ha sido timbrada', data: $timbrada);
        }

        $xml = $this->genera_xml(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar XML', data: $xml);
        }

        $xml_contenido = file_get_contents($xml->doc_documento_ruta_absoluta);

        $xml_timbrado = (new timbra())->timbra(contenido_xml: $xml_contenido);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al timbrar XML', data: $xml_timbrado);
        }

        file_put_contents(filename: $xml->doc_documento_ruta_absoluta, data: $xml_timbrado->xml_sellado);

        $alta_qr = $this->guarda_documento(directorio: "codigos_qr", extension: "jpg", contenido: $xml_timbrado->qr_code,
            nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar QR', data: $alta_qr);
        }

        $alta_txt = $this->guarda_documento(directorio: "textos", extension: "txt", contenido: $xml_timbrado->txt,
            nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar TXT', data: $alta_txt);
        }

        $datos_xml = $this->get_datos_xml(ruta_xml: $xml->doc_documento_ruta_absoluta);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener datos del XML', data: $datos_xml);
        }

        $cfdi_sellado = (new fc_cfdi_sellado($this->link))->maqueta_datos(codigo: $datos_xml['cfdi_comprobante']['NoCertificado'],
            descripcion: $datos_xml['cfdi_comprobante']['NoCertificado'], fc_factura_id: $nom_nomina->fc_factura_id,
            comprobante_sello: $datos_xml['cfdi_comprobante']['Sello'], comprobante_certificado: $datos_xml['cfdi_comprobante']['Certificado'],
            comprobante_no_certificado: $datos_xml['cfdi_comprobante']['NoCertificado'], complemento_tfd_sl: "",
            complemento_tfd_fecha_timbrado: $datos_xml['tfd']['FechaTimbrado'],
            complemento_tfd_no_certificado_sat: $datos_xml['tfd']['NoCertificadoSAT'], complemento_tfd_rfc_prov_certif: $datos_xml['tfd']['RfcProvCertif'],
            complemento_tfd_sello_cfd: $datos_xml['tfd']['SelloCFD'], complemento_tfd_sello_sat: $datos_xml['tfd']['SelloSAT'],
            uuid: $datos_xml['tfd']['UUID'], complemento_tfd_tfd: "",cadena_complemento_sat: $xml_timbrado->txt);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar datos para cfdi sellado', data: $cfdi_sellado);
        }

        $alta = (new fc_cfdi_sellado($this->link))->alta_registro(registro: $cfdi_sellado);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfdi sellado', data: $alta);
        }

        return $cfdi_sellado;
    }

    private function get_datos_xml(string $ruta_xml = ""): array
    {
        $xml = simplexml_load_file($ruta_xml);
        $ns = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $ns['cfdi']);
        $xml->registerXPathNamespace('t', $ns['tfd']);

        $xml_data = array();
        $xml_data['cfdi_comprobante'] = array();
        $xml_data['cfdi_emisor'] = array();
        $xml_data['cfdi_receptor'] = array();
        $xml_data['cfdi_conceptos'] = array();
        $xml_data['tfd'] = array();

        $nodos = array();
        $nodos[] = '//cfdi:Comprobante';
        $nodos[] = '//cfdi:Comprobante//cfdi:Emisor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Receptor';
        $nodos[] = '//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto';
        $nodos[] = '//t:TimbreFiscalDigital';

        foreach ($nodos as $key => $nodo) {
            foreach ($xml->xpath($nodo) as $value) {
                $data = (array)$value->attributes();
                $data = $data['@attributes'];
                $xml_data[array_keys($xml_data)[$key]] = $data;
            }
        }
        return $xml_data;
    }
    private function guarda_documento(string $directorio, string $extension, string $contenido, int $nom_nomina_id): array|stdClass
    {
        $ruta_archivos = $this->ruta_archivos(directorio: $directorio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener ruta de archivos', data: $ruta_archivos);
        }

        $ruta_archivo = "$ruta_archivos/$this->registro_id.$extension";

        $guarda_archivo = (new files())->guarda_archivo_fisico(contenido_file: $contenido, ruta_file: $ruta_archivo);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar archivo', data: $guarda_archivo);
        }

        $tipo_documento = $this->doc_tipo_documento_id(extension: $extension);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar extension del documento', data: $tipo_documento);
        }

        $doc_documento_modelo = new doc_documento(link: $this->link);

        $file['name'] = $guarda_archivo;
        $file['tmp_name'] = $guarda_archivo;

        $doc_documento_modelo->registro['doc_tipo_documento_id'] = $tipo_documento;
        $doc_documento_modelo->registro['descripcion'] = "$this->registro_id.$extension";
        $doc_documento_modelo->registro['descripcion_select'] = "$this->registro_id.$extension";

        $documento = $doc_documento_modelo->alta_bd(file: $file);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar jpg', data: $documento);
        }

        $registro['nom_nomina_id'] = $nom_nomina_id;
        $registro['doc_documento_id'] = $documento->registro_id;
        $nomina_documento = (new nom_nomina_documento($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al guardar relacion nomina con documento', data: $nomina_documento);
        }

        return $documento;
    }

    public function obten_xml(string $uuid): array|SoapClient|stdClass
    {
        if($uuid === ''){
            return $this->error->error('$uuid no puede venir vacio',$uuid);
        }

        $generales = new generales();
        $datos_ruta_pac= $generales->ruta_pac;
        $datos_usuario = $generales->usuario_integrador;
        $datos_rfc = $generales->rfc;

        $ws = $datos_ruta_pac;
        $usuario_int = $datos_usuario;
        $rfc_emisor = $datos_rfc;

        try {
            $params = array();
            $params['usuarioIntegrador'] = $usuario_int;
            $params['folioUUID'] = $uuid;
            $params['rfcEmisor'] = $rfc_emisor;

            $client = new SoapClient($ws,$params);
            $response = $client->__soapCall('ObtieneCFDI', array('parameters' => $params));
            if((int)$response->ObtieneCFDIResult->anyType[6]>0){
                return $this->error->error('Error al obtener',$response);
            }

        }
        catch (SoapFault $fault) {
            return $this->error->error('Error de conexion PAC',$fault);
        }
        return $response;
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

    public function total_deducciones_infonavit_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_deduccion.es_infonavit']  = 'activo';
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

    public function total_percepciones_dias_descanso_laborados(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_dia_descanso']  = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
    }

    public function total_percepciones_prima_vacacional_activo(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.id']  = 12;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
    }

    public function total_percepciones_gratificacion_activo(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.id']  = 18;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
    }

    public function total_percepciones_gratificacion_especial_activo(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.id']  = 14;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
    }
    
    public function total_percepciones_premio_puntualidad_activo(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.id']  = 15;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
    }
    
    public function total_percepciones_premio_asistencia_activo(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.id']  = 16;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
    }
    
    public function total_percepciones_ayuda_transporte_activo(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.id']  = 17;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
    }

    public function total_percepciones_horas_extras_activo(int $nom_nomina_id)
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.id']  = 13;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_par_percepcion);
        }

        $montos = new stdClass();
        $montos->gravado = 0.0;
        $montos->exento = 0.0;

        foreach ($r_nom_par_percepcion->registros as $registro){
            $montos->gravado = round($registro['nom_par_percepcion_importe_gravado'],2);
            $montos->exento = round($registro['nom_par_percepcion_importe_exento'],2);
        }

        return $montos;
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
    
    public function total_percepciones_compensacion_activo(int $nom_nomina_id): float|array
    {
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_compensacion']  = 'activo';
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
        if($nom_nomina_id <= 0){
            return $this->error->error(mensaje: 'Error nomina id debe se ser mayor a 0', data: $nom_nomina_id);
        }

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
        if($nom_nomina_id <= 0){
            return $this->error->error(mensaje: 'Error nomina id debe se ser mayor a 0', data: $nom_nomina_id);
        }

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

        $total_sueldos = $total_sueldos_gravado + $total_sueldos_exento;
        return round($total_sueldos,2);
    }

    public function isr_aguinaldo(int $nom_nomina_id){
        $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error('Error al obtener sat receptor', $nom_nomina);
        }

        $em_empleado = (new em_empleado($this->link))->registro(registro_id: $nom_nomina->em_empleado_id);
        if(errores::$error){
            return $this->error->error('Error al obtener sat receptor', $em_empleado);
        }

        $montos_aguinaldo = $this->montos_aguinaldo(em_empleado_id: $nom_nomina->em_empleado_id,
            nom_periodo_id: $nom_nomina->nom_periodo_id);
        if(errores::$error){
            return $this->error->error('Error al obtener montos aguinaldo', $montos_aguinaldo);
        }

        $ingreso_ordinario = $em_empleado->salario_diario * 30.4;

        $isr = (new calculo_isr())->isr(
            cat_sat_periodicidad_pago_nom_id: $nom_nomina->cat_sat_periodicidad_pago_nom_id, link: $this->link,
            monto: $ingreso_ordinario, fecha: $nom_nomina->fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        $subsidio = (new calculo_subsidio())->subsidio(
            cat_sat_periodicidad_pago_nom_id: $nom_nomina->cat_sat_periodicidad_pago_nom_id, link: $this->link, monto: $ingreso_ordinario,
            fecha: $nom_nomina->fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $subsidio', data: $subsidio);
        }

        $isr_ingreso_ordinario = round($isr - $subsidio, 2);

        $total_ingreso_ordinario_gravado = $ingreso_ordinario + $montos_aguinaldo['importe_gravado'];

        $isr = (new calculo_isr())->isr(
            cat_sat_periodicidad_pago_nom_id: $nom_nomina->cat_sat_periodicidad_pago_nom_id, link: $this->link,
            monto: $total_ingreso_ordinario_gravado, fecha: $nom_nomina->fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        $subsidio = (new calculo_subsidio())->subsidio(
            cat_sat_periodicidad_pago_nom_id: $nom_nomina->cat_sat_periodicidad_pago_nom_id, link: $this->link,
            monto: $total_ingreso_ordinario_gravado, fecha: $nom_nomina->fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $subsidio', data: $subsidio);
        }

        $isr_total_ingreso_ordinario = round($isr - $subsidio, 2);

        return round($isr_total_ingreso_ordinario - $isr_ingreso_ordinario, 2);
    }

    public function montos_aguinaldo(int $em_empleado_id, int $nom_periodo_id){
        $bruto_aguinaldo = $this->bruto_aguinaldo(em_empleado_id: $em_empleado_id,nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total gravado', data: $bruto_aguinaldo);
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

        $monto_umas_mensual = round($monto_uma * 30, 2);

        $nom_par_percepcion['importe_exento'] = round($bruto_aguinaldo,2);
        $nom_par_percepcion['importe_gravado'] = 0;

        if((float)$monto_umas_mensual < (float)$bruto_aguinaldo){
            $res = $bruto_aguinaldo - $monto_umas_mensual;
            $nom_par_percepcion['importe_exento'] = round($monto_umas_mensual,2);
            $nom_par_percepcion['importe_gravado'] = round($res,2);
        }

        return $nom_par_percepcion;
    }

    public function bruto_aguinaldo(int $em_empleado_id, int $nom_periodo_id){
        $em_empleado = (new em_empleado($this->link))->registro(registro_id: $em_empleado_id);
        if(errores::$error){
            return $this->error->error('Error al obtener sat receptor', $em_empleado);
        }

        $dias_proporcionales = $this->dias_proporcionales_aguinaldo(em_empleado_id: $em_empleado_id,
            nom_periodo_id: $nom_periodo_id);
        if(errores::$error){
            return $this->error->error('Error al obtener sat receptor', $dias_proporcionales);
        }


        $monto_bruto = $this->monto_bruto_aguinaldo(dias_proporcionales: $dias_proporcionales,
            salario_diario: $em_empleado->salario_diario);
        if(errores::$error){
            return $this->error->error('Error al obtener sat receptor', $monto_bruto);
        }

        return $monto_bruto;
    }

    public function monto_bruto_aguinaldo(float $dias_proporcionales, float $salario_diario){
        if($dias_proporcionales<=0){
            return $this->error->error('Error $dias_proporcionales debe ser menor a 0', $dias_proporcionales);
        }

        if($salario_diario<=0){
            return $this->error->error('Error $salario_diario debe ser menor a 0', $salario_diario);
        }

        $monto_bruto_aguinaldo = $dias_proporcionales * $salario_diario;
        return round($monto_bruto_aguinaldo,2);
    }

    private function dias_proporcionales_aguinaldo(int $em_empleado_id, int $nom_periodo_id): float|array
    {
        if($em_empleado_id<=0){
            return $this->error->error('Error sat_receptor_id debe ser mayor a 0', $em_empleado_id);
        }
        if($nom_periodo_id<=0){
            return $this->error->error('Error $sat_nomina_periodo_pago_id debe ser mayor a 0', $nom_periodo_id);
        }

        $dias_aplicables = $this->dias_aplicables_aguinaldo($em_empleado_id, $nom_periodo_id);
        if(errores::$error){
            return $this->error->error('Error al obtener dias', $dias_aplicables);
        }
        $dias_anio = 365;
        $dias_total_aguinaldo = 15;
        $dias_proporcionales = $dias_aplicables * $dias_total_aguinaldo / $dias_anio;

        return round($dias_proporcionales);
    }

    private function dias_aplicables_aguinaldo(int $em_empleado_id, int $nom_periodo_id): array|int
    {
        if($em_empleado_id<=0){
            return $this->error->error('Error sat_receptor_id debe ser mayor a 0', $em_empleado_id);
        }
        if($nom_periodo_id<=0){
            return $this->error->error('Error $sat_nomina_periodo_pago_id debe ser mayor a 0', $nom_periodo_id);
        }

        $data = $this->fechas_aguinaldo( em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if(errores::$error){
            return $this->error->error('Error al obtener datos', $data);
        }

        $dias_aplicables = $this->calcula_dias_aguinaldo($data->fecha_fin_periodo_pago, $data->fecha_inicio_receptor);
        if(errores::$error){
            return $this->error->error('Error al obtener dias', $dias_aplicables);
        }

        return $dias_aplicables;
    }

    private function fechas_aguinaldo(int $em_empleado_id, int $nom_periodo_id){
        $em_empleado = (new em_empleado($this->link))->registro(registro_id: $em_empleado_id);
        if(errores::$error){
            return $this->error->error('Error al obtener sat receptor', $em_empleado);
        }
        $nom_periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if(errores::$error){
            return $this->error->error('Error al obtener sat nomina periodo pago', $nom_periodo);
        }

        $data = new stdClass();
        $data->fecha_fin_periodo_pago = $em_empleado;
        $data->fecha_inicio_receptor = $nom_periodo;

        return $data;
    }

    private function calcula_dias_aguinaldo(string $fecha_final_periodo, string $fecha_inicio_rel_laboral): int|array
    {
        $valida = $this->validacion->valida_fecha(fecha: $fecha_final_periodo);
        if(errores::$error){
            return $this->error->error('Error al validar $fecha_final_periodo '.$fecha_final_periodo, $valida);
        }
        $valida = $this->validacion->valida_fecha(fecha: $fecha_inicio_rel_laboral);
        if(errores::$error){
            return $this->error->error('Error al validar $fecha_inicio_rel_laboral '.$fecha_inicio_rel_laboral, $valida);
        }

        $fecha_fin_anio = $this->fecha_fin_year(fecha: $fecha_final_periodo);
        if(errores::$error){
            return $this->error->error('Error al fecha fin', $fecha_fin_anio);
        }

        $diferencia_dias = $this->n_dias_entre_fechas(fecha_inicio: $fecha_inicio_rel_laboral,
            fecha_fin: $fecha_fin_anio);
        if(errores::$error){
            return $this->error->error('Error al obtener dias', $diferencia_dias);
        }

        $dias_aplicables = $this->calculo_dias_para_calculo_aguinaldo(diferencia_dias: $diferencia_dias);
        if(errores::$error){
            return $this->error->error('Error al obtener dias', $diferencia_dias);
        }

        return $dias_aplicables;
    }

    private function calculo_dias_para_calculo_aguinaldo(int $diferencia_dias): int
    {
        $dias_aplicables = $diferencia_dias;
        if($diferencia_dias>=365){
            $dias_aplicables = 365;
        }
        return $dias_aplicables;
    }

    private function fecha_fin_year(string $fecha): array|string
    {
        $valida = $this->validacion->valida_fecha(fecha: $fecha);
        if(errores::$error){
            return $this->error->error('Error al validar fecha', $valida);
        }
        $year = $this->year(fecha: $fecha);
        if(errores::$error){
            return $this->error->error('Error al obtener year', $year);
        }
        return $year.'-12-31';
    }

    public function n_dias_entre_fechas(string $fecha_inicio, string $fecha_fin): int|array
    {
        $valida = $this->validacion->valida_fecha(fecha: $fecha_inicio);
        if(errores::$error){
            return $this->error->error('$fecha_inicio invalida '.$fecha_inicio, $valida);
        }
        $valida = $this->validacion->valida_fecha(fecha: $fecha_fin);
        if(errores::$error){
            return $this->error->error('$fecha_fin invalida '.$fecha_fin, $valida);
        }
        try {
            $fecha_inicio_date = new DateTime($fecha_inicio);
            $fecha_fin_base = new DateTime($fecha_fin);
            $diff = $fecha_inicio_date->diff($fecha_fin_base);
        }
        catch (Throwable $e){
            $data = new stdClass();
            $data->parametros = new stdClass();
            $data->e = $e;
            $data->parametros->fecha_inicio = $fecha_inicio;
            $data->parametros->fecha_fin = $fecha_fin;
            return $this->error->error("Error al calcular diferencia de fechas", $data);
        }
        return (int)$diff->days + 1;
    }

    /**
     * @param string $fecha
     * @return int|array
     */
    private function year(string $fecha): int|array
    {
        $valida = $this->validacion->valida_fecha($fecha);
        if(errores::$error){
            return $this->error->error('Error al validar fecha', $valida);
        }

        $fecha_int = strtotime($fecha);
        return (int)date("Y", $fecha_int);
    }
}