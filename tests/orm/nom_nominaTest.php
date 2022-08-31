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
use models\fc_partida;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_otro_pago;
use models\nom_par_percepcion;
use stdClass;


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
        $_SESSION['usuario_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nom_par_deduccion = new nom_par_deduccion($this->link);
        $nom_par_percepcion = new nom_par_percepcion($this->link);
        $nom_par_otro_pago = new nom_par_otro_pago($this->link);
        $fc_factura = new fc_factura($this->link);
        $fc_cfd_partida = new fc_partida($this->link);

        $del_nom_par_otro_pago = $nom_par_otro_pago->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $nom_par_otro_pago', data: $del_nom_par_otro_pago);
            print_r($error);
            exit;
        }

        $del_nom_par_percepcion = $nom_par_percepcion->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $nom_par_percepcion', data: $del_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $del_nom_par_deduccion = $nom_par_deduccion->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $nom_par_deduccion', data: $del_nom_par_deduccion);
            print_r($error);
            exit;
        }

        $del_fc_cfd_partida = $fc_cfd_partida->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $del_fc_cfd_partida', data: $del_fc_cfd_partida);
            print_r($error);
            exit;
        }

        $del_nom_nomina = $nomina->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar nom_nomina', data: $del_nom_nomina);
            print_r($error);
            exit;
        }

        $del_fc_factura = $fc_factura->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar fc_factura', data: $del_fc_factura);
            print_r($error);
            exit;
        }

        $del_em_cuenta_bancaria = (new em_cuenta_bancaria($this->link))->elimina_todo();
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
        $alta_em_cuenta_bancaria = (new em_cuenta_bancaria($this->link))->alta_registro($em_cuenta_bancaria);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al dar de alta cuenta', data: $alta_em_cuenta_bancaria);
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

        $nomina->registro = $nom_nomina_ins;

        $resultado = $nomina->alta_bd();


        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);


    }

    public function test_calcula_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_cuota_fija = 25;
        $cuota_excedente = 1;
        $resultado = $nomina->calcula_isr($cuota_excedente, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(26, $resultado);

    }

    public function test_cuota_excedente_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_porcentaje_excedente = 10;
        $diferencia_li = 1000;
        $resultado = $nomina->cuota_excedente_isr($diferencia_li, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(100.000, $resultado);
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

    public function test_genera_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);


        $monto = 1;
        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_limite_inferior = 0.01;
        $row_isr->cat_sat_isr_porcentaje_excedente = 10;
        $row_isr->cat_sat_isr_cuota_fija = 10;
        $resultado = $nomina->genera_isr($monto, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(10.1, $resultado);
        errores::$error = false;
    }



    public function test_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);

        $cat_sat_periodicidad_pago_nom_id = 1;
        $monto = .01;
        $resultado = $nomina->isr($cat_sat_periodicidad_pago_nom_id, $monto);


        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0.0, $resultado);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';


        $cat_sat_periodicidad_pago_nom_id = 1;
        $monto = 179.97	;
        $resultado = $nomina->isr($cat_sat_periodicidad_pago_nom_id, $monto);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(10.57, $resultado);


        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';


        $cat_sat_periodicidad_pago_nom_id = 1;
        $monto = 10685.7	;
        $resultado = $nomina->isr($cat_sat_periodicidad_pago_nom_id, $monto);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(3351.21, $resultado);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';


        $cat_sat_periodicidad_pago_nom_id = 2;
        $monto = 10685.7	;
        $resultado = $nomina->isr($cat_sat_periodicidad_pago_nom_id, $monto);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2104.9, $resultado);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';


        $cat_sat_periodicidad_pago_nom_id = 3;
        $monto = 10685.7	;
        $resultado = $nomina->isr($cat_sat_periodicidad_pago_nom_id, $monto);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1571.43, $resultado);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 1;
        $_GET['session_id'] = '1';


        $cat_sat_periodicidad_pago_nom_id = 4;
        $monto = 10685.7	;
        $resultado = $nomina->isr($cat_sat_periodicidad_pago_nom_id, $monto);


        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(943.46, $resultado);

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
        $fc_factura_modelo = new fc_factura($this->link);
        $fc_partida_modelo = new fc_partida($this->link);
        $nom_par_percepcion_modelo = new nom_par_percepcion($this->link);
        $nom_par_deduccion_modelo = new nom_par_deduccion($this->link);

        $del_nom_par_percepcion = $nom_par_percepcion_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar nom_par_percepcion', data: $del_nom_par_percepcion);
            print_r($error);
            exit;
        }
        $del_nom_par_deduccion = $nom_par_deduccion_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar nom_par_percepcion', data: $del_nom_par_deduccion);
            print_r($error);
            exit;
        }

        $del_nom_nomina = $nomina->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar $nom_nomina', data: $del_nom_nomina);
            print_r($error);
            exit;
        }


        $del_cfd_partida = $fc_partida_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar fc_partida', data: $del_cfd_partida);
            print_r($error);
            exit;
        }

        $del_fc_factura = $fc_factura_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar factura', data: $del_fc_factura);
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

        $alta_nom_nomina = $nomina->alta_registro($nom_nomina);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nomina', data: $alta_nom_nomina);
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
    }



}

