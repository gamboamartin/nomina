<?php
namespace gamboamartin\nomina\tests\controllers;

use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\xml_nom;
use gamboamartin\test\liberator;
use gamboamartin\test\test;


use gamboamartin\nomina\models\nom_nomina;
use stdClass;
use gamboamartin\nomina\tests\base_test;


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
        $xml_nom = new liberator($xml_nom);

        $fc_factura_id = 1;
        $link = $this->link;


        $del = (new base_test())->del_cat_sat_moneda($link);
        if(errores::$error) {
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_tipo_de_comprobante($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_metodo_pago($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_fc_partida($link);
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

    public function test_data_cfdi_base(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();

        $xml_nom = new liberator($xml_nom);


        $del = (new base_test())->del_cat_sat_moneda($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_metodo_pago($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_fc_factura($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_com_cliente($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_org_empresa($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_fc_csd($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_fc_partida($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }

        $fc_factura_id = 1;
        $link = $this->link;
        $resultado = $xml_nom->data_cfdi_base($fc_factura_id, $link);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->fc_factura->fc_factura_id);

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


        $del = (new base_test())->del_org_empresa($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_org_clasificacion_dep($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_cat_sat_tipo_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_com_producto($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_com_sucursal($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_im_uma($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_isr($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_subsidio($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_nom_percepcion($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_com_cliente($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_em_rel_empleado_sucursal($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_subsidio($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }




        $alta = (new base_test())->alta_nom_nomina(link: $this->link, em_empleado_codigo: '5',
            nom_percepcion_codigo: 3, nom_percepcion_codigo_bis: '3', nom_percepcion_descripcion: 3, nom_percepcion_id: 3);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }


        $fc_factura = new stdClass();
        $fc_factura->dp_cp_descripcion = 'a';
        $fc_factura->fc_factura_folio = 'a';
        $fc_factura->cat_sat_metodo_pago_codigo = 'a';
        $fc_factura->fc_factura_id = $alta->registro['fc_factura_id'];

        $resultado = $xml_nom->data_comprobante($fc_factura, $this->link);



        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(250, $resultado->sub_total);




        errores::$error = false;
    }

    public function test_data_emisor(){

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();
        $xml_nom = new liberator($xml_nom);

        $fc_factura = new stdClass();
        $fc_factura->org_empresa_rfc = 'ABAC152512554';
        $fc_factura->org_empresa_razon_social = 'x';
        $fc_factura->cat_sat_regimen_fiscal_codigo = '011';
        $resultado = $xml_nom->data_emisor($fc_factura);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('ABAC152512554', $resultado->rfc);
        $this->assertEquals('x', $resultado->nombre);
        $this->assertEquals('011', $resultado->regimen_fiscal);
        errores::$error = false;
    }

    public function test_data_receptor(){

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();
        //$xml_nom = new liberator($xml_nom);

        $com_sucursal = new stdClass();
        $fc_factura = new stdClass();
        $fc_factura->com_cliente_rfc = 'AAA010101AAA';
        $fc_factura->com_cliente_razon_social = 'a';

        $com_sucursal->dp_cp_descripcion = '04451';
        $com_sucursal->cat_sat_regimen_fiscal_codigo = '451';

        $resultado = $xml_nom->data_receptor($com_sucursal, $fc_factura);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('AAA010101AAA', $resultado->rfc);
        $this->assertEquals('a', $resultado->nombre);
        $this->assertEquals('04451', $resultado->domicilio_fiscal_receptor);
        $this->assertEquals('451', $resultado->regimen_fiscal_receptor);
        errores::$error = false;
    }

    public function test_emisor(){

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();
        $xml_nom = new liberator($xml_nom);

        $fc_factura_id = 1;
        $link = $this->link;

        $del = (new base_test())->del_cat_sat_moneda($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_metodo_pago($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new \gamboamartin\facturacion\tests\base_test())->del_fc_factura($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new \gamboamartin\facturacion\tests\base_test())->alta_fc_factura($link);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }

        $resultado = $xml_nom->emisor($fc_factura_id, $link);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('EKU9003173C9', $resultado->rfc);
        $this->assertEquals('ESCUELA KEMPER URGATE', $resultado->nombre);
        $this->assertEquals('021', $resultado->regimen_fiscal);
        errores::$error = false;
    }

    public function test_receptor(){

        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();
        $xml_nom = new liberator($xml_nom);


        $com_sucursal_id = 1;
        $fc_factura_id = 1;


        $del = (new base_test())->del_fc_factura($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_nom_percepcion($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $fc_factura_id = $alta->registro['fc_factura_id'];

        $resultado = $xml_nom->receptor($com_sucursal_id, $fc_factura_id, $this->link);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('MOFY900516NL1', $resultado->rfc);
        $this->assertEquals('YADIRA MAGALY MONTAÑEZ FELIX', $resultado->nombre);
        $this->assertEquals('601', $resultado->regimen_fiscal_receptor);
        $this->assertEquals('44520', $resultado->domicilio_fiscal_receptor);
        errores::$error = false;
    }

    public function test_xml(){

        errores::$error = false;

        $_GET['seccion'] = 'nom_nomina';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $xml_nom = new xml_nom();
        //$xml_nom = new liberator($xml_nom);
        $nom_nomina = new stdClass();

        $del = (new base_test())->del_fc_factura($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $nom_nomina_rs = (new nom_nomina($this->link))->registro($alta->registro_id);
        if(errores::$error){
            $error = (new errores())->error('Error al obtener', $nom_nomina_rs);
            print_r($error);
            exit;
        }

        $nom_nomina->fc_factura_id = $nom_nomina_rs['fc_factura_id'];
        $nom_nomina->nom_nomina_id = 1;
        $nom_nomina->cat_sat_tipo_nomina_codigo = 1;
        $nom_nomina->nom_nomina_fecha_pago = '2022-01-01';
        $nom_nomina->nom_nomina_fecha_inicial_pago = '2022-01-01';
        $nom_nomina->nom_nomina_fecha_final_pago = '2022-01-01';
        $nom_nomina->em_empleado_fecha_inicio_rel_laboral = '2022-01-01';
        $nom_nomina->nom_nomina_num_dias_pagados = '1';
        $nom_nomina->im_registro_patronal_descripcion = '1';
        $nom_nomina->em_empleado_nss = '1';
        $nom_nomina->em_empleado_curp = '1';
        $nom_nomina->cat_sat_tipo_contrato_nom_codigo = '1';
        $nom_nomina->cat_sat_tipo_jornada_nom_codigo = '1';
        $nom_nomina->cat_sat_tipo_regimen_nom_codigo = '1';
        $nom_nomina->em_empleado_codigo = '1';
        $nom_nomina->org_departamento_descripcion = '1';
        $nom_nomina->org_puesto_descripcion = '1';
        $nom_nomina->em_clase_riesgo_codigo = '1';
        $nom_nomina->cat_sat_periodicidad_pago_nom_codigo = '1';
        $nom_nomina->em_cuenta_bancaria_clabe = '1';
        $nom_nomina->bn_banco_codigo = '1';
        $nom_nomina->em_empleado_salario_diario_integrado = '1';
        $nom_nomina->dp_estado_codigo = '1';

        $link = $this->link;
        $resultado = $xml_nom->xml(link: $link, nom_nomina: $nom_nomina);
        //print_r($resultado);exit;
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }


}

