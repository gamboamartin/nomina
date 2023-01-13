<?php
namespace gamboamartin\nomina\tests\orm;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use gamboamartin\nomina\models\calcula_imss;
use gamboamartin\nomina\models\em_cuenta_bancaria;
use gamboamartin\nomina\models\em_empleado;
use gamboamartin\nomina\models\fc_cfd_partida;
use gamboamartin\nomina\models\fc_factura;
use gamboamartin\nomina\models\fc_partida;
use gamboamartin\nomina\models\nom_conf_empleado;
use gamboamartin\nomina\models\nom_data_subsidio;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_otro_pago;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\nomina\models\nom_rel_empleado_sucursal;
use gamboamartin\nomina\models\nominas;
use stdClass;
use gamboamartin\nomina\tests\base_test;


class nominasTest extends test {
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

    public function test_asigna_codigo_partida(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_deduccion($this->link);
        $nominas = new liberator($nominas);


        $registro = array();
        $registro['nom_nomina_id'] = '1';
        $resultado = $nominas->asigna_codigo_partida($registro);



        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado['nom_nomina_id']);
        errores::$error = false;
    }

    public function test_base_calculo_impuesto(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        //$nominas = new liberator($nominas);



        $del = (new base_test())->del_nom_percepcion($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $del = (new base_test())->del_fc_factura($this->link);
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

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:10.57, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:179.97, limite_superior:316.27, porcentaje_excedente:10.88);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_nom_nomina(link: $this->link,
            nom_percepcion_descripcion: 'Sueldos, Salarios Rayas y Jornales');
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta ', $alta);
            print_r($error);
            exit;
        }


        $del = (new base_test())->del_cat_sat_isr(link: $this->link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_cat_sat_isr(link: $this->link, cuota_fija:25.4, fecha_fin: '9999-01-01',
            fecha_inicio: '1900-01-01', limite_inferior:316.28, limite_superior:367.65, porcentaje_excedente:16);
        if(errores::$error){
            $error = (new errores())->error('Error al insertar', $alta);
            print_r($error);
            exit;
        }

        $nom_par_percepcion = array();
        $nom_par_percepcion['id'] = 1;
        $nom_par_percepcion['nom_nomina_id'] = 1;
        $nom_par_percepcion['nom_percepcion_id'] = 1;
        $nom_par_percepcion['importe_gravado'] = 100;
        $r_alta_nom_par_percepcion = $nominas->alta_registro($nom_par_percepcion);
        if(errores::$error){
            $error = (new errores())->error('Error al dar de alta percepcion', $r_alta_nom_par_percepcion);
            print_r($error);
            exit;
        }

        $partida_percepcion_id = 1;

        $resultado = $nominas->base_calculo_impuesto($partida_percepcion_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals('1', $resultado->nom_par_percepcion_id);
        $this->assertEquals('Sueldos, Salarios Rayas y Jornales', $resultado->nom_par_percepcion_descripcion);
        $this->assertEquals('1', $resultado->nom_nomina_id);
        $this->assertEquals('1', $resultado->nom_percepcion_id);
        $this->assertEquals('Diario', $resultado->cat_sat_periodicidad_pago_nom_descripcion);
        $this->assertEquals('1', $resultado->em_empleado_id);
        $this->assertEquals('250', $resultado->em_empleado_salario_diario_integrado);
        errores::$error = false;
    }


    public function test_existe_data_deduccion(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_deduccion($this->link);
        //$nominas = new liberator($nominas);

        $del = (new nom_data_subsidio($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar data subsidio', $del);
            print_r($error);
            exit;
        }

        $del = (new nom_par_deduccion($this->link))->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar deduccion', $del);
            print_r($error);
            exit;
        }

        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->existe_data_deduccion($nom_deduccion_id, $nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->filtro['nom_nomina.id']);
        $this->assertEquals(1, $resultado->filtro['nom_deduccion.id']);
        $this->assertFalse( $resultado->existe);

        errores::$error = false;


        $nom_par_deduccion = array();
        $nom_par_deduccion['id'] = 1;
        $nom_par_deduccion['nom_nomina_id'] = 1;
        $nom_par_deduccion['nom_deduccion_id'] = 1;
        $nom_par_deduccion['importe_exento'] = 100;

        $alta = (new nom_par_deduccion($this->link))->alta_registro($nom_par_deduccion);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar deduccion', $alta);
            print_r($error);
            exit;
        }

        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->existe_data_deduccion($nom_deduccion_id, $nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->filtro['nom_nomina.id']);
        $this->assertEquals(1, $resultado->filtro['nom_deduccion.id']);
        $this->assertTrue( $resultado->existe);
        errores::$error = false;
    }

    public function test_data_deduccion(): void{
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        //$nominas = new liberator($nominas);

        $monto_exento = 0;
        $monto_gravado = 0;
        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->data_deduccion($monto_exento, $monto_gravado, $nom_deduccion_id, $nom_nomina_id);
        $this->assertIsObject($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado->row_ins['nom_nomina_id']);
        $this->assertEquals(1, $resultado->row_ins['nom_deduccion_id']);
        $this->assertEquals(0, $resultado->row_ins['importe_gravado']);
        $this->assertEquals(0, $resultado->row_ins['importe_exento']);
        $this->assertTrue($resultado->existe);

        errores::$error = false;
    }

    public function test_filtro_partida(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        $nominas = new liberator($nominas);

        $tabla = 'x';
        $nom_nomina_id = 1;
        $id = 1;

        $resultado = $nominas->filtro_partida($id, $nom_nomina_id, $tabla);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['nom_nomina.id']);
        $this->assertEquals(1, $resultado['x.id']);

        errores::$error = false;
    }

    public function test_get_by_nomina(): void{
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        //$nominas = new liberator($nominas);


        $nom_nomina_id = 1;

        $resultado = $nominas->get_by_nomina($nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        errores::$error = false;
    }

    public function test_nom_par_deduccion_aut(): void{
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_GET['session_id'] = '1';
        $nominas = new nom_par_percepcion($this->link);
        $nominas = new liberator($nominas);

        $monto_gravado = -0.000;
        $monto_exento = 0;
        $nom_deduccion_id = 1;
        $nom_nomina_id = 1;

        $resultado = $nominas->nom_par_deduccion_aut($monto_exento, $monto_gravado, $nom_deduccion_id, $nom_nomina_id);
        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);
        $this->assertEquals(1, $resultado['nom_nomina_id']);
        $this->assertEquals(1, $resultado['nom_deduccion_id']);
        $this->assertEquals(0, $resultado['importe_gravado']);
        $this->assertEquals(0, $resultado['importe_exento']);
        errores::$error = false;

    }






}

