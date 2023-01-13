<?php
namespace gamboamartin\nomina\tests\templates\directivas;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\template_1\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use html\nom_conf_factura_html;
use html\nom_nomina_html;
use html\nom_periodo_html;
use JsonException;
use gamboamartin\nomina\models\em_cuenta_bancaria;
use gamboamartin\nomina\models\fc_cfd_partida;
use gamboamartin\nomina\models\fc_factura;
use gamboamartin\nomina\models\fc_partida;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\nomina\models\nom_periodo;
use stdClass;


class nom_nomina_htmlTest extends test {
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


    public function test_input_num_dias_pagados(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $html = new html();
        $html = new nom_nomina_html($html);

        $html = new liberator($html);

        $cols = 1;
        $row_upd = new stdClass();
        $value_vacio = false;

        $resultado = $html->input_num_dias_pagados($cols, $row_upd, $value_vacio);


        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase("trol-label' for='num_dias_pagados'>Nº dias pagados</", $resultado);

        errores::$error = false;
    }





}

