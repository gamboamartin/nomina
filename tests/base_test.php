<?php
namespace tests;
use base\orm\modelo_base;
use gamboamartin\errores\errores;
use models\em_cuenta_bancaria;
use models\em_empleado;
use models\nom_conf_empleado;
use models\nom_nomina;
use models\nom_periodo;
use models\nom_rel_empleado_sucursal;
use PDO;

class base_test{

    public function alta_em_cuenta_bancaria(PDO $link): array|\stdClass
    {
        $em_cuenta_bancaria = array();
        $em_cuenta_bancaria['id'] = 1;
        $em_cuenta_bancaria['codigo'] = 1;
        $em_cuenta_bancaria['descripcion'] = 1;
        $em_cuenta_bancaria['bn_sucursal_id'] = 1;
        $em_cuenta_bancaria['em_empleado_id'] = 1;
        $em_cuenta_bancaria['descripcion_select'] = 1;


        $alta = (new em_cuenta_bancaria($link))->alta_registro($em_cuenta_bancaria);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);
        }
        return $alta;
    }

    public function alta_em_empleado(PDO $link): array|\stdClass
    {
        $em_empleado = array();
        $em_empleado['id'] = 1;
        $em_empleado['nombre'] = 1;
        $em_empleado['ap'] = 1;
        $em_empleado['rfc'] = 1;
        $em_empleado['codigo'] = 1;
        $em_empleado['descripcion_select'] = 1;
        $em_empleado['alias'] = 1;
        $em_empleado['codigo_bis'] = 1;
        $em_empleado['telefono'] = 1;
        $em_empleado['dp_calle_pertenece_id'] = 1;
        $em_empleado['cat_sat_regimen_fiscal_id'] = 1;
        $em_empleado['im_registro_patronal_id'] = 1;
        $em_empleado['curp'] = 1;
        $em_empleado['nss'] = 1;
        $em_empleado['fecha_inicio_rel_laboral'] = '2022-01-01';
        $em_empleado['org_puesto_id'] =1;
        $em_empleado['salario_diario'] =250;
        $em_empleado['salario_diario_integrado'] =250;
        $alta = (new em_empleado($link))->alta_registro($em_empleado);
        if(errores::$error){
           return (new errores())->error('Error al dar de alta ', $alta);

        }
        return $alta;
    }
    
    public function alta_nom_conf_empleado(PDO $link): array|\stdClass
    {
        $nom_conf_empleado = array();
        $nom_conf_empleado['id'] = 1;
        $nom_conf_empleado['codigo'] = 1;
        $nom_conf_empleado['descripcion'] = 1;
        $nom_conf_empleado['em_cuenta_bancaria_id'] = 1;
        $nom_conf_empleado['nom_conf_nomina_id'] = 1;

        $alta = (new nom_conf_empleado($link))->alta_registro($nom_conf_empleado);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }

    public function alta_nom_nomina(PDO $link): array|\stdClass
    {
        $nom_nomina = array();
        $nom_nomina['id'] = 1;
        $nom_nomina['im_registro_patronal_id'] = 1;
        $nom_nomina['em_empleado_id'] = 1;
        $nom_nomina['folio'] = 1;
        $nom_nomina['fecha'] = 1;
        $nom_nomina['cat_sat_periodicidad_pago_nom_id'] = 1;
        $nom_nomina['em_cuenta_bancaria_id'] = 1;
        $nom_nomina['fecha_inicial_pago'] = '2022-01-01';
        $nom_nomina['fecha_final_pago'] = '2022-01-01';
        $nom_nomina['num_dias_pagados'] = '1';
        $nom_nomina['descuento'] = '0';
        $nom_nomina['nom_periodo_id'] = 1;
        $nom_nomina['nom_conf_empleado_id'] = 1;

        $alta = (new nom_nomina($link))->alta_registro($nom_nomina);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);
        }
        return $alta;
    }

    public function alta_nom_periodo(PDO $link): array|\stdClass
    {
        $nom_periodo = array();
        $nom_periodo['id'] = 1;
        $nom_periodo['codigo'] = 1;
        $nom_periodo['descripcion'] = 1;
        $nom_periodo['cat_sat_periodicidad_pago_nom_id'] = 1;
        $nom_periodo['im_registro_patronal_id'] = 1;
        $nom_periodo['nom_tipo_periodo_id'] = 1;


        $alta = (new nom_periodo($link))->alta_registro($nom_periodo);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar periodo', data: $alta);

        }
        return $alta;
    }
    
    public function alta_nom_rel_empleado_sucursal(PDO $link): array|\stdClass
    {
        $nom_rel_empleado_sucursal = array();
        $nom_rel_empleado_sucursal['id'] = 1;
        $nom_rel_empleado_sucursal['codigo'] = 1;
        $nom_rel_empleado_sucursal['descripcion'] = 1;
        $nom_rel_empleado_sucursal['em_empleado_id'] = 1;
        $nom_rel_empleado_sucursal['com_sucursal_id'] = 1;


        $alta = (new nom_rel_empleado_sucursal($link))->alta_registro($nom_rel_empleado_sucursal);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta ', $alta);

        }
        return $alta;
    }

    public function del(PDO $link, string $name_model): array
    {
        $model = (new modelo_base($link))->genera_modelo(modelo: $name_model);
        $del = $model->elimina_todo();
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al eliminar '.$name_model, data: $del);
        }
        return $del;
    }

}
