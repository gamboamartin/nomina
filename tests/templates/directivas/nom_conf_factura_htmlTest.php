<?php
namespace gamboamartin\nomina\tests\templates\directivas;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\template_1\html;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use html\nom_conf_factura_html;
use JsonException;
use gamboamartin\nomina\models\em_cuenta_bancaria;
use gamboamartin\nomina\models\fc_cfd_partida;
use gamboamartin\nomina\models\fc_factura;
use gamboamartin\nomina\models\fc_partida;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_percepcion;
use stdClass;


class nom_conf_factura_htmlTest extends test {
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


    public function test_btn_next_action(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

        $html = new html();
        $html = new nom_conf_factura_html($html);

        $html = new liberator($html);

        $label = 'a';
        $value = 'b';
        $resultado = $html->btn_next_action($label, $value);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals("<div class='col-md-12'><button type='submit' class='btn btn-info btn-guarda col-md-12' name='btn_action_next' value='b'>a</button></div>", $resultado);

        errores::$error = false;
    }





}

