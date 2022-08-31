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
use models\calculo_subsidio;
use models\fc_cfd_partida;
use models\fc_factura;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use stdClass;


class calculo_subsidioTest extends test {
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

    public function test_calcula_subsidio_nomina(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_subsidio();
        //$calculo = new liberator($calculo);

        $modelo = new nom_par_percepcion($this->link);

        $del_percepcion = $modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar percepcion', $del_percepcion);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 100;

        $alta_percepcion = $modelo->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta percepcion', $alta_percepcion);
            print_r($error);
            exit;
        }

        $partida_percepcion_id = 1;
        $resultado = $calculo->calcula_subsidio_nomina($modelo, $partida_percepcion_id);

        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('13.38', $resultado);


        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 2;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 100;

        $alta_percepcion = $modelo->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta percepcion', $alta_percepcion);
            print_r($error);
            exit;
        }

        $resultado = $calculo->calcula_subsidio_nomina($modelo, $partida_percepcion_id);


        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('9.69', $resultado);
    }

    public function test_filtro_especial_subsidio(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_subsidio();
        $calculo = new liberator($calculo);

        $monto = 0.01;
        $resultado = $calculo->filtro_especial_subsidio($monto);

        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('>=', $resultado[0][date('Y-m-d')]['operador']);
        $this->assertEquals('cat_sat_subsidio.fecha_inicio', $resultado[0][date('Y-m-d')]['valor']);
        $this->assertEquals('AND', $resultado[0][date('Y-m-d')]['comparacion']);
        $this->assertEquals(1, $resultado[0][date('Y-m-d')]['valor_es_campo']);
        errores::$error = false;

    }

    public function test_genera_subsidio(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_subsidio();
        $calculo = new liberator($calculo);

        $row_subsidio = new stdClass();
        $row_subsidio->cat_sat_subsidio_cuota_fija = 10;

        $resultado = $calculo->genera_subsidio($row_subsidio);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(10, $resultado);
        errores::$error = false;
    }

    public function test_get_subsidio(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_subsidio();
        $calculo = new liberator($calculo);

        $monto = 0.01;
        $cat_sat_periodicidad_pago_nom_id = 1;

        $resultado = $calculo->get_subsidio($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado->cat_sat_subsidio_id);
        $this->assertEquals('0.01', $resultado->cat_sat_subsidio_limite_inferior);
        $this->assertEquals('58.19', $resultado->cat_sat_subsidio_limite_superior);
        $this->assertEquals('13.39', $resultado->cat_sat_subsidio_cuota_fija);

        errores::$error = false;
    }

    public function test_subsidio(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_subsidio();
        $calculo = new liberator($calculo);

        $monto = .01;
        $cat_sat_periodicidad_pago_nom_id = 1;

        $resultado = $calculo->subsidio($cat_sat_periodicidad_pago_nom_id, $this->link, $monto);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('13.39', $resultado);

        errores::$error = false;
    }

    public function test_subsidio_nomina(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_subsidio();
        $calculo = new liberator($calculo);

        $nom_nomina_id = 1;
        $total_gravado = 1;
        $resultado = $calculo->subsidio_nomina($this->link, $nom_nomina_id, $total_gravado);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('13.39', $resultado);
        errores::$error = false;
    }

    public function test_subsidio_total_nomina_por_percepcion(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $calculo = new calculo_subsidio();
        $calculo = new liberator($calculo);
        $modelo = new nom_par_percepcion($this->link);

        $partida_percepcion_id = 1;
        $total_gravado = 0.01;

        $resultado = $calculo->subsidio_total_nomina_por_percepcion($modelo, $partida_percepcion_id, $total_gravado);
        $this->assertIsNumeric($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('13.39', $resultado);
        errores::$error = false;
    }


}

