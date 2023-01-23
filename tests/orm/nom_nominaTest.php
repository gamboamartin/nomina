<?php
namespace gamboamartin\nomina\tests\orm;

use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\facturacion\models\fc_partida;
use gamboamartin\test\liberator;
use gamboamartin\test\test;

use gamboamartin\nomina\models\nom_conf_empleado;
use gamboamartin\nomina\models\nom_data_subsidio;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_otro_pago;
use gamboamartin\nomina\models\nom_par_percepcion;
use stdClass;
use gamboamartin\nomina\tests\base_test;


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

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';

    }


    public function test_alta_bd(){
        errores::$error = false;


        $nomina = new nom_nomina($this->link);


        $del = (new base_test())->del_org_clasificacion_dep($this->link);
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

        $del = (new base_test())->del_nom_percepcion($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $alta = (new base_test())->alta_nom_periodo(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_em_empleado(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_conf_empleado(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_rel_empleado_sucursal(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_percepcion(link: $this->link, aplica_subsidio: 'activo', codigo: 2, codigo_bis: 2, descripcion: 2, id: 2);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija: 262.52, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior: 1399.27, limite_superior: 2671.42, porcentaje_excedente: 30);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_subsidio(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_subsidio(link: $this->link, cuota_fija:0, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior: .01, limite_superior: 999999, porcentaje_excedente: 1.92);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_im_uma(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_im_uma(link: $this->link, fecha_fin: '9999-01-01', fecha_inicio: '1900-01-01', monto: 96.22);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $nom_nomina_ins = array();
        $nom_nomina_ins['id'] = 1;
        $nom_nomina_ins['im_registro_patronal_id'] = 1;
        $nom_nomina_ins['em_empleado_id'] = 1;
        $nom_nomina_ins['folio'] = 1;
        $nom_nomina_ins['fecha'] = '2022-01-01';
        $nom_nomina_ins['num_dias_pagados'] = '10';
        $nom_nomina_ins['descuento'] = '0';
        $nom_nomina_ins['cat_sat_periodicidad_pago_nom_id'] = '1';
        $nom_nomina_ins['em_cuenta_bancaria_id'] = '1';
        $nom_nomina_ins['fecha_inicial_pago'] = '2022-01-01';
        $nom_nomina_ins['fecha_final_pago'] = '2022-01-01';
        $nom_nomina_ins['fecha_pago'] = '2022-01-01';
        $nom_nomina_ins['nom_periodo_id'] = 1;
        $nom_nomina_ins['nom_conf_empleado_id'] = 1;
        $nom_nomina_ins['cat_sat_tipo_jornada_nom_id'] = 1;
        $nom_nomina_ins['cat_sat_tipo_nomina_id'] = 1;
        $nom_nomina_ins['dp_calle_pertenece_id'] = 1;

        $nomina->registro = $nom_nomina_ins;

        $resultado = $nomina->alta_bd();
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado->registro_id);
        $this->assertStringContainsStringIgnoringCase('INSERT INTO nom_nomina ',$resultado->sql);


    }

    public function test_aplica_subsidio_percepcion(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);

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

        $del = (new base_test())->del_org_empresa($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
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

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija: 10.57, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior: 179.97, limite_superior: 316.27, porcentaje_excedente: 10.88);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del($this->link, 'gamboamartin\\nomina\\models\\nom_par_percepcion');
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $nom_nomina_id = 1;
        $resultado = $nomina->aplica_subsidio_percepcion($nom_nomina_id);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);

        errores::$error = false;


        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:142.22, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:887.79, limite_superior: 1399.26, porcentaje_excedente: 23.52);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 1000;
        $alta = (new nom_par_percepcion($this->link))->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta percepciones', $alta);
            print_r($error);
            exit;
        }



        $nom_nomina_id = 1;
        $resultado = $nomina->aplica_subsidio_percepcion($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_asigna_campo(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $registro = array();
        $campos_asignar = array('x');
        $campo = 'a';

        $resultado = $nomina->asigna_campo($registro, $campo, $campos_asignar);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_deducciones(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->deducciones($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_existe_deduccion_isr(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $del = (new nom_par_deduccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar deducciones', $del);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $nomina->existe_deduccion_isr($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);
        errores::$error = false;

        $nom_par_deduccion = array();
        $nom_par_deduccion['id'] = 1;
        $nom_par_deduccion['nom_nomina_id'] = 1;
        $nom_par_deduccion['nom_deduccion_id'] = 1;
        $nom_par_deduccion['importe_gravado'] = 100;

        $alta = (new nom_par_deduccion($this->link))->alta_registro($nom_par_deduccion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta deduccion', $alta);
            print_r($error);
            exit;
        }


        $nom_nomina_id = 1;
        $resultado = $nomina->existe_deduccion_isr($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;

    }

    public function test_existe_key_imss(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);


        $partida = array();
        $tabla = 'a';
        $resultado = $nomina->existe_key_imss($partida, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertFalse($resultado);
        errores::$error = false;
        $partida = array();
        $tabla = 'a';
        $partida['a_aplica_imss'] = 'inactivo';
        $resultado = $nomina->existe_key_imss($partida, $tabla);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);
        errores::$error = false;
    }

    public function test_existe_otro_pago_subsidio(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);



        $del = (new base_test())->del_nom_par_otro_pago($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $nomina->existe_otro_pago_subsidio($nom_nomina_id);
        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertNotTrue($resultado);


        errores::$error = false;

        $nom_otro_pago_ins['id'] = 1;
        $nom_otro_pago_ins['nom_nomina_id'] = 1;
        $nom_otro_pago_ins['nom_otro_pago_id'] = 2;
        $alta = (new nom_par_otro_pago($this->link))->alta_registro($nom_otro_pago_ins);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta otro pago', $alta);
            print_r($error);
            exit;
        }



        $nom_nomina_id = 1;
        $resultado = $nomina->existe_otro_pago_subsidio($nom_nomina_id);

        $this->assertIsBool($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertTrue($resultado);

        errores::$error = false;
    }

    public function test_genera_registro_par_percepcion(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);


        $nom_nomina_id = 1;
        $percepcion = array();
        $percepcion['nom_percepcion_id'] = 1;
        $percepcion['nom_conf_percepcion_importe_gravado'] = 1;
        $percepcion['nom_conf_percepcion_importe_exento'] = 1;
        $resultado = $nomina->genera_registro_par_percepcion($nom_nomina_id,$percepcion);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_genera_registros_alta_bd(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina->registro['im_registro_patronal_id'] = 1;
        $nomina->registro['em_empleado_id'] = 1;
        $nomina->registro['nom_conf_empleado_id'] = 1;
        $nomina = new liberator($nomina);


        $resultado = $nomina->genera_registros_alta_bd();
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;

    }

    public function test_genera_valor_campo(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $campos_asignar = array('x');
        $resultado = $nomina->genera_valor_campo($campos_asignar);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('x',$resultado);
        errores::$error = false;
    }

    public function test_get_sucursal_by_empleado(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $del = (new base_test())->del_nom_rel_empleado_sucursal($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }


        $em_empleado_id = 1;
        $resultado = $nomina->get_sucursal_by_empleado($em_empleado_id);


        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error no existe sucursal relacionada',$resultado['mensaje']);

        errores::$error = false;

        $del = (new base_test())->del_com_sucursal($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }



        $alta = (new base_test())->alta_nom_rel_empleado_sucursal($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }

        $em_empleado_id = 1;
        $resultado = $nomina->get_sucursal_by_empleado($em_empleado_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1',$resultado['nom_rel_empleado_sucursal_id']);

        errores::$error = false;
    }

    public function test_otros_pagos(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->otros_pagos($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_partidas(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->partidas($nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertObjectHasAttribute('percepciones', $resultado);
        $this->assertObjectHasAttribute('deducciones', $resultado);
        $this->assertObjectHasAttribute('otros_pagos', $resultado);
        errores::$error = false;
    }

    public function test_percepciones(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);


        $nom_nomina_id = 1;
        $resultado = $nomina->percepciones($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);


        errores::$error = false;
    }

    public function test_total_gravado(): void
    {
        errores::$error = false;


        $nomina = new nom_nomina($this->link);


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

        $del = (new base_test())->del_org_empresa($this->link);
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

        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:10.57, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:179.97, limite_superior: 316.27, porcentaje_excedente: 10.88);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }



        $nom_par_percepcion_modelo = new nom_par_percepcion($this->link);

        $del_nom_par_percepcion = $nom_par_percepcion_modelo->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar nom_par_percepcion', data: $del_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:.41, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:21.21, limite_superior: 179.96, porcentaje_excedente: 6.4);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
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


        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:46.62, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:440.19, limite_superior: 887.78, porcentaje_excedente:21.36);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

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

        $del = (new nom_par_percepcion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:644.17, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:2671.43, limite_superior: 3561.9, porcentaje_excedente:32);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 3000.0000;
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
        $this->assertEquals(3000, $resultado);

        errores::$error = false;
    }

    public function test_total_percepciones_gravado(): void
    {
        errores::$error = false;

        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $del = (new base_test())->del_fc_factura($this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:10.57, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:179.97, limite_superior: 316.27, porcentaje_excedente:10.88);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }


        $nom_nomina_id = 1;

        $resultado = $nomina->total_percepciones_gravado($nom_nomina_id);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(250, $resultado);

        $del = (new base_test())->del_nom_par_percepcion($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $resultado = $nomina->total_percepciones_gravado($nom_nomina_id);
        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0, $resultado);
        errores::$error = false;
    }
    public function test_total_sueldos_gravado(): void
    {
        errores::$error = false;

        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $nom_nomina_id = 0;

        $resultado = $nomina->total_sueldos_gravado(nom_nomina_id: $nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error nomina id debe se ser mayor a 0',
            $resultado['mensaje']);

        errores::$error = false;

        $nom_nomina_id = 1;

        $resultado = $nomina->total_sueldos_gravado(nom_nomina_id: $nom_nomina_id);

        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(0, $resultado);

        errores::$error = false;
    }


    public function test_year(): void
    {
        errores::$error = false;

        $nomina = new nom_nomina($this->link);
        $nomina = new liberator($nomina);

        $fecha = '';

        $resultado = $nomina->year(fecha: $fecha);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha',$resultado['mensaje']);

        errores::$error = false;

        $fecha = 'a';

        $resultado = $nomina->year(fecha: $fecha);
        $this->assertIsArray($resultado);
        $this->assertTrue(errores::$error);
        $this->assertStringContainsStringIgnoringCase('Error al validar fecha',$resultado['mensaje']);

        errores::$error = false;

        $fecha = '2023-01-01';

        $resultado = $nomina->year(fecha: $fecha);
        $this->assertIsInt($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2023, $resultado);

        errores::$error = false;
    }



}

