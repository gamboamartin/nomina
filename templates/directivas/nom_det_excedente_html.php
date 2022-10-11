<?php
namespace html;

use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_det_excedente;
use gamboamartin\system\html_controler;
use models\nom_det_excedente;
use PDO;
use stdClass;

class nom_det_excedente_html extends html_controler {

    private function asigna_inputs_alta(controlador_nom_det_excedente $controler, array|stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_per_excedente_id = $inputs['selects']->nom_per_excedente_id;
        $controler->inputs->select->nom_percepcion_id = $inputs['selects']->nom_percepcion_id;
        $controler->inputs->porcentaje = $inputs['inputs']->porcentaje;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_det_excedente $controler, modelo $modelo, PDO $link, array $keys_selects = array()): array|stdClass
    {
        $inputs = $this->init_alta2(row_upd: $controler->row_upd,modelo: $controler->modelo,link: $link,keys_selects:  $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_alta(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function select_nom_det_excedente_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                         bool $disabled = false, array $filtro = array()): array|string
    {
        $modelo = new nom_det_excedente(link: $link);

        $select = $this->select_catalogo(cols: $cols, con_registros: $con_registros, id_selected: $id_selected,
            modelo: $modelo, disabled: $disabled, filtro: $filtro, label: 'Det Excedente', required: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


}
