<?php
namespace html;

use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_conf_deduccion;
use gamboamartin\nomina\controllers\controlador_nom_conf_nomina;

use gamboamartin\system\html_controler;
use models\nom_conf_deduccion;
use models\nom_conf_nomina;
use PDO;
use stdClass;

class nom_conf_deduccion_html extends html_controler {

    private function asigna_inputs(controlador_nom_conf_deduccion $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_conf_nomina_id = $inputs->selects->nom_conf_nomina_id;
        $controler->inputs->select->nom_deduccion_id = $inputs->selects->nom_deduccion_id;
        $controler->inputs->importe_gravado = $inputs->texts->importe_gravado;
        $controler->inputs->importe_exento = $inputs->texts->importe_exento;
        $controler->inputs->fecha_inicio = $inputs->texts->fecha_inicio;
        $controler->inputs->fecha_fin = $inputs->texts->fecha_fin;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_conf_deduccion $controler, modelo $modelo, PDO $link, array $keys_selects = array()): array|stdClass
    {
        $inputs = $this->init_alta2(modelo: $modelo,link: $link,keys_selects: $keys_selects);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function genera_inputs_modifica(controlador_nom_conf_deduccion $controler,PDO $link,
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

    public function inputs_nom_conf_deduccion(controlador_nom_conf_deduccion $controlador,
                                       stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    public function input_importe_gravado(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->input_text_required(disable: $disabled, name: 'importe_gravado',
            place_holder: 'Importe Gravado', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_importe_exento(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->input_text_required(disable: $disabled, name: 'importe_exento',
            place_holder: 'Importe Exento', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_fecha_inicio(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->fecha_required(disable: $disabled, name: 'fecha_inicio',
            place_holder: 'Fecha Inicio', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_fecha_fin(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->fecha_required(disable: $disabled, name: 'fecha_fin',
            place_holder: 'Fecha Fin', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }


    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new nom_conf_nomina_html(html:$this->html_base))->select_nom_conf_nomina_id(
            cols: 12, con_registros:true, id_selected:$row_upd->nom_conf_nomina_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_conf_nomina_id = $select;

        $select = (new nom_deduccion_html(html:$this->html_base))->select_nom_deduccion_id(
            cols: 12, con_registros:true, id_selected:$row_upd->nom_deduccion_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_deduccion_id = $select;

        return $selects;
    }

    public function select_nom_conf_deduccion_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                           bool $required = true): array|string
    {
        $modelo = new nom_conf_deduccion(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo, label: 'Configuracion Deduccion',required: $required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_importe_gravado = $this->input_importe_gravado(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_importe_gravado);
        }
        $texts->importe_gravado = $in_importe_gravado;

        $in_importe_exento = $this->input_importe_exento(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_importe_exento);
        }
        $texts->importe_exento = $in_importe_exento;

        $row_upd->fecha_inicio = date('Y-m-d');

        $in_fecha_inicio = $this->input_fecha_inicio(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicio);
        }
        $texts->fecha_inicio = $in_fecha_inicio;

        $row_upd->fecha_fin = date('Y-m-d');

        $in_fecha_fin = $this->input_fecha_fin(cols: 6, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_fin);
        }
        $texts->fecha_fin = $in_fecha_fin;


        return $texts;
    }
}
