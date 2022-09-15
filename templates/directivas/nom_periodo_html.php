<?php
namespace html;



use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_deduccion;

use gamboamartin\nomina\controllers\controlador_nom_periodo;
use gamboamartin\system\html_controler;
use gamboamartin\template\directivas;
use gamboamartin\validacion\validacion;
use models\nom_deduccion;
use models\nom_periodo;
use PDO;
use stdClass;

class nom_periodo_html extends html_controler {

    private function asigna_inputs(controlador_nom_periodo $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_conf_nomina_id = $inputs->selects->nom_conf_nomina_id;
        $controler->inputs->select->cat_sat_periodicidad_pago_nom_id = $inputs->selects->cat_sat_periodicidad_pago_nom_id;
        $controler->inputs->select->im_registro_patronal_id = $inputs->selects->im_registro_patronal_id;
        $controler->inputs->select->nom_tipo_periodo_id = $inputs->selects->nom_tipo_periodo_id;
        $controler->inputs->select->cat_sat_tipo_nomina_id = $inputs->selects->cat_sat_tipo_nomina_id;
        $controler->inputs->fecha_inicial_pago = $inputs->texts->fecha_inicial_pago;
        $controler->inputs->fecha_final_pago = $inputs->texts->fecha_final_pago;
        $controler->inputs->fecha_pago = $inputs->texts->fecha_pago;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_periodo $controler, PDO $link): array|stdClass
    {
        $inputs = $this->init_alta_base(link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function init_alta_base(PDO $link): array|stdClass
    {
        $selects = $this->selects_alta_base(link: $link);
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

    private function genera_inputs_modifica(controlador_nom_periodo $controler,PDO $link,
                                            stdClass $params = new stdClass()): array|stdClass
    {
        $keys = array('cat_sat_periodicidad_pago_nom_id','im_registro_patronal_id','cat_sat_tipo_nomina_id',
            'nom_tipo_periodo_id');

        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $controler->row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row upd',data:  $valida);
        }


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
        $keys = array('cat_sat_periodicidad_pago_nom_id','im_registro_patronal_id','cat_sat_tipo_nomina_id',
            'nom_tipo_periodo_id');

        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row upd',data:  $valida);
        }


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

    public function inputs_nom_periodo(controlador_nom_periodo $controlador,
                                       stdClass $params = new stdClass()): array|stdClass
    {

        $keys = array('cat_sat_periodicidad_pago_nom_id','im_registro_patronal_id','cat_sat_tipo_nomina_id',
            'nom_tipo_periodo_id');

        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $controlador->row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row upd',data:  $valida);
        }

        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    private function selects_alta_base(PDO $link): array|stdClass
    {
    $selects = new stdClass();

    $select = (new nom_conf_nomina_html(html:$this->html_base))->select_nom_conf_nomina_id(
    cols: 6, con_registros:true, id_selected:-1,link: $link,required: false);
    if(errores::$error){
    return $this->error->error(mensaje: 'Error al generar select',data:  $select);
    }
    $selects->nom_conf_nomina_id = $select;

    $select = (new cat_sat_periodicidad_pago_nom_html(html:$this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
    cols: 12, con_registros:true, id_selected:-1,link: $link);
    if(errores::$error){
    return $this->error->error(mensaje: 'Error al generar select',data:  $select);
    }
    $selects->cat_sat_periodicidad_pago_nom_id = $select;

    $select = (new im_registro_patronal_html(html:$this->html_base))->select_im_registro_patronal_id(
    cols: 12, con_registros:true, id_selected:-1,link: $link,required: true);
    if(errores::$error){
    return $this->error->error(mensaje: 'Error al generar select',data:  $select);
    }
    $selects->im_registro_patronal_id = $select;

    $select = (new nom_tipo_periodo_html(html:$this->html_base))->select_nom_tipo_periodo_id(
    cols: 6, con_registros:true, id_selected:-1,link: $link);
    if(errores::$error){
    return $this->error->error(mensaje: 'Error al generar select',data:  $select);
    }
    $selects->nom_tipo_periodo_id = $select;

    $select = (new cat_sat_tipo_nomina_html(html:$this->html_base))->select_cat_sat_tipo_nomina_id(
    cols: 6, con_registros:true, id_selected:-1,link: $link);
    if(errores::$error){
    return $this->error->error(mensaje: 'Error al generar select',data:  $select);
    }
    $selects->cat_sat_tipo_nomina_id = $select;

    return $selects;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {

        $keys = array('cat_sat_periodicidad_pago_nom_id','im_registro_patronal_id','cat_sat_tipo_nomina_id',
            'nom_tipo_periodo_id');

        $valida = (new validacion())->valida_existencia_keys(keys: $keys, registro: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row upd',data:  $valida);
        }

        $selects = new stdClass();

        $select = (new nom_conf_nomina_html(html:$this->html_base))->select_nom_conf_nomina_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link,required: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_conf_nomina_id = $select;

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
            cols: 6, con_registros:true, id_selected:$row_upd->nom_tipo_periodo_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_tipo_periodo_id = $select;

        $select = (new cat_sat_tipo_nomina_html(html:$this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_tipo_nomina_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;

        return $selects;
    }


    /**
     * Genera un select de tipo nom periodo
     * @param int $cols No de columnas para css
     * @param bool $con_registros si con registros integra los registros en options
     * @param int $id_selected identificador para selected
     * @param PDO $link Conexion a la base de datos
     * @return array|string
     * @version 0.294.10
     */
    public function select_nom_periodo_id(int $cols, bool $con_registros, int $id_selected, PDO $link): array|string
    {
        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }

        if(is_null($id_selected)){
            $id_selected = -1;
        }

        $modelo = new nom_periodo(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo, label: 'Periodo',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $row_upd->fecha_inicial_pago = date('Y-m-d');

        $in_fecha_inicial_pago = $this->input_fecha_inicial_pago(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicial_pago);
        }
        $texts->fecha_inicial_pago = $in_fecha_inicial_pago;

        $row_upd->fecha_final_pago = date('Y-m-d');

        $in_fecha_final_pago = $this->input_fecha_final_pago(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_final_pago);
        }
        $texts->fecha_final_pago = $in_fecha_final_pago;

        $row_upd->fecha_pago = date('Y-m-d');

        $in_fecha_pago = $this->input_fecha_pago(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_pago);
        }
        $texts->fecha_pago = $in_fecha_pago;

        return $texts;
    }

    private function texts_modifica(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_fecha_inicial_pago = $this->input_fecha_inicial_pago(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicial_pago);
        }
        $texts->fecha_inicial_pago = $in_fecha_inicial_pago;

        $in_fecha_final_pago = $this->input_fecha_final_pago(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_final_pago);
        }
        $texts->fecha_final_pago = $in_fecha_final_pago;

        $in_fecha_pago = $this->input_fecha_pago(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_pago);
        }
        $texts->fecha_pago = $in_fecha_pago;

        return $texts;
    }

}
