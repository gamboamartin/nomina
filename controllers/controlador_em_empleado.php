<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */

namespace gamboamartin\nomina\controllers;


use gamboamartin\documento\models\doc_documento;
use gamboamartin\errores\errores;
use gamboamartin\nomina\models\em_empleado;
use gamboamartin\plugins\Importador;
use Throwable;

class controlador_em_empleado extends \gamboamartin\empleado\controllers\controlador_em_empleado
{

    public function calcula_sdi(bool $header, bool $ws = true)
    {
        $em_empleado_id = $_GET['em_empleado_id'];
        $fecha_inicio_rel = $_GET['fecha_inicio_rel_laboral'];
        $salario_diario = $_GET['salario_diario'];

        $result = (new em_empleado($this->link))->calcula_sdi(em_empleado_id: $em_empleado_id,
            fecha_inicio_rel: $fecha_inicio_rel, salario_diario: $salario_diario);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener', data: $result, header: $header, ws: $ws);
        }

        if ($header) {
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:' . $retorno);
            exit;
        }
        if ($ws) {
            header('Content-Type: application/json');
            try {
                echo json_encode($result, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                return $this->retorno_error(mensaje: 'Error al maquetar estados', data: $e, header: false, ws: $ws);
            }
            exit;
        }

        return $result;
    }

    public function lee_archivo(bool $header, bool $ws = false)
    {
        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['descripcion'] = rand();
        $doc_documento_modelo->registro['descripcion_select'] = rand();
        $doc_documento_modelo->registro['doc_tipo_documento_id'] = 1;
        $doc_documento = $doc_documento_modelo->alta_bd(file: $_FILES['archivo']);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al dar de alta el documento', data: $doc_documento);
            if (!$header) {
                return $error;
            }
            print_r($error);
            die('Error');
        }

        $columnas = ["codigo", "nombre", "ap", "am", "telefono", "curp", "rfc", "nss", "fecha_inicio_rel_laboral",
            "salario_diario", "factor_integracion", "salario_diario_integrado", "num_cuenta", "clabe"];

        $fechas = array("fecha_ingreso");

        $empleados_excel = Importador::getInstance()
            ->leer_registros(ruta_absoluta: $doc_documento->registro['doc_documento_ruta_absoluta'], columnas: $columnas,
                fechas: $fechas);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error obtener empleados', data: $empleados_excel);
            if (!$header) {
                return $error;
            }
            print_r($error);
            die('Error');
        }

        foreach ($empleados_excel as $empleado) {

            $registro = array();
            $keys = array('codigo', 'nombre', 'ap', 'am', 'telefono', 'curp', 'rfc', 'nss', 'fecha_inicio_rel_laboral',
                'salario_diario', 'salario_diario_integrado','num_cuenta', 'clabe');
            foreach ($keys as $key) {
                if (isset($empleado->$key)) {
                    $registro[$key] = str_replace("'","",$empleado->$key);
                }
            }

            $r_alta = (new em_empleado($this->link))->alta_registro(registro: $registro);
            if (errores::$error) {
                $error = $this->errores->error(mensaje: 'Error al dar de alta empleado', data: $r_alta);
                if (!$header) {
                    return $error;
                }
                print_r($error);
                die('Error');
            }
        }

        header('Location:' . $this->link_lista);
        exit;
    }

}
