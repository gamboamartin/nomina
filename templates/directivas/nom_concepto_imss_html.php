<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_concepto_imss;
use gamboamartin\system\html_controler;
use gamboamartin\nomina\models\nom_concepto_imss;
use PDO;
use stdClass;


class nom_concepto_imss_html extends html_controler {

    public function select_nom_concepto_imss_id(int $cols,bool $con_registros,int $id_selected, PDO $link): array|string
    {
        $modelo = new nom_concepto_imss($link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,label: 'Concepto IMSS',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    private function asigna_inputs(controlador_nom_concepto_imss $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs->selects->nom_nomina_id;
        $controler->inputs->select->nom_tipo_concepto_imss_id = $inputs->selects->nom_tipo_concepto_imss_id;
        $controler->inputs->monto = $inputs->texts->monto;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_concepto_imss $controler, array $keys_selects, PDO $link): array|stdClass
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

    private function genera_inputs_modifica(controlador_nom_concepto_imss $controler,PDO $link,
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

    protected function init_alta(array $keys_selects, PDO $link): array|stdClass
    {
        $texts = $this->texts_alta(row_upd: new stdClass(), value_vacio: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $init = parent::init_alta($keys_selects, $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $init);
        }

        $init->texts = $texts;

        return $init;
    }

    private function init_modifica(PDO $link, stdClass $row_upd, stdClass $params = new stdClass()): array|stdClass
    {

        $selects = $this->selects_modifica(link: $link, row_upd: $row_upd,params: $params);
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

    private function selects_modifica(PDO $link, stdClass $row_upd, stdClass $params = new stdClass()): array|stdClass
    {

        $cols_nom_nomina_id = $params->nom_nomina_id->cols ?? 4;
        $disabled_nom_nomina_id = $params->nom_nomina_id->disabled ?? false;
        $filtro_nom_nomina_id = $params->nom_nomina_id->filtro ?? array();

        $selects = new stdClass();

        $select = (new nom_nomina_html(html:$this->html_base))->select_nom_nomina_id(
            cols: $cols_nom_nomina_id, con_registros:true, id_selected: $row_upd->nom_nomina_id,link: $link,
            disabled: $disabled_nom_nomina_id, filtro: $filtro_nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_nomina_id = $select;

        $cols_nom_tipo_concepto_imss_id = $params->nom_tipo_concepto_imss_id->cols ?? 4;
        $select = (new nom_tipo_concepto_imss_html(html:$this->html_base))->select_nom_tipo_concepto_imss_id(
            cols: $cols_nom_tipo_concepto_imss_id, con_registros:true, id_selected:$row_upd->nom_tipo_concepto_imss_id,
            link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }

        $selects->nom_tipo_concepto_imss_id = $select;

        return $selects;
    }


    public function inputs_nom_concepto_imss(controlador_nom_concepto_imss $controlador,
                                              stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_monto = $this->input_monto(cols: 4, row_upd:  $row_upd,value_vacio:  $value_vacio,
            disabled: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_monto);
        }

        $texts->monto = $in_monto;

        return $texts;
    }

    private function texts_modifica(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_monto = $this->input_monto(cols: 4, row_upd:  $row_upd,value_vacio:  false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_monto);
        }

        $texts->monto = $in_monto;
        return $texts;
    }

    public function input_monto(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'monto',place_holder: 'Monto',
            row_upd: $row_upd, value_vacio: $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols,html:  $html);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

}
