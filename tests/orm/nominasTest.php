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

