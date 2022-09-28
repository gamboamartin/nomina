<?php
namespace tests;
use base\orm\modelo_base;
use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_csd;
use gamboamartin\organigrama\models\org_departamento;
use gamboamartin\organigrama\models\org_empresa;
use gamboamartin\organigrama\models\org_puesto;
use gamboamartin\organigrama\models\org_sucursal;
use models\im_registro_patronal;
use models\nom_conf_empleado;
use models\nom_conf_nomina;
use models\nom_nomina;
use models\nom_periodo;
use models\nom_rel_empleado_sucursal;
use PDO;
use stdClass;

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
        $em_cuenta_bancaria['clabe'] = 1;
        $em_cuenta_bancaria['num_cuenta'] = 1;
        $em_cuenta_bancaria['alias'] = 1;
        $em_cuenta_bancaria['codigo_bis'] = 1;


        $alta = (new em_cuenta_bancaria($link))->alta_registro($em_cuenta_bancaria);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);
        }
        return $alta;
    }

    public function alta_em_empleado(PDO $link, float $salario_diario = 250,
                                     float $salario_diario_integrado = 250): array|\stdClass
    {

        $alta = $this->alta_org_puesto($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $alta = $this->alta_im_registro_patronal($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $registro = array();
        $registro['id'] = 1;
        $registro['nombre'] = 1;
        $registro['ap'] = 1;
        $registro['rfc'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion_select'] = 1;
        $registro['alias'] = 1;
        $registro['codigo_bis'] = 1;
        $registro['telefono'] = 1;
        $registro['dp_calle_pertenece_id'] = 1;
        $registro['cat_sat_regimen_fiscal_id'] = 1;
        $registro['im_registro_patronal_id'] = 1;
        $registro['curp'] = 1;
        $registro['nss'] = 1;
        $registro['fecha_inicio_rel_laboral'] = '2022-01-01';
        $registro['org_puesto_id'] =1;
        $registro['salario_diario'] =$salario_diario;
        $registro['salario_diario_integrado'] =$salario_diario_integrado;
        $registro['cat_sat_tipo_regimen_nom_id'] =1;
        $alta = (new em_empleado($link))->alta_registro($registro);
        if(errores::$error){
           return (new errores())->error('Error al dar de alta ', $alta);

        }
        return $alta;
    }

    public function alta_fc_csd(PDO $link): array|\stdClass
    {
        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['serie'] = 1;
        $registro['org_sucursal_id'] = 1;
        $registro['descripcion_select'] = 1;
        $registro['alias'] = 1;
        $registro['codigo_bis'] = 1;


        $alta = (new fc_csd($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }
    
    public function alta_nom_conf_empleado(PDO $link): array|\stdClass
    {
        $alta = $this->alta_nom_conf_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $alta = $this->alta_em_cuenta_bancaria($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

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

    public function alta_im_registro_patronal(PDO $link): array|\stdClass
    {

        $alta = $this->alta_fc_csd($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['im_clase_riesgo_id'] = 1;
        $registro['fc_csd_id'] = 1;
        $registro['descripcion_select'] = 1;


        $alta = (new im_registro_patronal($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);
        }
        return $alta;
    }

    public function alta_nom_conf_nomina(PDO $link): array|\stdClass
    {

        $alta = (new \gamboamartin\cat_sat\tests\base_test())->alta_cat_sat_tipo_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);
        }

        $nom_conf_empleado = array();
        $nom_conf_empleado['id'] = 1;
        $nom_conf_empleado['codigo'] = 1;
        $nom_conf_empleado['descripcion'] = 1;
        $nom_conf_empleado['nom_conf_factura_id'] = 1;
        $nom_conf_empleado['descripcion_select'] = 1;
        $nom_conf_empleado['cat_sat_periodicidad_pago_nom_id'] = 1;
        $nom_conf_empleado['cat_sat_tipo_nomina_id'] = 1;


        $alta = (new nom_conf_nomina($link))->alta_registro($nom_conf_empleado);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }

    public function alta_nom_nomina(PDO $link, float $salario_diario = 250, float $salario_diario_integrado = 250): array|stdClass
    {

        $alta = $this->alta_org_sucursal(link: $link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $alta = $this->alta_em_empleado(
            link: $link, salario_diario: $salario_diario, salario_diario_integrado: $salario_diario_integrado);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $alta = $this->alta_nom_conf_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }
        $alta = $this->alta_nom_rel_empleado_sucursal($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }



        $alta = $this->alta_nom_periodo($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }


        $registro = array();
        $registro['id'] = 1;
        $registro['im_registro_patronal_id'] = 1;
        $registro['em_empleado_id'] = 1;
        $registro['folio'] = 1;
        $registro['fecha'] = 1;
        $registro['cat_sat_periodicidad_pago_nom_id'] = 1;
        $registro['em_cuenta_bancaria_id'] = 1;
        $registro['fecha_inicial_pago'] = '2022-01-01';
        $registro['fecha_final_pago'] = '2022-01-01';
        $registro['num_dias_pagados'] = '1';
        $registro['descuento'] = '0';
        $registro['nom_periodo_id'] = 1;
        $registro['nom_conf_empleado_id'] = 1;
        $registro['cat_sat_tipo_jornada_nom_id'] = 1;
        $registro['dp_calle_pertenece_id'] = 1;
        $registro['cat_sat_tipo_nomina_id'] = 1;

        $alta = (new nom_nomina($link))->alta_registro($registro);
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

    public function alta_org_departamento(PDO $link): array|\stdClass
    {


        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['org_clasificacion_dep_id'] = 1;



        $alta = (new org_departamento($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);
        }
        return $alta;
    }

    public function alta_org_empresa(PDO $link): array|\stdClass
    {
        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['razon_social'] = 1;
        $registro['rfc'] = 1;
        $registro['nombre_comercial'] = 1;
        $registro['org_tipo_empresa_id'] = 1;

        $alta = (new org_empresa($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar empresa', data: $alta);
        }
        return $alta;
    }

    public function alta_org_puesto(PDO $link): array|\stdClass
    {

        $alta = $this->alta_org_departamento($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['org_tipo_puesto_id'] = 1;
        $registro['org_departamento_id'] = 1;



        $alta = (new org_puesto($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);
        }
        return $alta;
    }

    public function alta_org_sucursal(PDO $link): array|\stdClass
    {

        $alta = $this->alta_org_empresa($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        $del = $this->del_org_sucursal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }


        $registro = array();
        $registro['id'] = 1;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['org_empresa_id'] = 1;
        $registro['codigo_bis'] = 1;
        $registro['org_tipo_sucursal_id'] = 1;


        $alta = (new org_sucursal($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar sucursal', data: $alta);

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

    public function del_cat_sat_tipo_nomina(PDO $link): array
    {

        $del = $this->del_nom_conf_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        $del = $this->del_nom_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = (new \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_tipo_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }




    public function del_em_cuenta_bancaria(PDO $link): array
    {
        $del = $this->del_nom_conf_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del($link, 'gamboamartin\\empleado\\models\\em_cuenta_bancaria');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_em_empleado(PDO $link): array
    {

        $del = $this->del_em_cuenta_bancaria($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_rel_empleado_sucursal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del($link, 'gamboamartin\\empleado\\models\\em_empleado');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_fc_csd(PDO $link): array
    {
        $del = (new base_test())->del_im_registro_patronal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new base_test())->del_fc_factura($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = $this->del($link, 'gamboamartin\\facturacion\\models\\fc_csd');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_fc_factura(PDO $link): array
    {
        $del = $this->del_fc_partida($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        $del = $this->del($link, 'gamboamartin\\facturacion\\models\\fc_factura');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_fc_partida(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\facturacion\\models\\fc_partida');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_im_registro_patronal(PDO $link): array
    {

        $del = (new base_test())->del_nom_periodo($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = $this->del($link, 'models\\im_registro_patronal');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_concepto_imss(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_concepto_imss');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_conf_empleado(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_conf_empleado');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_conf_nomina(PDO $link): array
    {

        $del = (new base_test())->del_nom_conf_percepcion($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_nom_conf_empleado($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = $this->del($link, 'models\\nom_conf_nomina');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_conf_percepcion(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_conf_percepcion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_data_subsidio(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_data_subsidio');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_nomina(PDO $link): array
    {
        $del = $this->del_nom_par_deduccion($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_par_otro_pago($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_par_percepcion($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }



        $del = $this->del($link, 'models\\nom_nomina');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_par_deduccion(PDO $link): array
    {
        $del = $this->del_nom_data_subsidio($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del($link, 'models\\nom_par_deduccion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_par_otro_pago(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_par_otro_pago');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_par_percepcion(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_par_percepcion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_periodo(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_periodo');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_rel_empleado_sucursal(PDO $link): array
    {
        $del = $this->del($link, 'models\\nom_rel_empleado_sucursal');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_org_departamento(PDO $link): array
    {
        $del = (new base_test())->del_org_puesto($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = $this->del($link, 'gamboamartin\\organigrama\\models\\org_departamento');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_org_empresa(PDO $link): array
    {

        $del = (new base_test())->del_org_departamento($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_org_porcentaje_act_economica($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_org_sucursal($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = $this->del($link, 'gamboamartin\\organigrama\\models\\org_empresa');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_org_porcentaje_act_economica(PDO $link): array
    {



        $del = $this->del($link, 'gamboamartin\\organigrama\\models\\org_porcentaje_act_economica');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_org_puesto(PDO $link): array
    {

        $del = (new base_test())->del_em_empleado($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = $this->del($link, 'gamboamartin\\organigrama\\models\\org_puesto');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_org_sucursal(PDO $link): array
    {

        $del = (new base_test())->del_fc_csd($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = $this->del($link, 'gamboamartin\\organigrama\\models\\org_sucursal');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

}
