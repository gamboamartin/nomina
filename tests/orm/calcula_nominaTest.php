<?php
namespace gamboamartin\nomina\tests\orm;

use gamboamartin\errores\errores;
use gamboamartin\test\test;
use gamboamartin\nomina\models\calcula_nomina;
use gamboamartin\nomina\models\nom_par_percepcion;
use stdClass;
use gamboamartin\nomina\tests\base_test;


class calcula_nominaTest extends test {
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

    public function test_antiguedad_empleado(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calcula_nomina();
        $fecha_final_pago = '2022-02-02';
        $fecha_inicio_rel_laboral = '2022-02-02';
        $resultado = $calculo->antiguedad_empleado($fecha_final_pago, $fecha_inicio_rel_laboral);

        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('P0W',$resultado);

        errores::$error = false;

        $fecha_final_pago = '2021-12-24';
        $fecha_inicio_rel_laboral = '2015-01-01';
        $resultado = $calculo->antiguedad_empleado($fecha_final_pago, $fecha_inicio_rel_laboral);
        $this->assertIsString($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('P364W',$resultado);


        errores::$error = false;
    }

    public function test_calcula_impuestos_netos_por_nomina(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calcula_nomina();
        //$calculo = new liberator($calculo);

        $link = $this->link;

        $del = (new base_test())->del_nom_incidencia($this->link);
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

        $del = (new base_test())->del_org_empresa($this->link);
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

        $del = (new base_test())->del_cat_sat_subsidio($this->link);
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

        $alta = (new base_test())->alta_nom_nomina(
            link: $this->link, cat_sat_isr_cuota_fija:10.57 , cat_sat_isr_limite_inferior: 179.97,
            cat_sat_isr_porcentaje_excedente: 10.89);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $calculo->calcula_impuestos_netos_por_nomina($link, $nom_nomina_id);

        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(18.20,$resultado->isr_neto);
        $this->assertEquals(0,$resultado->subsidio_neto);

        errores::$error = false;

    }

    public function test_calculos(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calcula_nomina();
        //$calculo = new liberator($calculo);

        $link = $this->link;


        $del = (new base_test())->del_fc_factura($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar nomina', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_im_uma($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar nomina', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_isr($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar nomina', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_subsidio($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar nomina', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_im_uma(link: $link, fecha_fin: '9999-01-01', fecha_inicio: '1900-01-01', monto: 1);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar uma', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_subsidio($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar isr', $alta);
            print_r($error);
            exit;
        }
        $alta = (new base_test())->alta_cat_sat_isr($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar isr', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_nomina($link);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar nomina', $alta);
            print_r($error);
            exit;
        }


        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 3000;
        $alta = (new nom_par_percepcion($this->link))->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta', $alta);
            print_r($error);
            exit;
        }

        $nom_nomina_id = 1;
        $resultado = $calculo->calculos($link, $nom_nomina_id);



        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(62.4,$resultado->isr);
        $this->assertEquals(0,$resultado->subsidio);

