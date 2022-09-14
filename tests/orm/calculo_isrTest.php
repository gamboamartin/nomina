<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\adm_dia;
use models\calcula_imss;
use models\calculo_isr;
use models\fc_cfd_partida;
use models\fc_factura;
use models\fc_partida;
use models\nom_data_subsidio;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_otro_pago;
use models\nom_par_percepcion;
use models\nom_periodo;
use stdClass;
use tests\base_test;


class calculo_isrTest extends test {
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

    public function test_calcula_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);

        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_cuota_fija = 25;
        $cuota_excedente = 1;
        $resultado = $calculo->calcula_isr($cuota_excedente, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(26, $resultado);

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



        $calculo = new calculo_isr();


        $del = (new base_test())->del_cat_sat_tipo_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_em_empleado($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_fc_factura($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_nom_conf_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_nom_periodo($this->link);
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
        $del = (new base_test())->del($this->link, 'models\\nom_par_percepcion');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
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
        $resultado = $calculo->calcula_isr_nomina(modelo: $percepcion, partida_percepcion_id: $nom_par_percepcion_id);

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
        $resultado = $calculo->calcula_isr_nomina(modelo: $percepcion, partida_percepcion_id: $nom_par_percepcion_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsFloat($resultado);
        $this->assertEquals(442.74, $resultado);

        errores::$error = false;
    }

    public function test_calcula_isr_por_nomina(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_isr();
        //$calculo = new liberator($calculo);

        $del = (new nom_par_percepcion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar nomina percepcion', data: $del);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 1000;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar nomina percepcion', data: $alta_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $calculo->calcula_isr_por_nomina($this->link, $nom_nomina_id);
        $this->assertNotTrue(errores::$error);
        $this->assertIsFloat($resultado);
        $this->assertEquals(168.61, $resultado);
        errores::$error = false;
    }

    public function test_cuota_excedente_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);

        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_porcentaje_excedente = 10;
        $diferencia_li = 1000;
        $resultado = $calculo->cuota_excedente_isr($diferencia_li, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(100.000, $resultado);
        errores::$error = false;
    }

    public function test_diferencia_li(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);
        $monto = 50;
        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_limite_inferior = 10;
        $resultado = $calculo->diferencia_li($monto, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(40.0, $resultado);

        errores::$error = false;


        $monto = 3000;
        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_limite_inferior = 2699.41;
        $resultado = $calculo->diferencia_li($monto, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(300.59, $resultado);


        errores::$error = false;
    }

    public function test_filtro_especial_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $monto = 10;
        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);
        $resultado = $calculo->filtro_especial_isr($monto);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('>=', $resultado[0][date('Y-m-d')]['operador']);
        $this->assertEquals('cat_sat_isr.fecha_inicio', $resultado[0][date('Y-m-d')]['valor']);
        $this->assertEquals('AND', $resultado[0][date('Y-m-d')]['comparacion']);
        $this->assertEquals(true, $resultado[0][date('Y-m-d')]['valor_es_campo']);

        $this->assertEquals('>=', $resultado[2][date(10)]['operador']);

        errores::$error = false;
    }

    public function test_genera_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);


        $monto = 1;
        $row_isr = new stdClass();
        $row_isr->cat_sat_isr_limite_inferior = 0.01;
        $row_isr->cat_sat_isr_porcentaje_excedente = 10;
        $row_isr->cat_sat_isr_cuota_fija = 10;
        $resultado = $calculo->genera_isr($monto, $row_isr);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(10.1, $resultado);
        errores::$error = false;
    }

    public function test_get_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);
        $monto = 0.01;
        $cat_sat_periodicidad_pago_nom_id = 1;
        $resultado = $calculo->get_isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->cat_sat_isr_id);

        errores::$error = false;

        $monto = 3000;
        $cat_sat_periodicidad_pago_nom_id = 3;
        $resultado = $calculo->get_isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2699.41, $resultado->cat_sat_isr_limite_inferior);
        $this->assertEquals(10.88, $resultado->cat_sat_isr_porcentaje_excedente);


        errores::$error = false;
    }

    public function test_isr(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);

        $cat_sat_periodicidad_pago_nom_id = 1;
        $monto = .01;
        $resultado = $calculo->isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);


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
        $resultado = $calculo->isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
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
        $resultado = $calculo->isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
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
        $resultado = $calculo->isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
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
        $resultado = $calculo->isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
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
        $resultado = $calculo->isr($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);


        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(943.46, $resultado);

        errores::$error = false;
    }

    public function test_isr_nomina(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);


        $total_gravado = 1;
        $nom_nomina_id = 1;
        $resultado = $calculo->isr_nomina($this->link, $nom_nomina_id, $total_gravado);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0.02, $resultado);
        errores::$error = false;
    }

    public function test_isr_total_nomina_por_percepcion(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calculo_isr();
        $calculo = new liberator($calculo);

        $modelo = new nom_par_percepcion($this->link);

        $nom_par_percepcion_del = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar percepciones', $nom_par_percepcion_del);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 1;
        $nom_par_percepcion_alta = $modelo->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta percepcion', $nom_par_percepcion_alta);
            print_r($error);
            exit;
        }

        $partida_percepcion_id = 1;
        $total_gravado = 100;
        $resultado = $calculo->isr_total_nomina_por_percepcion($modelo, $partida_percepcion_id, $total_gravado);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(5.45, $resultado);
        errores::$error = false;
    }


}

