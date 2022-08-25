<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\test;
use JsonException;
use models\nom_nomina;
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
        $_GET['session_id'] = '1';


        $cat_sat_periodicidad_pago_nom_id = 4;
        $monto = 10685.7	;
        $resultado = $nomina->isr($cat_sat_periodicidad_pago_nom_id, $monto);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(943.46, $resultado);

        errores::$error = false;
    }

    public function test_total_gravado(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 1;
        $_GET['session_id'] = '1';
        $nomina = new nom_nomina($this->link);

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
        $_SESSION['usuario_id'] = 1;
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

