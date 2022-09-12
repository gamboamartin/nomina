<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\calcula_imss;
use models\fc_cfd_partida;
use models\fc_factura;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use stdClass;


class calcula_imssTest extends test {
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

    public function test_dias_quincena(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $fecha = '';


        $resultado = $calculo->dias_quincena($fecha);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha', $resultado['mensaje']);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';


        $fecha = '2022-08-15';


        $resultado = $calculo->dias_quincena($fecha);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(15.5,$resultado);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';


        $fecha = '2022-02-15';


        $resultado = $calculo->dias_quincena($fecha);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(14,$resultado);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';


        $fecha = '2022-06-15';


        $resultado = $calculo->dias_quincena($fecha);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(15,$resultado);

        errores::$error = false;
    }


    public function test_imss(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();

        $cat_sat_periodicidad_pago_nom_id = 2;
        $fecha = '2022-01-01';
        $n_dias = 7;
        $sbc = 180;
        $sd = 141.70;

        $resultado = $calculo->imss($cat_sat_periodicidad_pago_nom_id, $fecha, $n_dias, $sbc, $sd);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0.0, $resultado['prestaciones_en_dinero_trabajador']);
        $this->assertEquals(0.0, $resultado['pensionados_beneficiarios']);
        $this->assertEquals(0.0, $resultado['invalidez_vida']);
        $this->assertEquals(0.0, $resultado['cesantia']);
        $this->assertEquals(0.0, $resultado['excedente']);
        $this->assertEquals(0.0, $resultado['total']);
        $this->assertEquals(0.0, $resultado['n_dias_mes']);
        $this->assertEquals(7, $resultado['n_dias']);

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();

        $cat_sat_periodicidad_pago_nom_id = 2;
        $fecha = '2022-01-01';
        $n_dias = 7;
        $sbc = 190;
        $sd = 190;

        $resultado = $calculo->imss($cat_sat_periodicidad_pago_nom_id, $fecha, $n_dias, $sbc, $sd);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(3.33, $resultado['prestaciones_en_dinero_trabajador']);
        $this->assertEquals(4.99, $resultado['pensionados_beneficiarios']);
        $this->assertEquals(8.31, $resultado['invalidez_vida']);
        $this->assertEquals(14.96, $resultado['cesantia']);
        $this->assertEquals(0.0, $resultado['excedente']);
        $this->assertEquals(31.59, $resultado['total']);
        $this->assertEquals(0.0, $resultado['n_dias_mes']);
        $this->assertEquals(7, $resultado['n_dias']);

        $cat_sat_periodicidad_pago_nom_id = 2;
        $fecha = '2022-01-01';
        $n_dias = 15;
        $sbc = 221;
        $sd = 221;

        $resultado = $calculo->imss($cat_sat_periodicidad_pago_nom_id, $fecha, $n_dias, $sbc, $sd);
        $this->assertEquals(8.29, $resultado['prestaciones_en_dinero_trabajador']);
        $this->assertEquals(12.43, $resultado['pensionados_beneficiarios']);
        $this->assertEquals(20.72, $resultado['invalidez_vida']);
        $this->assertEquals(37.29, $resultado['cesantia']);
        $this->assertEquals(0.0, $resultado['excedente']);
        $this->assertEquals(78.73, $resultado['total']);
        $this->assertEquals(0.0, $resultado['n_dias_mes']);
        $this->assertEquals(15, $resultado['n_dias']);



