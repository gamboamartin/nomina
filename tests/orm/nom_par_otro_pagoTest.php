<?php
namespace tests\controllers;

use controllers\controlador_cat_sat_tipo_persona;
use gamboamartin\errores\errores;
use gamboamartin\test\liberator;
use gamboamartin\test\test;
use JsonException;
use models\em_cuenta_bancaria;
use models\fc_cfd_partida;
use models\fc_factura;
use models\fc_partida;
use models\nom_conf_empleado;
use models\nom_data_subsidio;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_otro_pago;
use models\nom_par_percepcion;
use stdClass;
use tests\base_test;


class nom_par_otro_pagoTest extends test {
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


    public function test_elimina_bd(): void
    {
        errores::$error = false;

        $_GET['seccion'] = 'cat_sat_tipo_persona';
        $_GET['accion'] = 'lista';
        $_SESSION['grupo_id'] = 1;
        $_SESSION['usuario_id'] = 2;
        $_GET['session_id'] = '1';
        $percepcion = new nom_par_otro_pago($this->link);

        $nom_nomina_modelo = new nom_nomina($this->link);
        $nom_nom_par_otro_pago_modelo = new nom_par_otro_pago($this->link);

        $nom_nom_par_deduccion_modelo = new nom_par_deduccion($this->link);
        $fc_factura_modelo = new fc_factura($this->link);
        $fc_partida_modelo = new fc_partida($this->link);
        $em_cuenta_bancaria_modelo = new em_cuenta_bancaria($this->link);



        $del = (new base_test())->del($this->link, 'nom_data_subsidio');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'nom_par_percepcion');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'nom_par_otro_pago');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'nom_par_deduccion');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'nom_nomina');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'fc_partida');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'fc_factura');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'nom_conf_empleado');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }
        $del = (new base_test())->del($this->link, 'em_cuenta_bancaria');
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al eliminar', data: $del);
            print_r($error);
            exit;
        }

        $alta = (new base_test())->alta_em_cuenta_bancaria($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }
        $alta = (new base_test())->alta_nom_conf_empleado($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }
        $alta = (new base_test())->alta_nom_nomina($this->link);
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar', data: $alta);
            print_r($error);
            exit;
        }


        $percepcion->registro['nom_nomina_id'] = 1;
        $percepcion->registro['nom_otro_pago_id'] = 1;
        $percepcion->registro['importe_gravado'] = 100;
        $resultado = $percepcion->alta_bd();
        if(errores::$error){
            $error = (new errores())->error(mensaje: 'Error al insertar otro pago', data: $resultado);
            print_r($error);
            exit;
        }

        $resultado = $percepcion->elimina_bd($resultado->registro_id);


        $this->assertIsArray($resultado);
        $this->assertNotTrue(errores::$error);

        errores::$error = false;
    }



}

