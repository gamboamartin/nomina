<?php
namespace html;


use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_percepcion;
use gamboamartin\system\html_controler;
use models\nom_percepcion;
use PDO;
use stdClass;

class nom_percepcion_html extends html_controler {

    private function asigna_inputs(controlador_nom_percepcion $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->cat_sat_tipo_percepcion_nom_id = $inputs->selects->cat_sat_tipo_percepcion_nom_id;
        $controler->inputs->aplica_imss = $inputs->texts->aplica_imss;
        $controler->inputs->aplica_subsidio = $inputs->texts->aplica_subsidio;
        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_percepcion $controler, array $keys_selects, PDO $link): array|stdClass
    {
        $inputs = $this->init_alta(keys_selects: $keys_selects, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function genera_inputs_modifica(controlador_nom_percepcion $controler,PDO $link,
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



    public function input_aplica_imss(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->input_text_required(disable: $disabled, name: 'aplica_imss',
            place_holder: 'IMSS', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_aplica_subsidio(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->input_text_required(disable: $disabled, name: 'aplica_subsidio',
            place_holder: 'Subsidio', row_upd: $row_upd, value_vacio: $value_vacio);
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

    public function inputs_nom_percepcion(controlador_nom_percepcion $controlador,
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

        $select = (new cat_sat_tipo_percepcion_nom_html(html:$this->html_base))->select_cat_sat_tipo_percepcion_nom_id(
            cols: 12, con_registros:true, id_selected:$row_upd->cat_sat_tipo_percepcion_nom_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_tipo_percepcion_nom_id = $select;

        return $selects;
    }

    public function select_nom_percepcion_id(int $cols, bool $con_registros, int $id_selected, PDO $link): array|string
    {
        $modelo = new nom_percepcion(link: $link);

        $extra_params_keys[] = 'nom_percepcion_id';
        $extra_params_keys[] = 'nom_percepcion_descripcion';

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo, extra_params_keys:$extra_params_keys,label: 'Percepcion',required: true);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }

        return $select;
    }

    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_aplica_imss = $this->input_aplica_imss(cols: 6, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_aplica_imss);
        }
        $texts->aplica_imss = $in_aplica_imss;

        $in_aplica_subsidio = $this->input_aplica_subsidio(cols: 6, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_aplica_subsidio);
        }
        $texts->aplica_subsidio = $in_aplica_subsidio;

        return $texts;
    }

}
