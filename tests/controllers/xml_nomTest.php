<?php
namespace tests\controllers;


use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_nomina;
use gamboamartin\nomina\controllers\xml_nom;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


use stdClass;
use tests\base_test;


class xml_nomTest extends test {
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

    public function test_comprobante(){

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();
            //$xml_nom = new liberator($xml_nom);

        $fc_factura_id = 1;
        $link = $this->link;

        $del = (new base_test())->del_nom_nomina($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\facturacion\tests\base_test())->del_fc_factura($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new \gamboamartin\facturacion\tests\base_test())->alta_fc_partida($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }



        $resultado = $xml_nom->comprobante($fc_factura_id, $link);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->folio);
        $this->assertEquals(1, $resultado->total);
        $this->assertEquals(1, $resultado->sub_total);
        $this->assertEquals(0, $resultado->descuento);
        errores::$error = false;

    }

    public function test_data_comprobante(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();
        $xml_nom = new liberator($xml_nom);


        $del = (new base_test())->del_nom_conf_empleado($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $del = (new base_test())->del_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_nom_rel_empleado_sucursal($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_nom_periodo($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\empleado\test\base_test())->del_em_empleado($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\im_registro_patronal\test\base_test())->del_im_registro_patronal($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\facturacion\tests\base_test())->del_fc_csd($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\im_registro_patronal\test\base_test())->del_im_conf_pres_empresa($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\organigrama\tests\base_test())->del_org_empresa($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_nom_conf_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_tipo_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\organigrama\tests\base_test())->del_org_clasificacion_dep($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }


        $fc_factura = new stdClass();
        $fc_factura->dp_cp_descripcion = 'a';
        $fc_factura->fc_factura_folio = 'a';
        $fc_factura->fc_factura_id = $alta->registro['fc_factura_id'];

        $resultado = $xml_nom->data_comprobante($fc_factura, $this->link);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(250, $resultado->sub_total);




        errores::$error = false;
    }




}

