<?php
namespace html;

use gamboamartin\comercial\controllers\controlador_emempleado;
use gamboamartin\empleado\controllers\controlador_em_empleado;
use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_par_deduccion;
use gamboamartin\nomina\controllers\controlador_nom_par_percepcion;
use gamboamartin\nomina\controllers\controlador_nom_percepcion;
use gamboamartin\system\html_controler;
use models\cat_sat_tipo_percepcion_nom;
use models\com_sucursal;
use models\em_empleado;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use models\nom_percepcion;
use PDO;
use stdClass;

class nom_par_deduccion_html extends html_controler {

    private function asigna_inputs(controlador_nom_par_deduccion $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs->selects->nom_nomina_id;
        $controler->inputs->select->nom_deduccion_id = $inputs->selects->nom_deduccion_id;
        $controler->inputs->importe_gravado = $inputs->texts->importe_gravado;
        $controler->inputs->importe_exento = $inputs->texts->importe_exento;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_par_deduccion $controler, PDO $link): array|stdClass
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

    private function genera_inputs_modifica(controlador_nom_par_deduccion $controler,PDO $link,
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

    public function init_alta(PDO $link): array|stdClass
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

    public function input_importe_exento(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: false,name: 'importe_exento',place_holder: 'Importe exento',
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

    public function input_importe_gravado(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: false,name: 'importe_gravado',place_holder: 'Importe gravado',
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

    public function inputs_nom_par_deduccion(controlador_nom_par_deduccion $controlador,
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

        $select = (new nom_nomina_html(html:$this->html_base))->select_nom_nomina_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_nomina_id = $select;

        $select = (new nom_deduccion_html(html:$this->html_base))->select_nom_deduccion_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_deduccion_id = $select;

        return $selects;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new nom_nomina_html(html:$this->html_base))->select_nom_nomina_id(
            cols: 6, con_registros:true, id_selected:$row_upd->nom_nomina_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_nomina_id = $select;

        $select = (new nom_deduccion_html(html:$this->html_base))->select_nom_deduccion_id(
            cols: 6, con_registros:true, id_selected:$row_upd->nom_deduccion_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_deduccion_id = $select;

        return $selects;
    }

    public function select_nom_par_deduccion_id(int $cols, bool $con_registros, int $id_selected, PDO $link): array|string
    {
        $modelo = new nom_par_deduccion(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,label: 'Deduccion',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    private function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_importe_gravado = $this->input_importe_gravado(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_importe_gravado);
        }
        $texts->importe_gravado = $in_importe_gravado;

        $in_importe_exento = $this->input_importe_exento(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_importe_exento);
        }
        $texts->importe_exento = $in_importe_exento;
        return $texts;
    }

}
