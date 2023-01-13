<?php
namespace html;

use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_detalle_layout;
use gamboamartin\system\html_controler;
use gamboamartin\nomina\models\nom_detalle_layout;
use gamboamartin\nomina\models\nom_nomina;
use PDO;
use stdClass;

class nom_detalle_layout_html extends html_controler {

    private function asigna_inputs_alta(controlador_nom_detalle_layout $controler, array|stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs['selects']->nom_nomina_id;
        $controler->inputs->select->nom_layout_id = $inputs['selects']->nom_layout_id;

        return $controler->inputs;
    }

    private function asigna_inputs_modifica(controlador_nom_detalle_layout $controler, stdClass $inputs): array|stdClass
    {

        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs->selects->nom_nomina_id;
        $controler->inputs->select->nom_layout_id = $inputs->selects->nom_layout_id;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_detalle_layout $controler, modelo $modelo, PDO $link, array $keys_selects = array()): array|stdClass
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

    private function genera_inputs_modifica(controlador_nom_detalle_layout $controler,PDO $link,
                                            stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->init_modifica(link: $link, row_upd: $controler->row_upd, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_modifica(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function init_modifica(PDO $link, stdClass $row_upd, stdClass $params = new stdClass()): array|stdClass
    {
        $selects = $this->selects_modifica(link: $link, row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar selects',data:  $selects);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        return $alta_inputs;
    }

    public function inputs_nom_detalle_layout(controlador_nom_detalle_layout $controlador,
                                      stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new nom_nomina_html(html:$this->html_base))->select_nom_nomina_id(
            cols: 12, con_registros:true, id_selected:$row_upd->nom_nomina_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_nomina_id = $select;

        $select = (new nom_layout_html(html:$this->html_base))->select_nom_layout_id(
            cols: 12, con_registros:true, id_selected:$row_upd->nom_layout_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_layout_id = $select;

        return $selects;
    }


    public function select_nom_detalle_layout_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                         bool $disabled = false, array $filtro = array()): array|string
    {
        $modelo = new nom_detalle_layout(link: $link);

        $select = $this->select_catalogo(cols: $cols, con_registros: $con_registros, id_selected: $id_selected,
            modelo: $modelo, disabled: $disabled, filtro: $filtro, label: 'Detalle Layout', required: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


}
