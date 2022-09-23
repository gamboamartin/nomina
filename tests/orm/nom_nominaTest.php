<?php
namespace tests\orm;

use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\facturacion\models\fc_partida;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use models\nom_conf_empleado;
use models\nom_data_subsidio;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_otro_pago;
use models\nom_par_percepcion;
use stdClass;
use tests\base_test;


class nom_nominaTest extends test {
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


    public function test_alta_bd(){
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);

        $del = (new base_test())->del_org_departamento(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_fc_csd(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_tipo_nomina(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $alta = (new base_test())->alta_em_empleado(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_conf_empleado(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_rel_empleado_sucursal(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_periodo(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }



        $nom_nomina_ins = array();
        $nom_nomina_ins['id'] = 1;
        $nom_nomina_ins['im_registro_patronal_id'] = 1;
        $nom_nomina_ins['em_empleado_id'] = 1;
        $nom_nomina_ins['folio'] = 1;
        $nom_nomina_ins['fecha'] = '2022-01-01';
        $nom_nomina_ins['num_dias_pagados'] = '10';
        $nom_nomina_ins['descuento'] = '0';
        $nom_nomina_ins['cat_sat_periodicidad_pago_nom_id'] = '1';
        $nom_nomina_ins['em_cuenta_bancaria_id'] = '1';
        $nom_nomina_ins['fecha_inicial_pago'] = '2022-01-01';
        $nom_nomina_ins['fecha_final_pago'] = '2022-01-01';
        $nom_nomina_ins['nom_periodo_id'] = 1;
        $nom_nomina_ins['nom_conf_empleado_id'] = 1;
        $nom_nomina_ins['cat_sat_tipo_jornada_nom_id'] = 1;
        $nom_nomina_ins['cat_sat_tipo_nomina_id'] = 1;
        $nom_nomina_ins['dp_calle_pertenece_id'] = 1;

        $nomina->registro = $nom_nomina_ins;

        $resultado = $nomina->alta_bd();


        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


    }

    public function test_aplica_subsidio_percepcion(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);

        $del = (new base_test())->del_cat_sat_tipo_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $del = (new base_test())->del_org_empresa($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del($this->link, 'models\\nom_par_percepcion');
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $nom_nomina_id = 1;
        $resultado = $nomina->aplica_subsidio_percepcion($nom_nomina_id);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;
        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 1000;
        $alta = (new nom_par_percepcion($this->link))->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta percepciones', $alta);
            print_r($error);
            exit;
        }



        $nom_nomina_id = 1;
        $resultado = $nomina->aplica_subsidio_percepcion($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_deducciones(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->deducciones($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_existe_deduccion_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $del = (new nom_par_deduccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar deducciones', $del);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $nomina->existe_deduccion_isr($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);
        errores::$error = false;

        $nom_par_deduccion = array();
        $nom_par_deduccion['id'] = 1;
        $nom_par_deduccion['nom_nomina_id'] = 1;
        $nom_par_deduccion['nom_deduccion_id'] = 1;
        $nom_par_deduccion['importe_gravado'] = 100;

        $alta = (new nom_par_deduccion($this->link))->alta_registro($nom_par_deduccion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta deduccion', $alta);
            print_r($error);
            exit;
        }


        $nom_nomina_id = 1;
        $resultado = $nomina->existe_deduccion_isr($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

    }

    public function test_existe_key_imss(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);


        $partida = array();
        $tabla = 'a';
        $resultado = $nomina->existe_key_imss($partida, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertFalse($resultado);
        errores::$error = false;
        $partida = array();
        $tabla = 'a';
        $partida['a_aplica_imss'] = 'inactivo';
        $resultado = $nomina->existe_key_imss($partida, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_existe_otro_pago_subsidio(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);


        $del = (new nom_data_subsidio($this->link))->elimina_todo();
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

        $nom_nomina_id = 1;
        $resultado = $nomina->existe_otro_pago_subsidio($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);


        errores::$error = false;

        $nom_otro_pago_ins['id'] = 1;
        $nom_otro_pago_ins['nom_nomina_id'] = 1;
        $nom_otro_pago_ins['nom_otro_pago_id'] = 2;
        $alta = (new nom_par_otro_pago($this->link))->alta_registro($nom_otro_pago_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta otro pago', $alta);
            print_r($error);
            exit;
        }



        $nom_nomina_id = 1;
        $resultado = $nomina->existe_otro_pago_subsidio($nom_nomina_id);

        errores::$error = false;
    }

    public function test_genera_registro_par_percepcion(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);


        $nom_nomina_id = 1;
        $percepcion = array();
        $percepcion['nom_percepcion_id'] = 1;
        $percepcion['nom_conf_percepcion_importe_gravado'] = 1;
        $percepcion['nom_conf_percepcion_importe_exento'] = 1;
        $resultado = $nomina->genera_registro_par_percepcion($nom_nomina_id,$percepcion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_genera_valor_campo(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $campos_asignar = array('x');
        $resultado = $nomina->genera_valor_campo($campos_asignar);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;
    }

    public function test_get_sucursal_by_empleado(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);


        $del = (new base_test())->del_org_departamento($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_fc_csd($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }



        $em_empleado_id = 1;
        $resultado = $nomina->get_sucursal_by_empleado($em_empleado_id);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe sucursal relacionada',$resultado['mensaje']);

        errores::$error = false;

        $alta = (new base_test())->alta_em_empleado($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_rel_empleado_sucursal($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }

        $em_empleado_id = 1;
        $resultado = $nomina->get_sucursal_by_empleado($em_empleado_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['nom_rel_empleado_sucursal_id']);

        errores::$error = false;
    }

    public function test_otros_pagos(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->otros_pagos($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_partidas(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->partidas($nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertObjectHasAttribute('percepciones', $resultado);
        $this->assertObjectHasAttribute('deducciones', $resultado);
        $this->assertObjectHasAttribute('otros_pagos', $resultado);
        errores::$error = false;
    }

    public function test_percepciones(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->percepciones($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_total_gravado(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);


        $del = (new base_test())->del_cat_sat_tipo_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }



        $del = (new base_test())->del_org_empresa($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }


        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }



        $nom_par_percepcion_modelo = new nom_par_percepcion($this->link);

        $del_nom_par_percepcion = $nom_par_percepcion_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar nom_par_percepcion', data: $del_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 100;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $alta_nom_par_percepcion = $nom_par_percepcion_modelo->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nom_par_percepcion', data: $alta_nom_par_percepcion);
            print_r($error);
            exit;
        }


        $nom_nomina_id = 1;

        $resultado = $nomina->total_gravado($nom_nomina_id);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(100.00, $resultado);


        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';


        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 2;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 500.354;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $alta_nom_par_percepcion = $nom_par_percepcion_modelo->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nom_par_percepcion', data: $alta_nom_par_percepcion);
            print_r($error);
            exit;
        }


        $nom_nomina_id = 1;

        $resultado = $nomina->total_gravado($nom_nomina_id);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(600.35, $resultado);

        errores::$error = false;

        $del = (new nom_par_percepcion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 3000.0000;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $alta_nom_par_percepcion = $nom_par_percepcion_modelo->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nom_par_percepcion', data: $alta_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;

        $resultado = $nomina->total_gravado($nom_nomina_id);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(3000, $resultado);

        errores::$error = false;
    }



}

