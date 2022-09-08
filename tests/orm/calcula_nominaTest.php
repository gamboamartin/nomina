<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\calcula_imss;
use models\calcula_nomina;
use models\em_cuenta_bancaria;
use models\em_empleado;
use models\fc_cfd_partida;
use models\fc_factura;
use models\fc_partida;
use models\nom_conf_empleado;
use models\nom_data_subsidio;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_otro_pago;
use models\nom_par_percepcion;
use models\nom_periodo;
use models\nom_rel_empleado_sucursal;
use stdClass;


class calcula_nominaTest extends test {
    public errores $errores;
    private stdClass $paths_conf;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->errores = new errores();
        $this->paths_conf = new stdClass();
        $this->paths_conf->generales = '/var/www/html/cat_sat/config/generales.php';
        $this->paths_conf->database = '/var/www/html/cat_sat/config/database.php';
        $this->paths_conf->views = '/var/www/html/cat_sat/config/views.php';
    }

    public function test_calcula_impuestos_netos_por_nomina(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calcula_nomina();
        //$calculo = new liberator($calculo);

        $link = $this->link;

        $del = (new nom_data_subsidio($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar data_subsidio', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_deduccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar par_deduccion', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_otro_pago($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar par_otro_pago', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_percepcion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar par_percepcion', $del);
            print_r($error);
            exit;
        }


        $del = (new nom_nomina($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar nomina', $del);
            print_r($error);
            exit;
        }

        $del = (new fc_partida($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar partida', $del);
            print_r($error);
            exit;
        }
        
        $del = (new fc_factura($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar factura', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_rel_empleado_sucursal($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new nom_conf_empleado($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new em_cuenta_bancaria($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new em_empleado($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new nom_periodo($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }


        $em_empleado_ins = array();
        $em_empleado_ins['id'] = 1;
        $em_empleado_ins['nombre'] = 1;
        $em_empleado_ins['ap'] = 1;
        $em_empleado_ins['rfc'] = 1;
        $em_empleado_ins['codigo'] = 1;
        $em_empleado_ins['descripcion_select'] = 1;
        $em_empleado_ins['alias'] = 1;
        $em_empleado_ins['codigo_bis'] = 1;
        $em_empleado_ins['telefono'] = 1;
        $em_empleado_ins['dp_calle_pertenece_id'] = 1;
        $em_empleado_ins['cat_sat_regimen_fiscal_id'] = 1;
        $em_empleado_ins['im_registro_patronal_id'] = 1;
        $em_empleado_ins['curp'] = 1;
        $em_empleado_ins['nss'] = 1;
        $em_empleado_ins['fecha_inicio_rel_laboral'] = '2022-01-01';
        $em_empleado_ins['org_puesto_id'] =1;
        $em_empleado_ins['salario_diario'] =200;
        $em_empleado_ins['salario_diario_integrado'] =200;
        $alta = (new em_empleado($this->link))->alta_registro($em_empleado_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta ', $alta);
            print_r($error);
            exit;
        }

        $nom_conf_empleado_ins = array();
        $nom_conf_empleado_ins['id'] = 1;
        $nom_conf_empleado_ins['codigo'] = 1;
        $nom_conf_empleado_ins['descripcion'] = 1;
        $nom_conf_empleado_ins['em_empleado_id'] = 1;
        $nom_conf_empleado_ins['nom_conf_nomina_id'] = 1;
        $nom_conf_empleado_ins['descripcion_select'] = 1;


        $alta = (new nom_conf_empleado($this->link))->alta_registro($nom_conf_empleado_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta ', $alta);
            print_r($error);
            exit;
        }

        $nom_rel_empleado_sucursal_ins = array();
        $nom_rel_empleado_sucursal_ins['id'] = 1;
        $nom_rel_empleado_sucursal_ins['codigo'] = 1;
        $nom_rel_empleado_sucursal_ins['descripcion'] = 1;
        $nom_rel_empleado_sucursal_ins['em_empleado_id'] = 1;
        $nom_rel_empleado_sucursal_ins['com_sucursal_id'] = 1;


        $alta = (new nom_rel_empleado_sucursal($this->link))->alta_registro($nom_rel_empleado_sucursal_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta ', $alta);
            print_r($error);
            exit;
        }

        $em_cuenta_bancaria_ins = array();
        $em_cuenta_bancaria_ins['id'] = 1;
        $em_cuenta_bancaria_ins['codigo'] = 1;
        $em_cuenta_bancaria_ins['descripcion'] = 1;
        $em_cuenta_bancaria_ins['bn_sucursal_id'] = 1;
        $em_cuenta_bancaria_ins['em_empleado_id'] = 1;
        $em_cuenta_bancaria_ins['descripcion_select'] = 1;

        $alta = (new em_cuenta_bancaria($this->link))->alta_registro($em_cuenta_bancaria_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta ', $alta);
            print_r($error);
            exit;
        }

        $nom_periodo_ins = array();
        $nom_periodo_ins['id'] = 1;
        $nom_periodo_ins['codigo'] = 1;
        $nom_periodo_ins['descripcion'] = 1;
        $nom_periodo_ins['cat_sat_periodicidad_pago_nom_id'] = 1;
        $nom_periodo_ins['im_registro_patronal_id'] = 1;
        $nom_periodo_ins['nom_tipo_periodo_id'] = 1;


        $alta = (new nom_periodo($this->link))->alta_registro($nom_periodo_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta ', $alta);
            print_r($error);
            exit;
        }


        $nom_nomina_ins['id'] = 1;
        $nom_nomina_ins['im_registro_patronal_id'] = 1;
        $nom_nomina_ins['em_empleado_id'] = 1;
        $nom_nomina_ins['folio'] = 1;
        $nom_nomina_ins['fecha'] = '2022-01-01';
        $nom_nomina_ins['fecha_final_pago'] = '2022-01-01';
        $nom_nomina_ins['fecha_inicial_pago'] = '2022-01-01';
        $nom_nomina_ins['num_dias_pagados'] = 15;
        $nom_nomina_ins['descuento'] = 0;
        $nom_nomina_ins['cat_sat_periodicidad_pago_nom_id'] = 3;
        $nom_nomina_ins['em_cuenta_bancaria_id'] = 1;
        $nom_nomina_ins['nom_periodo_id'] = 1;

        $alta = (new nom_nomina($this->link))->alta_registro($nom_nomina_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta nomina', $alta);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $calculo->calcula_impuestos_netos_por_nomina($link, $nom_nomina_id);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(45.9,$resultado->isr_neto);
        $this->assertEquals(0,$resultado->subsidio_neto);

        errores::$error = false;

    }

    public function test_calculos(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calcula_nomina();
        //$calculo = new liberator($calculo);

        $link = $this->link;

        $del = (new nom_data_subsidio($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_percepcion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_otro_pago($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_deduccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_nomina($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new fc_partida($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar partida', $del);
            print_r($error);
            exit;
        }

        $del = (new fc_factura($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar factura', $del);
            print_r($error);
            exit;
        }

        $nom_nomina_ins['id'] = 1;
        $nom_nomina_ins['im_registro_patronal_id'] = 1;
        $nom_nomina_ins['em_empleado_id'] = 1;
        $nom_nomina_ins['folio'] = 1;
        $nom_nomina_ins['fecha'] = '2022-01-01';
        $nom_nomina_ins['fecha_final_pago'] = '2022-01-01';
        $nom_nomina_ins['fecha_inicial_pago'] = '2022-01-01';
        $nom_nomina_ins['num_dias_pagados'] = 1;
        $nom_nomina_ins['descuento'] = 0;
        $nom_nomina_ins['cat_sat_periodicidad_pago_nom_id'] = 1;
        $nom_nomina_ins['em_cuenta_bancaria_id'] = 1;
        $nom_nomina_ins['nom_periodo_id'] = 1;

        $alta = (new nom_nomina($this->link))->alta_registro($nom_nomina_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta nomina', $alta);
            print_r($error);
            exit;
        }

        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 3000;
        $alta = (new nom_par_percepcion($this->link))->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $calculo->calculos($link, $nom_nomina_id);



        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(813.31,$resultado->isr);
        $this->assertEquals(0,$resultado->subsidio);

        errores::$error = false;

    }




}

