<?php
namespace html;


use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_conf_factura;

use gamboamartin\system\html_controler;

use models\nom_conf_factura;

use PDO;
use stdClass;

class nom_conf_factura_html extends html_controler {

    private function asigna_inputs(controlador_nom_conf_factura $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->cat_sat_forma_pago_id = $inputs->selects->cat_sat_forma_pago_id;
        $controler->inputs->select->cat_sat_metodo_pago_id = $inputs->selects->cat_sat_metodo_pago_id;
        $controler->inputs->select->cat_sat_moneda_id = $inputs->selects->cat_sat_moneda_id;
        $controler->inputs->select->com_tipo_cambio_id = $inputs->selects->com_tipo_cambio_id;
        $controler->inputs->select->cat_sat_uso_cfdi_id = $inputs->selects->cat_sat_uso_cfdi_id;
        $controler->inputs->select->cat_sat_tipo_de_comprobante_id = $inputs->selects->cat_sat_tipo_de_comprobante_id;
        $controler->inputs->select->com_producto_id = $inputs->selects->com_producto_id;
        return $controler->inputs;
    }

    /**
     * Genera un boton para siguiente accion
     * @param string $label Etiqueta del boton
     * @param string $value Valor de boton de la siguiente accion
     * @return array|string
     * @version 0.272.8
     */
    private function btn_next_action(string $label, string $value): array|string
    {
        $valida  = $this->directivas->valida_data_base(label: $label,value:  $value);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar datos', data: $valida);
        }

        $btn = $this->directivas->btn_action_next_div(label: $label, value: $value, cols: 12);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar btn', data: $btn);
        }

        return $btn;

    }

    public function btns_views(): array
    {
        $btns = array();
        $btn_modifica = $this->btn_next_action(label: 'Guarda', value: 'modifica');

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar boton',data:  $btn_modifica);

        }
        $btns['sub_guarda'] = $btn_modifica;



        return $btns;
    }

    public function genera_inputs_alta(controlador_nom_conf_factura $controler,array $keys_selects, PDO $link): array|stdClass
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

    private function genera_inputs_modifica(controlador_nom_conf_factura $controler,PDO $link,
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

    public function inputs_nom_conf_factura(controlador_nom_conf_factura $controlador,
                                       stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    /**private function selects_alta(PDO $link): array|stdClass
    {
        $selects = new stdClass();

        $select = (new cat_sat_forma_pago_html(html:$this->html_base))->select_cat_sat_forma_pago_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_forma_pago_id = $select;

        $select = (new cat_sat_metodo_pago_html(html:$this->html_base))->select_cat_sat_metodo_pago_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_metodo_pago_id = $select;

        $select = (new cat_sat_moneda_html(html:$this->html_base))->select_cat_sat_moneda_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_moneda_id = $select;

        $select = (new com_tipo_cambio_html(html:$this->html_base))->select_com_tipo_cambio_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->com_tipo_cambio_id = $select;

        $select = (new cat_sat_uso_cfdi_html(html:$this->html_base))->select_cat_sat_uso_cfdi_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_uso_cfdi_id = $select;

        $select = (new cat_sat_tipo_de_comprobante_html(html:$this->html_base))->select_cat_sat_tipo_de_comprobante_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_tipo_de_comprobante_id = $select;

        $select = (new com_producto_html(html:$this->html_base))->select_com_producto_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->com_producto_id = $select;

        return $selects;
    }
     * */

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new cat_sat_forma_pago_html(html:$this->html_base))->select_cat_sat_forma_pago_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_forma_pago_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_forma_pago_id = $select;

        $select = (new cat_sat_metodo_pago_html(html:$this->html_base))->select_cat_sat_metodo_pago_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_metodo_pago_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_metodo_pago_id = $select;

        $select = (new cat_sat_moneda_html(html:$this->html_base))->select_cat_sat_moneda_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_moneda_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_moneda_id = $select;

        $select = (new com_tipo_cambio_html(html:$this->html_base))->select_com_tipo_cambio_id(
            cols: 6, con_registros:true, id_selected:$row_upd->com_tipo_cambio_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->com_tipo_cambio_id = $select;

        $select = (new cat_sat_uso_cfdi_html(html:$this->html_base))->select_cat_sat_uso_cfdi_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_uso_cfdi_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_uso_cfdi_id = $select;

        $select = (new cat_sat_tipo_de_comprobante_html(html:$this->html_base))->select_cat_sat_tipo_de_comprobante_id(
            cols: 6, con_registros:true, id_selected:$row_upd->cat_sat_tipo_de_comprobante_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_tipo_de_comprobante_id = $select;

        $select = (new com_producto_html(html:$this->html_base))->select_com_producto_id(
            cols: 6, con_registros:true, id_selected:$row_upd->com_producto_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->com_producto_id = $select;

        return $selects;
    }

    public function select_nom_conf_factura_id(int $cols, bool $con_registros, int $id_selected, PDO $link): array|string
    {
        $modelo = new nom_conf_factura(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,label: 'Configuracion Factura',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


}
