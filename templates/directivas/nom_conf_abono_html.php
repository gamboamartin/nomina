<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_conf_abono;
use gamboamartin\template\directivas;
use models\nom_conf_abono;
use PDO;
use stdClass;

class nom_conf_abono_html extends em_html {

    private function asigna_inputs(controlador_nom_conf_abono $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->em_tipo_abono_anticipo_id = $inputs->selects->em_tipo_abono_anticipo_id;
        $controler->inputs->select->nom_deduccion_id = $inputs->selects->nom_deduccion_id;
        $controler->inputs->id = $inputs->texts->id;
        $controler->inputs->codigo = $inputs->texts->codigo;

        return $controler->inputs;
    }

    public function genera_inputs(controlador_nom_conf_abono $controler, array $keys_selects = array()): array|stdClass
    {
        $inputs = $this->init_alta2(row_upd: $controler->row_upd, modelo: $controler->modelo, link: $controler->link,
            keys_selects:$keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }

        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function select_nom_conf_abono_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                          array $filtro = array()): array|string
    {
        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }
        if(is_null($id_selected)){
            $id_selected = -1;
        }
        $modelo = new nom_conf_abono(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,filtro: $filtro, label: 'Conf. Abono',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

}
