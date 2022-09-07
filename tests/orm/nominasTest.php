<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\calcula_imss;
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
use models\nom_rel_empleado_sucursal;
use models\nominas;
use stdClass;


class nominasTest extends test {
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

    public function test_asigna_codigo_partida(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_deduccion($this->link);
        $nominas = new liberator($nominas);


        $registro = array();
        $registro['nom_nomina_id'] = '1';
        $resultado = $nominas->asigna_codigo_partida($registro);



        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['nom_nomina_id']);
        errores::$error = false;
    }

    public function test_base_calculo_impuesto(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        //$nominas = new liberator($nominas);

        $del = (new nom_data_subsidio($this->link))->elimina_todo();
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

        $del = (new nom_par_otro_pago($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar cuenta_bancaria', $del);
            print_r($error);
            exit;
        }
        $r_del_nom_par_percepcion = $nominas->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar percepcion', $r_del_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $del = (new nom_nomina($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar nomina', $del);
            print_r($error);
            exit;
        }



        $del = (new em_cuenta_bancaria($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar cuenta_bancaria', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_conf_empleado($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar empleado', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_rel_empleado_sucursal($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar empleado', $del);
            print_r($error);
            exit;
        }

        $del = (new em_empleado($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar empleado', $del);
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
        $em_empleado_ins['salario_diario'] =250;
        $em_empleado_ins['salario_diario_integrado'] =250;
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

        $em_nomina_ins = array();
        $em_nomina_ins['id'] = 1;
        $em_nomina_ins['im_registro_patronal_id'] = 1;
        $em_nomina_ins['em_empleado_id'] = 1;
        $em_nomina_ins['folio'] = 1;
        $em_nomina_ins['fecha'] = '2022-01-01';
        $em_nomina_ins['fecha_inicial_pago'] = '2022-01-01';
        $em_nomina_ins['fecha_final_pago'] = '2022-01-01';
        $em_nomina_ins['num_dias_pagados'] = 15;
        $em_nomina_ins['descuento'] = 0;
        $em_nomina_ins['cat_sat_periodicidad_pago_nom_id'] = 1;
        $em_nomina_ins['em_cuenta_bancaria_id'] = 1;
        $em_nomina_ins['nom_periodo_id'] = 1;

        $alta = (new nom_nomina($this->link))->alta_registro($em_nomina_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta ', $alta);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 100;
        $r_alta_nom_par_percepcion = $nominas->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta percepcion', $r_alta_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $partida_percepcion_id = 1;

        $resultado = $nominas->base_calculo_impuesto($partida_percepcion_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado->nom_par_percepcion_id);
        $this->assertEquals('Sueldos, Salarios Rayas y Jornales', $resultado->nom_par_percepcion_descripcion);
        $this->assertEquals('1', $resultado->nom_nomina_id);
        $this->assertEquals('1', $resultado->nom_percepcion_id);
        $this->assertEquals('Diario', $resultado->cat_sat_periodicidad_pago_nom_descripcion);
        $this->assertEquals('1', $resultado->em_empleado_id);
        $this->assertEquals('250', $resultado->em_empleado_salario_diario_integrado);
        errores::$error = false;
    }


    public function test_existe_data_deduccion(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_deduccion($this->link);
        //$nominas = new liberator($nominas);

        $del = (new nom_data_subsidio($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar data subsidio', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_deduccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar deduccion', $del);
            print_r($error);
            exit;
        }

        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->existe_data_deduccion($nom_deduccion_id, $nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->filtro['nom_nomina.id']);
        $this->assertEquals(1, $resultado->filtro['nom_deduccion.id']);
        $this->assertFalse( $resultado->existe);

        errores::$error = false;


        $nom_par_deduccion = array();
        $nom_par_deduccion['id'] = 1;
        $nom_par_deduccion['nom_nomina_id'] = 1;
        $nom_par_deduccion['nom_deduccion_id'] = 1;
        $nom_par_deduccion['importe_exento'] = 100;

        $alta = (new nom_par_deduccion($this->link))->alta_registro($nom_par_deduccion);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar deduccion', $alta);
            print_r($error);
            exit;
        }

        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->existe_data_deduccion($nom_deduccion_id, $nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->filtro['nom_nomina.id']);
        $this->assertEquals(1, $resultado->filtro['nom_deduccion.id']);
        $this->assertTrue( $resultado->existe);
        errores::$error = false;
    }

    public function test_data_deduccion(): void{
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        //$nominas = new liberator($nominas);

        $monto_exento = 0;
        $monto_gravado = 0;
        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->data_deduccion($monto_exento, $monto_gravado, $nom_deduccion_id, $nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->row_ins['nom_nomina_id']);
        $this->assertEquals(1, $resultado->row_ins['nom_deduccion_id']);
        $this->assertEquals(0, $resultado->row_ins['importe_gravado']);
        $this->assertEquals(0, $resultado->row_ins['importe_exento']);
        $this->assertTrue($resultado->existe);

        errores::$error = false;
    }

    public function test_filtro_partida(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        $nominas = new liberator($nominas);

        $tabla = 'x';
        $nom_nomina_id = 1;
        $id = 1;

        $resultado = $nominas->filtro_partida($id, $nom_nomina_id, $tabla);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['nom_nomina.id']);
        $this->assertEquals(1, $resultado['x.id']);

        errores::$error = false;
    }

    public function test_get_by_nomina(): void{
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        //$nominas = new liberator($nominas);


        $nom_nomina_id = 1;

        $resultado = $nominas->get_by_nomina($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_nom_par_deduccion_aut(): void{
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        $nominas = new liberator($nominas);

        $monto_gravado = -0.000;
        $monto_exento = 0;
        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->nom_par_deduccion_aut($monto_exento, $monto_gravado, $nom_deduccion_id, $nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['nom_nomina_id']);
        $this->assertEquals(1, $resultado['nom_deduccion_id']);
        $this->assertEquals(0, $resultado['importe_gravado']);
        $this->assertEquals(0, $resultado['importe_exento']);
        errores::$error = false;

    }






}

