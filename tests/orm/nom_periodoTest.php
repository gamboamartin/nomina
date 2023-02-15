<?php
namespace gamboamartin\nomina\tests\orm;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use gamboamartin\nomina\models\em_cuenta_bancaria;
use gamboamartin\nomina\models\fc_cfd_partida;
use gamboamartin\nomina\models\fc_factura;
use gamboamartin\nomina\models\fc_partida;
use gamboamartin\nomina\models\nom_data_subsidio;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_otro_pago;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\nomina\models\nom_periodo;
use stdClass;


class nom_periodoTest extends test {
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


    public function test_get_empleados(){
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $modelo = new nom_periodo($this->link);

        $im_registro_patronal_id = 1;
        $cat_sat_periodicidad_pago_nom_id = 1;

        $resultado = $modelo->get_empleados($cat_sat_periodicidad_pago_nom_id, $im_registro_patronal_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;

    }

    public function test_genera_registro_nomina_empleado(){
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $modelo = new nom_periodo($this->link);
        $periodo = new liberator($modelo);

        $em_empleado = array();
        $nom_periodo = array();
        $nom_conf_empleado = array();
        $resultado = $periodo->genera_registro_nomina_empleado(em_empleado: $em_empleado, nom_periodo: $nom_periodo,
            nom_conf_empleado: $nom_conf_empleado);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar em_empleado',
            $resultado['mensaje']);

        errores::$error = false;

        $em_empleado['em_registro_patronal_id'] = 1;
        $em_empleado['em_empleado_id'] = 1;

        $nom_periodo = array();

        $nom_conf_empleado = array();

        $resultado = $periodo->genera_registro_nomina_empleado(em_empleado: $em_empleado, nom_periodo: $nom_periodo,
            nom_conf_empleado: $nom_conf_empleado);



        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar nom_periodo',
            $resultado['mensaje']);

        errores::$error = false;

        $em_empleado['im_registro_patronal_id'] = 1;
        $em_empleado['em_empleado_id'] = 1;

        $nom_periodo['nom_periodo_fecha_pago'] = 1;
        $nom_periodo['nom_periodo_cat_sat_periodicidad_pago_nom_id'] = 'a';
        $nom_periodo['nom_periodo_fecha_inicial_pago'] = 'a';
        $nom_periodo['nom_periodo_fecha_final_pago'] = 'a';
        $nom_periodo['cat_sat_periodicidad_pago_nom_n_dias'] = 1;
        $nom_periodo['nom_periodo_id'] = 1;

        $nom_conf_empleado = array();

        $resultado = $periodo->genera_registro_nomina_empleado(em_empleado: $em_empleado, nom_periodo: $nom_periodo,
            nom_conf_empleado: $nom_conf_empleado);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar nom_conf_empleado',
            $resultado['mensaje']);

        errores::$error = false;

        $em_empleado['im_registro_patronal_id'] = 1;
        $em_empleado['em_empleado_id'] = 1;

        $nom_periodo['nom_periodo_fecha_pago'] = 1;
        $nom_periodo['nom_periodo_cat_sat_periodicidad_pago_nom_id'] = 'a';
        $nom_periodo['nom_periodo_fecha_inicial_pago'] = 'a';
        $nom_periodo['nom_periodo_fecha_final_pago'] = 'a';
        $nom_periodo['cat_sat_periodicidad_pago_nom_n_dias'] = 1;
        $nom_periodo['nom_periodo_id'] = 1;

        $nom_conf_empleado['nom_conf_empleado_id'] = 1;
        $nom_conf_empleado['em_cuenta_bancaria_id'] = 1;

        $resultado = $periodo->genera_registro_nomina_empleado(em_empleado: $em_empleado, nom_periodo: $nom_periodo,
            nom_conf_empleado: $nom_conf_empleado);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }

}

