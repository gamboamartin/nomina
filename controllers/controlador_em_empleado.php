<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;


use gamboamartin\errores\errores;
use gamboamartin\nomina\models\em_empleado;
use Throwable;

class controlador_em_empleado extends \gamboamartin\empleado\controllers\controlador_em_empleado {

    public function calcula_sdi(bool $header, bool $ws = true){
        $em_empleado_id = $_GET['em_empleado_id'];
        $fecha_inicio_rel = $_GET['fecha_inicio_rel_laboral'];
        $salario_diario = $_GET['salario_diario'];

        $result = (new em_empleado($this->link))->calcula_sdi(em_empleado_id: $em_empleado_id,
            fecha_inicio_rel: $fecha_inicio_rel, salario_diario: $salario_diario);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener',data:  $result, header: $header,ws:$ws);
        }

        if($header){
            $retorno = $_SERVER['HTTP_REFERER'];
            header('Location:'.$retorno);
            exit;
        }
        if($ws){
            header('Content-Type: application/json');
            try {
                echo json_encode($result, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                return $this->retorno_error(mensaje: 'Error al maquetar estados',data:  $e, header: false,ws:$ws);
            }
            exit;
        }

        return $result;
    }

}
