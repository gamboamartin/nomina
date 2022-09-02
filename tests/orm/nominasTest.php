<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\calcula_imss;
use models\fc_cfd_partida;
use models\fc_factura;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use models\nominas;
use stdClass;


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

        $r_del_nom_par_percepcion = $nominas->elimina_todo();
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar percepcion', $r_del_nom_par_percepcion);
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




}

