<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_concepto_imss;
use gamboamartin\system\html_controler;
use models\nom_concepto_imss;
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

    public function input_monto(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: $disabled,name: 'monto',place_holder: 'Monto',
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
