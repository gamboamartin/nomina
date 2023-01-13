<?php
namespace html;

use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_layout;
use gamboamartin\system\html_controler;
use gamboamartin\nomina\models\bn_sucursal;
use gamboamartin\nomina\models\nom_layout;
use PDO;
use stdClass;

class nom_layout_html extends html_controler {

    private function asigna_inputs_alta(controlador_nom_layout $controler, array|stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->bn_sucursal_id = $inputs['selects']->bn_sucursal_id;
        $controler->inputs->select->nom_periodo_id = $inputs['selects']->nom_periodo_id;

        $controler->inputs->fecha_pago = $inputs['dates']->fecha_pago;

        return $controler->inputs;
    }

    private function asigna_inputs_modifica(controlador_nom_layout $controler, stdClass $inputs): array|stdClass
    {

        $controler->inputs->select = new stdClass();
        $controler->inputs->select->bn_sucursal_id = $inputs->selects->bn_sucursal_id;
        $controler->inputs->select->nom_periodo_id = $inputs->selects->nom_periodo_id;
        $controler->inputs->fecha_pago = $inputs->texts->fecha_pago;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_layout $controler, modelo $modelo, PDO $link, array $keys_selects = array()): array|stdClass
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

    private function genera_inputs_modifica(controlador_nom_layout $controler,PDO $link,
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

        $texts = $this->texts_modifica(row_upd: $row_upd, value_vacio: false, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar texts',data:  $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->texts = $texts;
        $alta_inputs->selects = $selects;
        return $alta_inputs;
    }

    public function input_fecha_pago(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $row = $row_upd;
        $row->fecha_pago = substr($row_upd->fecha_pago, 0, 10);
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->fecha_required(disabled: $disabled, name: 'fecha_pago',
            place_holder: 'Fecha Pago', row_upd: $row, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function inputs_nom_layout(controlador_nom_layout $controlador,
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

        $select = (new bn_sucursal_html(html:$this->html_base))->select_bn_sucursal_id(
            cols: 12, con_registros:true, id_selected:$row_upd->bn_sucursal_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->bn_sucursal_id = $select;

        $select = (new nom_periodo_html(html:$this->html_base))->select_nom_periodo_id(
            cols: 12, con_registros:true, id_selected:$row_upd->nom_periodo_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_periodo_id = $select;

        return $selects;
    }

    public function select_nom_layout_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                         bool $disabled = false, array $filtro = array()): array|string
    {
        $modelo = new nom_layout(link: $link);

        $select = $this->select_catalogo(cols: $cols, con_registros: $con_registros, id_selected: $id_selected,
            modelo: $modelo, disabled: $disabled, filtro: $filtro, label: 'Layout', required: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    protected function texts_modifica(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_fecha_pago = $this->input_fecha_pago(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_pago);
        }
        $texts->fecha_pago = $in_fecha_pago;

        return $texts;
    }

}
