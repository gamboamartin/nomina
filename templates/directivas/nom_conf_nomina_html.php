<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_conf_nomina;

use gamboamartin\system\html_controler;
use models\nom_conf_nomina;
use models\nom_conf_percepcion;
use PDO;
use stdClass;

class nom_conf_nomina_html extends html_controler {

    private function asigna_inputs(controlador_nom_conf_nomina $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_conf_factura_id = $inputs->selects->nom_conf_factura_id;
        $controler->inputs->select->cat_sat_periodicidad_pago_nom_id = $inputs->selects->cat_sat_periodicidad_pago_nom_id;
        $controler->inputs->select->cat_sat_tipo_nomina_id = $inputs->selects->cat_sat_tipo_nomina_id;

        return $controler->inputs;
    }

    private function asigna_inputs_asigna_percepcion(controlador_nom_conf_nomina $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_conf_nomina_id = $inputs->selects->nom_conf_nomina_id;
        $controler->inputs->select->nom_percepcion_id = $inputs->selects->nom_percepcion_id;
        $controler->inputs->importe_gravado = $inputs->texts->importe_gravado;
        $controler->inputs->importe_exento = $inputs->texts->importe_exento;
        $controler->inputs->fecha_inicio = $inputs->texts->fecha_inicio;
        $controler->inputs->fecha_fin = $inputs->texts->fecha_fin;
        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_conf_nomina $controler, array $keys_selects, PDO $link): array|stdClass
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

    private function genera_inputs_modifica(controlador_nom_conf_nomina $controler,PDO $link,
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

    public function genera_inputs_asigna_percepcion(controlador_nom_conf_nomina $controler, array $keys_selects, PDO $link): array|stdClass
    {
        $inputs = (new nom_conf_percepcion_html(html: $this->html_base))->init_alta2(modelo: $controler->modelo,link: $link,keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_asigna_percepcion(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs;
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

    public function inputs_nom_conf_nomina(controlador_nom_conf_nomina $controlador,
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

        $select = (new nom_conf_factura_html(html:$this->html_base))->select_nom_conf_factura_id(
            cols: 12, con_registros:true, id_selected:$row_upd->nom_conf_factura_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_conf_factura_id = $select;

        $select = (new cat_sat_periodicidad_pago_nom_html(html:$this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
            cols: 12, con_registros:true, id_selected:$row_upd->cat_sat_periodicidad_pago_nom_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_periodicidad_pago_nom_id = $select;

        $select = (new cat_sat_tipo_nomina_html(html:$this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: 12, con_registros:true, id_selected:$row_upd->cat_sat_tipo_nomina_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;

        return $selects;
    }

    public function select_nom_conf_nomina_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                           bool $required = true): array|string
    {
        $modelo = new nom_conf_nomina(link: $link);

        $extra_params_keys[] = 'nom_conf_nomina_id';
        $extra_params_keys[] = 'nom_conf_nomina_cat_sat_periodicidad_pago_nom_id';
        $extra_params_keys[] = 'nom_conf_nomina_cat_sat_tipo_nomina_id';

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo, extra_params_keys:$extra_params_keys, label: 'Configuracion Nomina',required: $required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }




}
