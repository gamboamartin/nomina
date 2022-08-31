<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\calcula_imss;
use models\calculo_isr;
use models\fc_cfd_partida;
use models\fc_factura;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use stdClass;


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
       // $calculo = new liberator($calculo);
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


}

