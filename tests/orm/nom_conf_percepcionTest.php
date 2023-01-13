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
use gamboamartin\nomina\models\nom_conf_empleado;
use gamboamartin\nomina\models\nom_conf_percepcion;
use gamboamartin\nomina\models\nom_data_subsidio;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_otro_pago;
use gamboamartin\nomina\models\nom_par_percepcion;
use stdClass;
use gamboamartin\nomina\tests\base_test;


class nom_conf_percepcionTest extends test {
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


    public function test_aplica_septimo_dia(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $modelo = new nom_conf_percepcion($this->link);


        $nom_conf_nomina_id = -1;
        $resultado = $modelo->aplica_septimo_dia($nom_conf_nomina_id);


        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nom_conf_nomina_id debe ser mayor a 0',$resultado['mensaje']);

        errores::$error = false;

        $del = (new base_test())->del_nom_conf_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $nom_conf_nomina_id = 1;
        $resultado = $modelo->aplica_septimo_dia($nom_conf_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;

        $del = (new base_test())->del_nom_percepcion($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_conf_percepcion(link: $this->link, nom_percepcion_aplica_septimo_dia: 'activo', nom_percepcion_id: 2);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }
        $nom_conf_nomina_id = 1;
        $resultado = $modelo->aplica_septimo_dia($nom_conf_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);



        errores::$error = false;
    }



}

