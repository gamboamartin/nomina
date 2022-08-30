<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\em_cuenta_bancaria;
use models\fc_cfd_partida;
use models\fc_factura;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use stdClass;


class nom_par_percepcionTest extends test {
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


    public function test_alta_bd(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $percepcion = new nom_par_percepcion($this->link);

        $nom_nomina_modelo = new nom_nomina($this->link);
        $nom_nom_par_percepcion_modelo = new nom_par_percepcion($this->link);
        $nom_nom_par_deduccion_modelo = new nom_par_deduccion($this->link);
        $fc_factura_modelo = new fc_factura($this->link);
        $fc_cfd_partida_modelo = new fc_cfd_partida($this->link);
        $em_cuenta_bancaria_modelo = new em_cuenta_bancaria($this->link);



        $del_nom_par_percepcion = $nom_nom_par_percepcion_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $nom_nom_par_percepcion_modelo', data: $del_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $del_nom_par_deduccion = $nom_nom_par_deduccion_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $nom_nom_par_deduccion_modelo', data: $del_nom_par_deduccion);
            print_r($error);
            exit;
        }

        $del_nom_nomina = $nom_nomina_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar nomina', data: $del_nom_nomina);
            print_r($error);
            exit;
        }

        $del_fc_cfd_partida = $fc_cfd_partida_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $fc_cfd_partida', data: $del_fc_cfd_partida);
            print_r($error);
            exit;
        }

        $del_fc_factura = $fc_factura_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar factura', data: $del_fc_factura);
            print_r($error);
            exit;
        }

        $del_em_cuenta_bancaria = $em_cuenta_bancaria_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar em_cuenta_bancaria', data: $del_em_cuenta_bancaria);
            print_r($error);
            exit;
        }

        $em_cuenta_bancaria = array();
        $em_cuenta_bancaria['id'] = 1;
        $em_cuenta_bancaria['codigo'] = 1;
        $em_cuenta_bancaria['descripcion'] = 1;
        $em_cuenta_bancaria['bn_sucursal_id'] = 1;
        $em_cuenta_bancaria['em_empleado_id'] = 1;
        $em_cuenta_bancaria['descripcion_select'] = 1;


        $alta_em_cuenta_bancaria = $em_cuenta_bancaria_modelo->alta_registro($em_cuenta_bancaria);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar em_cuenta_bancaria', data: $alta_em_cuenta_bancaria);
            print_r($error);
            exit;
        }

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

        $alta_nom_nomina = $nom_nomina_modelo->alta_registro($nom_nomina);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nomina', data: $alta_nom_nomina);
            print_r($error);
            exit;
        }

        $percepcion->registro['nom_nomina_id'] = 1;
        $percepcion->registro['nom_percepcion_id'] = 1;
        $percepcion->registro['importe_gravado'] = 100;
        $resultado = $percepcion->alta_bd();


        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

    public function test_calcula_isr_nomina(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $percepcion = new nom_par_percepcion($this->link);
        $percepcion = new liberator($percepcion);

        $del_percepcion = $percepcion->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $del_percepcion', data: $del_percepcion);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 1000;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $alta_nom_par_percepcion = $percepcion->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nomina percepcion', data: $alta_nom_par_percepcion);
            print_r($error);
            exit;
        }


        $nom_par_percepcion_id = 1;
        $resultado = $percepcion->calcula_isr_nomina(partida_percepcion_id: $nom_par_percepcion_id);
        print_r($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertIsFloat($resultado);
        $this->assertEquals(168.61, $resultado);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';



        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 2;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 1000;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $alta_nom_par_percepcion = $percepcion->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nomina percepcion', data: $alta_nom_par_percepcion);
            print_r($error);
            exit;
        }


        $nom_par_percepcion_id = 1;
        $resultado = $percepcion->calcula_isr_nomina(partida_percepcion_id: $nom_par_percepcion_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsFloat($resultado);
        $this->assertEquals(442.74, $resultado);

        errores::$error = false;
    }



}

