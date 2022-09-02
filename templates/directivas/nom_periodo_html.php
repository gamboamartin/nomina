<?php
namespace html;



use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_deduccion;

use gamboamartin\nomina\controllers\controlador_nom_periodo;
use gamboamartin\system\html_controler;
use models\nom_deduccion;
use models\nom_periodo;
use PDO;
use stdClass;

class nom_periodo_html extends html_controler {

    private function asigna_inputs(controlador_nom_periodo $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->cat_sat_periodicidad_pago_nom_id = $inputs->selects->cat_sat_periodicidad_pago_nom_id;
        $controler->inputs->select->im_registro_patronal_id = $inputs->selects->im_registro_patronal_id;
        $controler->inputs->select->nom_tipo_periodo_id = $inputs->selects->nom_tipo_periodo_id;
        $controler->inputs->fecha_inicial_pago = $inputs->texts->fecha_inicial_pago;
        $controler->inputs->fecha_final_pago = $inputs->texts->fecha_final_pago;
        $controler->inputs->fecha_pago = $inputs->texts->fecha_pago;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_periodo $controler, PDO $link): array|stdClass
    {
        $inputs = $this->init_alta(link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function genera_inputs_modifica(controlador_nom_periodo $controler,PDO $link,
                                            stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->init_modifica(link: $link, row_upd: $controler->row_upd, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function init_alta(PDO $link): array|stdClass
    {
        $selects = $this->selects_alta(link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar selects',data:  $selects);
        }

        $texts = $this->texts_alta(row_upd: new stdClass(), value_vacio: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar texts',data:  $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    public function input_fecha_inicial_pago(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->fecha_required(disable: $disabled, name: 'fecha_inicial_pago',
            place_holder: 'Fecha inicial pago', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_fecha_final_pago(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->fecha_required(disable: $disabled, name: 'fecha_final_pago',
            place_holder: 'Fecha final pago', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_fecha_pago(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->fecha_required(disable: $disabled, name: 'fecha_pago', place_holder: 'Fecha pago',
            row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    private function init_modifica(PDO $link, stdClass $row_upd, stdClass $params = new stdClass()): array|stdClass
    {
        $selects = $this->selects_modifica(link: $link, row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar selects',data:  $selects);
        }

        $texts = $this->texts_alta(row_upd: $row_upd, value_vacio: false, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar texts',data:  $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->texts = $texts;
        $alta_inputs->selects = $selects;
        return $alta_inputs;
    }

    public function inputs_nom_periodo(controlador_nom_periodo $controlador,
                                       stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    private function selects_alta(PDO $link): array|stdClass
    {
        $selects = new stdClass();

        $select = (new cat_sat_periodicidad_pago_nom_html(html:$this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
            cols: 12, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_periodicidad_pago_nom_id = $select;

        $select = (new im_registro_patronal_html(html:$this->html_base))->select_im_registro_patronal_id(
            cols: 12, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->im_registro_patronal_id = $select;

        $select = (new nom_tipo_periodo_html(html:$this->html_base))->select_nom_tipo_periodo_id(
            cols: 12, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_tipo_periodo_id = $select;

        return $selects;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new cat_sat_periodicidad_pago_nom_html(html:$this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
            cols: 12, con_registros:true, id_selected:$row_upd->cat_sat_periodicidad_pago_nom_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_periodicidad_pago_nom_id = $select;

        $select = (new im_registro_patronal_html(html:$this->html_base))->select_im_registro_patronal_id(
            cols: 12, con_registros:true, id_selected:$row_upd->im_registro_patronal_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->im_registro_patronal_id = $select;

        $select = (new nom_tipo_periodo_html(html:$this->html_base))->select_nom_tipo_periodo_id(
            cols: 12, con_registros:true, id_selected:$row_upd->nom_tipo_periodo_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_tipo_periodo_id = $select;

        return $selects;
    }

    public function select_nom_periodo_id(int $cols, bool $con_registros, int $id_selected, PDO $link): array|string
    {
        $modelo = new nom_periodo(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo, label: 'Periodo',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    private function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_fecha_inicial_pago = $this->input_fecha_inicial_pago(cols: 6, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicial_pago);
        }
        $texts->fecha_inicial_pago = $in_fecha_inicial_pago;

        $in_fecha_final_pago = $this->input_fecha_final_pago(cols: 6, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_final_pago);
        }
        $texts->fecha_final_pago = $in_fecha_final_pago;

        $in_fecha_pago = $this->input_fecha_pago(cols: 6, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_pago);
        }
        $texts->fecha_pago = $in_fecha_pago;

        return $texts;
    }



}