        errores::$error = false;

    }

    public function test_nomina_descuentos(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calcula_nomina();

        $link = $this->link;

        $del = (new base_test())->del_cat_sat_isr($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del_cat_sat_subsidio($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $link, cat_sat_periodicidad_pago_nom_id: 3,
            cuota_fija: 6.15, fecha_fin: '2022-12-31', fecha_inicio: '2022-01-01', limite_inferior: 318.01,
            limite_superior: 2699.4, porcentaje_excedente: 6.40 );
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_subsidio(link: $link, cat_sat_periodicidad_pago_nom_id: 3,
            cuota_fija: 160.35, fecha_fin: '2022-12-31', fecha_inicio: '2022-01-01', porcentaje_excedente: 0);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }
        
        $cat_sat_periodicidad_pago_nom_id = 3;
        $em_salario_diario = 172.87;
        $em_empleado_salario_diario_integrado = 180.69;
        $nom_nomina_fecha_final_pago = '2022-01-01';
        $nom_nomina_num_dias_pagados = 15;
        $total_gravado = 2593.05;

        $resultado = $calculo->nomina_descuentos($cat_sat_periodicidad_pago_nom_id, $em_salario_diario,
            $em_empleado_salario_diario_integrado, $link, $nom_nomina_fecha_final_pago, $nom_nomina_num_dias_pagados,
            $total_gravado);



        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(-8.6,$resultado);

        errores::$error = false;

        $link = $this->link;


        $cat_sat_periodicidad_pago_nom_id = 3;
        $em_salario_diario = 172.88;
        $em_empleado_salario_diario_integrado = 180.69;
        $nom_nomina_fecha_final_pago = '2022-01-01';
        $nom_nomina_num_dias_pagados = 15;
        $total_gravado = 2593.05;

        $resultado = $calculo->nomina_descuentos($cat_sat_periodicidad_pago_nom_id, $em_salario_diario,
            $em_empleado_salario_diario_integrado, $link, $nom_nomina_fecha_final_pago, $nom_nomina_num_dias_pagados,
            $total_gravado);

        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(55.77,$resultado);

        errores::$error = false;

        errores::$error = false;
    }

    public function test_nomina_neto(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $calculo = new calcula_nomina();

        $link = $this->link;

        $del = (new base_test())->del_cat_sat_isr($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_subsidio($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }



        $alta = (new base_test())->alta_cat_sat_isr(link: $link, cat_sat_periodicidad_pago_nom_id: 3,
            cuota_fija: 6.15, fecha_fin: '2022-12-31', fecha_inicio: '2022-01-01', limite_inferior: 318.01,
            limite_superior: 2699.4, porcentaje_excedente: 6.4 );
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_subsidio(link: $link, cat_sat_periodicidad_pago_nom_id: 3,
            cuota_fija: 160.35, fecha_fin: '2022-12-31', fecha_inicio: '2022-01-01', limite_inferior: 2327.56,
            limite_superior: 2632.65 );
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $cat_sat_periodicidad_pago_nom_id = 3;
        $em_salario_diario = 172.87;
        $em_empleado_salario_diario_integrado = 180.69;
        $nom_nomina_fecha_final_pago = '2022-01-01';
        $nom_nomina_num_dias_pagados = 15;
        $total_gravado = 2601.65;

       $resultado = $calculo->nomina_neto($cat_sat_periodicidad_pago_nom_id, $em_salario_diario,
            $em_empleado_salario_diario_integrado, $link, $nom_nomina_fecha_final_pago, $nom_nomina_num_dias_pagados,
            $total_gravado);


        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(2593.05,$resultado);

        errores::$error = false;
        $link = $this->link;

        $del = (new base_test())->del_cat_sat_isr($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_cat_sat_subsidio($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }



        $alta = (new base_test())->alta_cat_sat_isr(link: $link, cat_sat_periodicidad_pago_nom_id: 3,
            cuota_fija: 158.55, fecha_fin: '2022-12-31', fecha_inicio: '2022-01-01', limite_inferior: 2699.41,
            limite_superior: 4744.05, porcentaje_excedente: 10.88 );
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_subsidio(link: $link, cat_sat_periodicidad_pago_nom_id: 3,
            cuota_fija: 107.4, fecha_fin: '2022-12-31', fecha_inicio: '2022-01-01', limite_inferior: 3510.16,
            limite_superior: 3642.60 );
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_subsidio(link: $link, alias: 2, cat_sat_periodicidad_pago_nom_id: 3,
            codigo: 2, codigo_bis: 2, cuota_fija: 0, descripcion: 2, descripcion_select: 2, fecha_fin: '2022-12-31',
            fecha_inicio: '2022-01-01', id: 2, limite_inferior: 3642.61, limite_superior: 999999,
            porcentaje_excedente: 0);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }


        $cat_sat_periodicidad_pago_nom_id = 3;
        $em_salario_diario = 266.67;
        $em_empleado_salario_diario_integrado = 278.72;
        $nom_nomina_fecha_final_pago = '2022-01-01';
        $nom_nomina_num_dias_pagados = 15;
        $total_gravado = 3600.7;

       $resultado = $calculo->nomina_neto($cat_sat_periodicidad_pago_nom_id, $em_salario_diario,
            $em_empleado_salario_diario_integrado, $link, $nom_nomina_fecha_final_pago, $nom_nomina_num_dias_pagados,
            $total_gravado);


        $this->assertIsFloat($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(4000.05,$resultado);

        errores::$error = false;
    }




}

