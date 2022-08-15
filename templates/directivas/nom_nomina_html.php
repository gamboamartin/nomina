<?php
namespace html;

use gamboamartin\comercial\controllers\controlador_emempleado;
use gamboamartin\empleado\controllers\controlador_em_empleado;
use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_nomina;
use gamboamartin\system\html_controler;
use models\com_sucursal;
use models\em_empleado;
use models\fc_cfd;
use models\nom_nomina;
use PDO;
use stdClass;

class nom_nomina_html extends html_controler {

    private function asigna_inputs(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->cat_sat_moneda_id = $inputs->selects->cat_sat_moneda_id;
        $controler->inputs->select->cat_sat_metodo_pago_id = $inputs->selects->cat_sat_metodo_pago_id;
        $controler->inputs->select->cat_sat_tipo_de_comprobante_id = $inputs->selects->cat_sat_tipo_de_comprobante_id;
        $controler->inputs->select->dp_calle_pertenece_id = $inputs->selects->dp_calle_pertenece_id;
        $controler->inputs->select->org_sucursal_id = $inputs->selects->org_sucursal_id;
        $controler->inputs->select->em_empleado_id = $inputs->selects->em_empleado_id;
        $controler->inputs->select->fc_cfd_id = $inputs->selects->fc_cfd_id;
        $controler->inputs->version = $inputs->texts->version;
        $controler->inputs->serie = $inputs->texts->serie;
        $controler->inputs->folio = $inputs->texts->folio;
        $controler->inputs->fecha = $inputs->texts->fecha;
        $controler->inputs->tipo_cambio = $inputs->texts->tipo_cambio;
        $controler->inputs->exportacion = $inputs->texts->exportacion;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_nomina $controler, PDO $link): array|stdClass
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

    private function genera_inputs_modifica(controlador_nom_nomina $controler, PDO $link): array|stdClass
    {
        $inputs = $this->init_modifica(link: $link, row_upd: $controler->row_upd);
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

    private function init_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {

        $selects = $this->selects_modifica(link: $link, row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar selects',data:  $selects);
        }

        $texts = $this->texts_alta(row_upd: $row_upd,value_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar texts',data:  $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    public function inputs_nom_nomina(controlador_nom_nomina $controlador_nom_nomina): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador_nom_nomina, link: $controlador_nom_nomina->link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    public function input_version(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: false,name: 'version',place_holder: 'Version',
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

    public function input_serie(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: false,name: 'serie',place_holder: 'Serie',
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

    public function input_folio(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: false,name: 'folio',place_holder: 'Folio',
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

    public function input_fecha(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->fecha_required(disable: false,name: 'fecha',place_holder: 'Fecha',
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

    public function input_tipo_cambio(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: false,name: 'tipo_cambio',place_holder: 'Tipo cambio',
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

    public function input_exportacion(int $cols, stdClass $row_upd, bool $value_vacio): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disable: false,name: 'exportacion',place_holder: 'Exportacion',
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

    private function selects_alta(PDO $link): array|stdClass
    {
        $selects = new stdClass();

        $select = (new cat_sat_moneda_html(html:$this->html_base))->select_cat_sat_moneda_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_moneda_id = $select;

        $select = (new cat_sat_metodo_pago_html(html:$this->html_base))->select_cat_sat_metodo_pago_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_metodo_pago_id = $select;

        $select = (new cat_sat_tipo_de_comprobante_html(html:$this->html_base))->select_cat_sat_tipo_de_comprobante_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_tipo_de_comprobante_id = $select;

        $select = (new dp_calle_pertenece_html(html:$this->html_base))->select_dp_calle_pertenece_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->dp_calle_pertenece_id = $select;

        $select = (new org_sucursal_html(html:$this->html_base))->select_org_sucursal_id(
            cols: 12, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->org_sucursal_id = $select;

        $select = (new em_empleado_html(html:$this->html_base))->select_em_empleado_id(
            cols: 12, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new fc_cfd_html(html:$this->html_base))->select_fc_cfd_id(
            cols: 12, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->fc_cfd_id = $select;

        return $selects;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new cat_sat_moneda_html(html:$this->html_base))->select_cat_sat_moneda_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_moneda_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_moneda_id = $select;

        $select = (new cat_sat_metodo_pago_html(html:$this->html_base))->select_cat_sat_metodo_pago_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_metodo_pago_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_metodo_pago_id = $select;

        $select = (new cat_sat_tipo_de_comprobante_html(html:$this->html_base))->select_cat_sat_tipo_de_comprobante_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_tipo_de_comprobante_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_tipo_de_comprobante_id = $select;

        $select = (new dp_calle_pertenece_html(html:$this->html_base))->select_dp_calle_pertenece_id(
            cols: 12, con_registros:true, id_selected:$row_upd->dp_calle_pertenece_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->dp_calle_pertenece_id = $select;

        $select = (new org_sucursal_html(html:$this->html_base))->select_org_sucursal_id(
            cols: 12, con_registros:true, id_selected:$row_upd->org_sucursal_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->org_sucursal_id = $select;

        $select = (new em_empleado_html(html:$this->html_base))->select_em_empleado_id(
            cols: 12, con_registros:true, id_selected:$row_upd->em_empleado_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new fc_cfd_html(html:$this->html_base))->select_fc_cfd_id(
            cols: 12, con_registros:true, id_selected:$row_upd->fc_cfd_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->fc_cfd_id = $select;

        return $selects;
    }

    public function select_nom_nomina_id(int $cols, bool $con_registros, int $id_selected, PDO $link): array|string
    {
        $modelo = new nom_nomina(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,label: 'Nomina',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    private function texts_alta(stdClass $row_upd, bool $value_vacio): array|stdClass
    {
        $texts = new stdClass();

        $in_version= $this->input_version(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_version);
        }
        $texts->version = $in_version;

        $in_serie = $this->input_serie(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_serie);
        }
        $texts->serie = $in_serie;

        $in_folio = $this->input_folio(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_folio);
        }
        $texts->folio = $in_folio;

        $in_fecha= $this->input_fecha(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_fecha);
        }
        $texts->fecha = $in_fecha;

        $in_tipo_cambio= $this->input_tipo_cambio(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_tipo_cambio);
        }
        $texts->tipo_cambio = $in_tipo_cambio;

        $in_exportacion = $this->input_exportacion(cols: 6,row_upd:  $row_upd,value_vacio:  $value_vacio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_exportacion);
        }
        $texts->exportacion = $in_exportacion;

        return $texts;
    }

}