        errores::$error = false;
    }

    public function test_init_base(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $fecha = '';

        $cat_sat_periodicidad_pago_nom_id = 1;
        $n_dias = 10;
        $fecha = '2022-01-01';
        $sbc = 1000;
        $sd = 200;
        $resultado = $calculo->init_base($cat_sat_periodicidad_pago_nom_id, $fecha, $n_dias, $sbc, $sd);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2022,$resultado->year);
        $this->assertEquals(89.62,$resultado->monto_uma);
        $this->assertEquals(10,$resultado->n_dias);
        $this->assertEquals(1000,$resultado->sbc);
        $this->assertEquals(200,$resultado->sd);
        errores::$error = false;
    }

    public function test_n_dias(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $fecha = '';

        $cat_sat_periodicidad_pago_nom_id = 1;
        $n_dias = 1;
        $fecha = '2022-01-01';
        $resultado = $calculo->n_dias($cat_sat_periodicidad_pago_nom_id, $fecha, $n_dias);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1,$resultado);
        errores::$error = false;
    }

    public function test_prestaciones_en_dinero(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $resultado = $calculo->prestaciones_en_dinero();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error total_percepciones debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->total_percepciones = 1;
        $resultado = $calculo->prestaciones_en_dinero();
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_total(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $resultado = $calculo->total();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error prestaciones_en_dinero debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->prestaciones_en_dinero = 1;
        $resultado = $calculo->total();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error pensionados_beneficiarios debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->prestaciones_en_dinero = 1;
        $calculo->pensionados_beneficiarios = 1;
        $resultado = $calculo->total();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error invalidez_vida debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->prestaciones_en_dinero = 1;
        $calculo->pensionados_beneficiarios = 1;
        $calculo->invalidez_vida = 1;
        $resultado = $calculo->total();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error cesantia debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->prestaciones_en_dinero = 1;
        $calculo->pensionados_beneficiarios = 1;
        $calculo->invalidez_vida = 1;
        $calculo->cesantia = 1;
        $resultado = $calculo->total();
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_total_percepciones(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $resultado = $calculo->total_percepciones();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error sbc debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->sbc = 1;
        $resultado = $calculo->total_percepciones();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error n_dias debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->sbc = 1;
        $calculo->n_dias = 1;
        $resultado = $calculo->total_percepciones();
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_valida_exedente(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $resultado = $calculo->valida_exedente();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error uma debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->monto_uma = 1;
        $resultado = $calculo->valida_exedente();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error sbc debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->monto_uma = 1;
        $calculo->sbc = 1;
        $resultado = $calculo->valida_exedente();
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error n_dias debe ser mayor a 0', $resultado['mensaje']);
        errores::$error = false;

        $calculo->monto_uma = 1;
        $calculo->sbc = 1;
        $calculo->n_dias = 1;
        $resultado = $calculo->valida_exedente();
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_valida_imss(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calcula_imss();
        $calculo = new liberator($calculo);

        $fecha = '';
        $n_dias = 0;
        $sbc = 0;
        $sd = 0;

        $resultado = $calculo->valida_imss($fecha, $n_dias, $sbc, $sd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha', $resultado['mensaje']);

        errores::$error = false;

        $fecha = 'a';
        $n_dias = 0;
        $sbc = 0;
        $sd = 0;

        $resultado = $calculo->valida_imss($fecha, $n_dias, $sbc, $sd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha', $resultado['mensaje']);

        errores::$error = false;

        $fecha = '2021-01-01';
        $n_dias = 0;
        $sbc = 0;
        $sd = 0;

        $resultado = $calculo->valida_imss($fecha, $n_dias, $sbc, $sd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar n_dias', $resultado['mensaje']);

        errores::$error = false;

        $fecha = '2021-01-01';
        $n_dias = 1;
        $sbc = 0;
        $sd = 0;

        $resultado = $calculo->valida_imss($fecha, $n_dias, $sbc, $sd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar sbc', $resultado['mensaje']);

        errores::$error = false;

        $fecha = '2021-01-01';
        $n_dias = 1;
        $sbc = 1;
        $sd = 0;

        $resultado = $calculo->valida_imss($fecha, $n_dias, $sbc, $sd);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar $sd', $resultado['mensaje']);

        errores::$error = false;

        $fecha = '2021-01-01';
        $n_dias = 1;
        $sbc = 1;
        $sd = 10;

        $resultado = $calculo->valida_imss($fecha, $n_dias, $sbc, $sd);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }


}

