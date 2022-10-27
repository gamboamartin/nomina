<?php

namespace html;

use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\nomina\controllers\controlador_nom_nomina;
use gamboamartin\validacion\validacion;
use models\nom_nomina;
use PDO;
use stdClass;

class nom_nomina_html extends base_nominas
{

    private function asigna_inputs(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->dp_calle_pertenece_id = $inputs->selects->dp_calle_pertenece_id;
        $controler->inputs->select->em_empleado_id = $inputs->selects->em_empleado_id;
        $controler->inputs->select->fc_factura_id = $inputs->selects->fc_factura_id;
        $controler->inputs->select->cat_sat_tipo_nomina_id = $inputs->selects->cat_sat_tipo_nomina_id;
        $controler->inputs->select->im_registro_patronal_id = $inputs->selects->im_registro_patronal_id;
        $controler->inputs->num_dias_pagados = $inputs->texts->num_dias_pagados;
        $controler->inputs->fecha_inicial_pago = $inputs->texts->fecha_inicial_pago;
        $controler->inputs->fecha_final_pago = $inputs->texts->fecha_final_pago;
        $controler->inputs->fecha_pago = $inputs->texts->fecha_pago;
        $controler->inputs->folio = $inputs->texts->folio;
        $controler->inputs->fecha = $inputs->texts->fecha;
        return $controler->inputs;
    }

    private function asigna_inputs_nueva_percepcion(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {

        $data_inputs = $this->inputs_percepcion_partida(controler: $controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener inputs', data: $data_inputs);
        }

        return $data_inputs;
    }

    private function asigna_inputs_nueva_deduccion(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {
        $inputs_ = $this->asigna_input_partida(controler: $controler,inputs:  $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_);
        }

        return $inputs_;
    }

    private function asigna_inputs_otro_pago(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs->selects->nom_nomina_id;
        $controler->inputs->select->nom_otro_pago_id = $inputs->selects->nom_otro_pago_id;
        $controler->inputs->importe_gravado = $inputs->texts->importe_gravado;
        $controler->inputs->importe_exento = $inputs->texts->importe_exento;
        return $controler->inputs;
    }

    private function asigna_inputs_crea_nomina(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {

        $keys = array('cat_sat_periodicidad_pago_nom_id','cat_sat_tipo_nomina_id','em_cuenta_bancaria_id',
            'em_empleado_id','im_registro_patronal_id','nom_periodo_id','nom_conf_empleado_id','org_puesto_id',
            'cat_sat_tipo_contrato_nom_id');

        $valida = (new validacion())->valida_existencia_keys(keys:  $keys,registro: $inputs->selects,
            valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar selects', data: $valida);
        }

        $controler->inputs->select = new stdClass();
        $controler->inputs->select->im_registro_patronal_id = $inputs->selects->im_registro_patronal_id;
        $controler->inputs->select->em_empleado_id = $inputs->selects->em_empleado_id;
        $controler->inputs->select->nom_conf_empleado_id = $inputs->selects->nom_conf_empleado_id;
        $controler->inputs->select->cat_sat_tipo_nomina_id = $inputs->selects->cat_sat_tipo_nomina_id;
        $controler->inputs->select->cat_sat_periodicidad_pago_nom_id = $inputs->selects->cat_sat_periodicidad_pago_nom_id;
        $controler->inputs->select->em_cuenta_bancaria_id = $inputs->selects->em_cuenta_bancaria_id;
        $controler->inputs->select->nom_periodo_id = $inputs->selects->nom_periodo_id;
        $controler->inputs->select->org_puesto_id = $inputs->selects->org_puesto_id;
        $controler->inputs->select->cat_sat_tipo_contrato_nom_id = $inputs->selects->cat_sat_tipo_contrato_nom_id;
        $controler->inputs->neto = $inputs->texts->neto;
        $controler->inputs->codigo = $inputs->texts->codigo;
        $controler->inputs->codigo_bis = $inputs->texts->codigo_bis;
        $controler->inputs->rfc = $inputs->texts->rfc;
        $controler->inputs->curp = $inputs->texts->curp;
        $controler->inputs->nss = $inputs->texts->nss;
        $controler->inputs->folio = $inputs->texts->folio;
        $controler->inputs->fecha_inicio_rel_laboral = $inputs->texts->fecha_inicio_rel_laboral;
        $controler->inputs->fecha = $inputs->texts->fecha;
        $controler->inputs->fecha_inicial_pago = $inputs->texts->fecha_inicial_pago;
        $controler->inputs->fecha_final_pago = $inputs->texts->fecha_final_pago;
        $controler->inputs->fecha_pago = $inputs->texts->fecha_pago;
        $controler->inputs->num_dias_pagados = $inputs->texts->num_dias_pagados;
        $controler->inputs->salario_diario = $inputs->texts->salario_diario;
        $controler->inputs->salario_diario_integrado = $inputs->texts->salario_diario_integrado;
        $controler->inputs->subtotal = $inputs->texts->subtotal;
        $controler->inputs->descuento = $inputs->texts->descuento;
        $controler->inputs->total = $inputs->texts->total;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_nomina $controler, array $keys_selects, PDO $link): array|stdClass
    {
        $inputs = $this->init_alta(keys_selects: $keys_selects, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function genera_inputs_nueva_percepcion(controlador_nom_nomina $controler, array $keys_selects, PDO $link): array|stdClass
    {
        $inputs = (new nom_par_percepcion_html(html: $this->html_base))->init_alta(keys_selects: $keys_selects, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_nueva_percepcion(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function genera_inputs_nueva_deduccion(controlador_nom_nomina $controler, array $keys_selects, PDO $link): array|stdClass
    {
        $inputs = (new nom_par_deduccion_html(html: $this->html_base))->init_alta(keys_selects: $keys_selects, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_nueva_deduccion(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function genera_inputs_otro_pago(controlador_nom_nomina $controler, array $keys_selects, PDO $link): array|stdClass
    {
        $inputs = (new nom_par_otro_pago_html(html: $this->html_base))->init_alta(keys_selects: $keys_selects, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_otro_pago(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function genera_inputs_crea_nomina_neto(controlador_nom_nomina $controler, array $keys_selects, PDO $link): array|stdClass
    {
        $inputs = $this->init_alta_crea_nomina_neto(link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_otro_pago(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function init_alta_crea_nomina_neto(PDO $link, stdClass $params = new stdClass()): array|stdClass
    {
        $selects = $this->selects_alta_crea_nomina(link: $link, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_alta_crea_nomina(row_upd: new stdClass(), value_vacio: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    public function genera_inputs_crea_nomina(controlador_nom_nomina $controler, PDO $link,
                                              stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->init_alta_crea_nomina(link: $link, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_crea_nomina(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function genera_inputs_modifica(controlador_nom_nomina $controler, PDO $link,
                                            stdClass               $params = new stdClass()): array|stdClass
    {
        $keys = array('cat_sat_tipo_nomina_id','em_empleado_id','im_registro_patronal_id','nom_conf_empleado_id',
            'nom_periodo_id','org_puesto_id','cat_sat_tipo_contrato_nom_id');

        $valida = (new validacion())->valida_existencia_keys(keys:  $keys,registro: $controler->row_upd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar row upd', data: $valida);
        }


        $inputs = $this->init_modifica(link: $link, row_upd: $controler->row_upd, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);
        }

        $keys = array('cat_sat_periodicidad_pago_nom_id','cat_sat_tipo_nomina_id','em_cuenta_bancaria_id',
            'em_empleado_id','im_registro_patronal_id','nom_periodo_id','nom_conf_empleado_id','org_puesto_id');

        $valida = (new validacion())->valida_existencia_keys(keys:  $keys,registro: $inputs->selects,
            valida_vacio: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar selects', data: $valida);
        }

        $inputs_asignados = $this->asigna_inputs_crea_nomina(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }


    private function init_alta_crea_nomina(PDO $link, stdClass $params = new stdClass()): array|stdClass
    {
        $selects = $this->selects_alta_crea_nomina(link: $link, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_alta_crea_nomina(row_upd: new stdClass(), value_vacio: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    private function init_modifica(PDO $link, stdClass $row_upd, stdClass $params = new stdClass()): array|stdClass
    {
        $keys = array('cat_sat_tipo_nomina_id','em_empleado_id','im_registro_patronal_id','nom_conf_empleado_id',
            'nom_periodo_id','org_puesto_id','cat_sat_tipo_contrato_nom_id');

        $valida = (new validacion())->valida_existencia_keys(keys:  $keys,registro: $row_upd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar row upd', data: $valida);
        }


        $selects = $this->selects_selects_modifica_crea_nomina(link: $link, row_upd: $row_upd, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_modifica_crea_nomina(link: $link, row_upd: $row_upd, value_vacio: false, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->texts = $texts;
        $alta_inputs->selects = $selects;
        return $alta_inputs;
    }

    public function inputs_nom_nomina(controlador_nom_nomina $controlador, stdClass $params = new stdClass()):
    array|stdClass
    {

        $keys = array('cat_sat_tipo_nomina_id','em_empleado_id','im_registro_patronal_id','nom_conf_empleado_id',
            'nom_periodo_id','org_puesto_id','cat_sat_tipo_contrato_nom_id');

        $valida = (new validacion())->valida_existencia_keys(keys:  $keys,registro: $controlador->row_upd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar row upd', data: $valida);
        }

        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);
        }
        return $inputs;
    }

    /**
     * Genera un input de num dias pagados
     * @param int $cols no de columnas css
     * @param stdClass $row_upd registro en proceso
     * @param bool $value_vacio Si vacio deja vacio el input
     * @param bool $disabled si disabled el input queda deshabilitado
     * @return array|string
     * @version 0.308.12
     */
    private function input_num_dias_pagados(int $cols, stdClass $row_upd, bool $value_vacio,
                                           bool $disabled = false): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->input_text_required(disabled: $disabled, name: 'num_dias_pagados',
            place_holder: 'Nº dias pagados', row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $html);
        }

        $div = $this->directivas->html->div_group(cols: $cols, html: $html);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar div', data: $div);
        }

        return $div;
    }

    public function input_fecha_inicial_pago(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->fecha_required(disabled: $disabled, name: 'fecha_inicial_pago',
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

        $html = $this->directivas->fecha_required(disabled: $disabled, name: 'fecha_final_pago',
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

        $html = $this->directivas->fecha_required(disabled: $disabled, name: 'fecha_pago', place_holder: 'Fecha pago',
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


    private function selects_alta_crea_nomina(PDO $link,stdClass $params = new stdClass()): array|stdClass
    {
        $selects = new stdClass();

        $cols_im_org_puesto_id = $params->org_puesto_id->cols ?? 6;

        $select = (new org_puesto_html(html: $this->html_base))->select_org_puesto_id(
            cols: $cols_im_org_puesto_id, con_registros: true, id_selected: -1, link: $link, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->org_puesto_id = $select;

        $cols_im_registro_patronal_id = $params->im_registro_patronal_id->cols ?? 6;

        $select = (new im_registro_patronal_html(html: $this->html_base))->select_im_registro_patronal_id(
            cols: $cols_im_registro_patronal_id, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->im_registro_patronal_id = $select;

        $cols_nom_periodo_id = $params->nom_periodo_id->cols ?? 6;
        $select = (new nom_periodo_html(html: $this->html_base))->select_nom_periodo_id(
            cols: $cols_nom_periodo_id, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->nom_periodo_id = $select;

        $cols_em_empleado_id = $params->em_empleado_id->cols ?? 12;
        $select = (new em_empleado_html(html: $this->html_base))->select_em_empleado_id(
            cols: $cols_em_empleado_id, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_empleado_id = $select;

        $cols_nom_conf_empleado_id = $params->nom_conf_empleado_id->cols ?? 12;
        $select = (new nom_conf_empleado_html(html: $this->html_base))->select_nom_conf_empleado_id(
            cols: $cols_nom_conf_empleado_id, con_registros: false, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->nom_conf_empleado_id = $select;

        $cols_cat_sat_tipo_nomina_id = $params->cat_sat_tipo_nomina_id->cols ?? 6;
        $select = (new cat_sat_tipo_nomina_html(html: $this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: $cols_cat_sat_tipo_nomina_id, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;

        $cols_cat_sat_periodicidad_pago_nom_id = $params->cat_sat_periodicidad_pago_nom_id->cols ?? 6;
        $select = (new cat_sat_periodicidad_pago_nom_html(html: $this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
            cols: $cols_cat_sat_periodicidad_pago_nom_id, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_periodicidad_pago_nom_id = $select;

        $cols_em_cuenta_bancaria_id = $params->em_cuenta_bancaria_id->cols ?? 12;
        $select = (new em_cuenta_bancaria_html(html: $this->html_base))->select_em_cuenta_bancaria_id(
            cols: $cols_em_cuenta_bancaria_id, con_registros: false, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_cuenta_bancaria_id = $select;

        $cols_cat_sat_tipo_contrato_nom_id = $params->cat_sat_tipo_contrato_nom_id->cols ?? 6;
        $select = (new cat_sat_tipo_contrato_nom_html(html: $this->html_base))->select_cat_sat_tipo_contrato_nom_id(
            cols: $cols_cat_sat_tipo_contrato_nom_id, con_registros: true,
            id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_contrato_nom_id = $select;



        return $selects;
    }

    private function selects_selects_modifica_crea_nomina(PDO $link, stdClass $row_upd,
                                                          stdClass $params = new stdClass()): array|stdClass
    {

        $keys = array('cat_sat_tipo_nomina_id','em_empleado_id','im_registro_patronal_id','nom_conf_empleado_id',
            'nom_periodo_id','org_puesto_id','cat_sat_tipo_contrato_nom_id');

        $valida = (new validacion())->valida_existencia_keys(keys:  $keys,registro: $row_upd);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar row upd', data: $valida);
        }

        $selects = new stdClass();

        $cols_im_registro_patronal_id = $params->im_registro_patronal_id->cols ?? 6;

        $select = (new im_registro_patronal_html(html: $this->html_base))->select_im_registro_patronal_id(
            cols: $cols_im_registro_patronal_id, con_registros: true, id_selected: $row_upd->im_registro_patronal_id,
            link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->im_registro_patronal_id = $select;

        $cols_nom_periodo_id = $params->nom_periodo_id->cols ?? 6;
        $select = (new nom_periodo_html(html: $this->html_base))->select_nom_periodo_id(
            cols: $cols_nom_periodo_id, con_registros: true, id_selected: $row_upd->nom_periodo_id,
            link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->nom_periodo_id = $select;

        $cols_em_empleado_id = $params->em_empleado_id->cols ?? 8;
        $select = (new em_empleado_html(html: $this->html_base))->select_em_empleado_id(
            cols: $cols_em_empleado_id, con_registros: true, id_selected: $row_upd->em_empleado_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_empleado_id = $select;

        if(!isset($row_upd->nom_conf_empleado_id)){
            $row_upd->nom_conf_empleado_id = -1;
        }

        $cols_nom_conf_empleado_id = $params->nom_conf_empleado_id->cols ?? 6;
        $select = (new nom_conf_empleado_html(html: $this->html_base))->select_nom_conf_empleado_id(
            cols: $cols_nom_conf_empleado_id, con_registros: true, id_selected: $row_upd->nom_conf_empleado_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->nom_conf_empleado_id = $select;

        $cols_cat_sat_tipo_nomina_id = $params->cat_sat_tipo_nomina_id->cols ?? 6;
        $select = (new cat_sat_tipo_nomina_html(html: $this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: $cols_cat_sat_tipo_nomina_id, con_registros: true, id_selected: $row_upd->cat_sat_tipo_nomina_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;
        $cols_cat_sat_periodicidad_pago_nom_id = $params->cat_sat_periodicidad_pago_nom_id->cols ?? 6;
        $select = (new cat_sat_periodicidad_pago_nom_html(html: $this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
            cols: $cols_cat_sat_periodicidad_pago_nom_id, con_registros: true, id_selected: $row_upd->cat_sat_periodicidad_pago_nom_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_periodicidad_pago_nom_id = $select;

        $filtro['em_empleado.id'] = $row_upd->em_empleado_id;

        $cols_em_cuenta_bancaria_id = $params->em_cuenta_bancaria_id->cols ?? 12;
        $select = (new em_cuenta_bancaria_html(html: $this->html_base))->select_em_cuenta_bancaria_id(
            cols: $cols_em_cuenta_bancaria_id, con_registros: true, id_selected: $row_upd->em_cuenta_bancaria_id,
            link: $link,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_cuenta_bancaria_id = $select;



        $cols_org_puesto_id = $params->org_puesto_id->cols ?? 12;
        $select = (new org_puesto_html(html: $this->html_base))->select_org_puesto_id(
            cols: $cols_org_puesto_id, con_registros: true, id_selected: $row_upd->org_puesto_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->org_puesto_id = $select;


        $cols_cat_sat_tipo_contrato_nom_id = $params->cat_sat_tipo_contrato_nom_id->cols ?? 12;
        $select = (new cat_sat_tipo_contrato_nom_html(html: $this->html_base))->select_cat_sat_tipo_contrato_nom_id(
            cols: $cols_cat_sat_tipo_contrato_nom_id, con_registros: true,
            id_selected: $row_upd->cat_sat_tipo_contrato_nom_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_contrato_nom_id = $select;


        return $selects;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new dp_calle_pertenece_html(html: $this->html_base))->select_dp_calle_pertenece_id(
            cols: 6, con_registros: true, id_selected: $row_upd->dp_calle_pertenece_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->dp_calle_pertenece_id = $select;

        $select = (new em_empleado_html(html: $this->html_base))->select_em_empleado_id(
            cols: 12, con_registros: true, id_selected: $row_upd->em_empleado_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new fc_factura_html(html: $this->html_base))->select_fc_factura_id(
            cols: 6, con_registros: true, id_selected: $row_upd->fc_factura_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->fc_factura_id = $select;

        $select = (new cat_sat_tipo_nomina_html(html: $this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: 6, con_registros: true, id_selected: $row_upd->cat_sat_tipo_nomina_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;

        $select = (new im_registro_patronal_html(html: $this->html_base))->select_im_registro_patronal_id(
            cols: 6, con_registros: true, id_selected: $row_upd->im_registro_patronal_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->im_registro_patronal_id = $select;

        return $selects;
    }

    public function select_nom_nomina_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                         bool $disabled = false, array $filtro = array()): array|string
    {
        $modelo = new nom_nomina(link: $link);

        $select = $this->select_catalogo(cols: $cols, con_registros: $con_registros, id_selected: $id_selected,
            modelo: $modelo, disabled: $disabled, filtro: $filtro, label: 'Nomina', required: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }

    protected function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
    {
        $texts = new stdClass();

        $in_num_dias_pagados = $this->input_num_dias_pagados(cols: 6, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_num_dias_pagados);
        }
        $texts->num_dias_pagados = $in_num_dias_pagados;

        $in_fecha_inicial_pago = $this->input_fecha_inicial_pago(cols: 6, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicial_pago);
        }
        $texts->fecha_inicial_pago = $in_fecha_inicial_pago;

        $in_folio = (new fc_factura_html(html: $this->html_base))->input_folio(cols: 6, row_upd: $row_upd,
            value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_folio);
        }
        $texts->folio = $in_folio;

        $row_upd->fecha = date('Y-m-d');

        $in_fecha = (new fc_factura_html(html: $this->html_base))->input_fecha(cols: 6, row_upd: $row_upd,
            value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha);
        }
        $texts->fecha = $in_fecha;

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

    private function texts_alta_crea_nomina(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()):
    array|stdClass
    {
        $texts = new stdClass();

        $in_codigo = $this->input_codigo(cols: 4, row_upd:  $row_upd,value_vacio:  $value_vacio,
            disabled: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_codigo);
        }
        $texts->codigo = $in_codigo;

        $in_codigo_bis = $this->input_codigo_bis(cols: 4,row_upd:  $row_upd,value_vacio:  $value_vacio,
            disabled:true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_codigo_bis);
        }
        $texts->codigo_bis = $in_codigo_bis;
        
        $in_neto = $this->input_neto(cols: 4,row_upd:  $row_upd,value_vacio:  $value_vacio,
            disabled:false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_neto);
        }
        $texts->neto = $in_neto;

        $in_rfc = (new em_empleado_html(html: $this->html_base))->input_rfc(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_rfc);
        }
        $texts->rfc = $in_rfc;

        $in_curp = (new em_empleado_html(html: $this->html_base))->input_curp(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_curp);
        }
        $texts->curp = $in_curp;

        $in_nss = (new em_empleado_html(html: $this->html_base))->input_nss(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_nss);
        }
        $texts->nss = $in_nss;

        $in_nss = (new em_empleado_html(html: $this->html_base))->input_nss(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_nss);
        }
        $texts->nss = $in_nss;

        $in_folio = (new fc_factura_html(html: $this->html_base))->input_folio(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_folio);
        }
        $texts->folio = $in_folio;

        $in_fecha_inicio_rel_laboral= (new em_empleado_html(html: $this->html_base))->input_fecha_inicio_rel_laboral(cols: 4,
            row_upd: $row_upd, value_vacio: false,disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicio_rel_laboral);
        }
        $texts->fecha_inicio_rel_laboral = $in_fecha_inicio_rel_laboral;

        $row_upd->fecha = date('Y-m-d');

        $in_fecha = (new fc_factura_html(html: $this->html_base))->input_fecha(cols: 4, row_upd: $row_upd,
            value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha);
        }
        $texts->fecha = $in_fecha;

        $row_upd->fecha_inicial_pago = date('Y-m-d');

        $in_fecha_inicial_pago = $this->input_fecha_inicial_pago(cols: 4, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicial_pago);
        }
        $texts->fecha_inicial_pago = $in_fecha_inicial_pago;

        $row_upd->fecha_final_pago = date('Y-m-d');

        $in_fecha_final_pago = $this->input_fecha_final_pago(cols: 4, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_final_pago);
        }
        $texts->fecha_final_pago = $in_fecha_final_pago;

        $row_upd->fecha_pago = date('Y-m-d');

        $in_fecha_pago = $this->input_fecha_pago(cols: 4, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_pago);
        }
        $texts->fecha_pago = $in_fecha_pago;

        $in_num_dias_pagados = $this->input_num_dias_pagados(cols: 4, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_num_dias_pagados);
        }
        $texts->num_dias_pagados = $in_num_dias_pagados;

        $row_upd->salario_diario = 0;

        $in_salario_diario = (new em_empleado_html(html: $this->html_base))->input_salario_diario(cols: 4,
            row_upd: $row_upd,value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_salario_diario);
        }
        $texts->salario_diario = $in_salario_diario;

        $row_upd->salario_diario_integrado = 0;

        $in_salario_diario_integrado = (new em_empleado_html(html: $this->html_base))->input_salario_diario_integrado(
            cols: 4, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_salario_diario_integrado);
        }
        $texts->salario_diario_integrado = $in_salario_diario_integrado;

        $row_upd->subtotal = 0;

        $in_subtotal = (new fc_factura_html(html: $this->html_base))->input_subtotal(
            cols: 4, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_subtotal);
        }
        $texts->subtotal = $in_subtotal;

        $row_upd->descuento = 0;

        $in_descuento = (new fc_factura_html(html: $this->html_base))->input_descuento(
            cols: 4, row_upd: $row_upd, value_vacio: false, disabled: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_descuento);
        }
        $texts->descuento = $in_descuento;

        $row_upd->total = 0;

        $in_total = (new fc_factura_html(html: $this->html_base))->input_total(
            cols: 4, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_total);
        }
        $texts->total = $in_total;

        return $texts;
    }

    private function texts_modifica_crea_nomina(PDO $link,stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()):
    array|stdClass
    {
        $fc_factura = (new nom_nomina($link))->registro_por_id(entidad:  new fc_factura($link), id: $row_upd->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de factura ', data: $fc_factura);
        }

        $em_empleado = (new nom_nomina($link))->registro_por_id(entidad:  new em_empleado($link), id: $row_upd->em_empleado_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado ', data: $em_empleado);
        }

        $subtotal = (new nom_nomina($link))->get_sub_total_nomina(fc_factura_id: $row_upd->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el subtotal de nomina', data: $subtotal);
        }

        $descuento = (new nom_nomina($link))->get_descuento_nomina(fc_factura_id: $row_upd->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el descuento de nomina', data: $descuento);
        }

        $texts = new stdClass();

        $row_upd->neto = $subtotal - $descuento;;

        $in_neto = $this->input_neto(cols: 12, row_upd:  $row_upd,value_vacio:  $value_vacio,
            disabled: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_neto);
        }
        $texts->neto = $in_neto;

        $in_codigo = $this->input_codigo(cols: 4, row_upd:  $row_upd,value_vacio:  $value_vacio,
            disabled: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_codigo);
        }
        $texts->codigo = $in_codigo;

        $in_codigo_bis = $this->input_codigo_bis(cols: 4,row_upd:  $row_upd,value_vacio:  $value_vacio,
            disabled:true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar input',data:  $in_codigo_bis);
        }
        $texts->codigo_bis = $in_codigo_bis;

        $row_upd->rfc = $em_empleado->em_empleado_rfc;

        $in_rfc = (new em_empleado_html(html: $this->html_base))->input_rfc(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_rfc);
        }
        $texts->rfc = $in_rfc;

        $row_upd->curp = $em_empleado->em_empleado_curp;

        $in_curp = (new em_empleado_html(html: $this->html_base))->input_curp(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_curp);
        }
        $texts->curp = $in_curp;

        $row_upd->nss = $em_empleado->em_empleado_nss;

        $in_nss = (new em_empleado_html(html: $this->html_base))->input_nss(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_nss);
        }
        $texts->nss = $in_nss;

        $in_nss = (new em_empleado_html(html: $this->html_base))->input_nss(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_nss);
        }
        $texts->nss = $in_nss;

        $row_upd->folio = $fc_factura->fc_factura_folio;

        $in_folio = (new fc_factura_html(html: $this->html_base))->input_folio(cols: 4, row_upd: $row_upd,
            value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_folio);
        }
        $texts->folio = $in_folio;

        $row_upd->fecha_inicio_rel_laboral = date('Y-m-d',strtotime($em_empleado->em_empleado_fecha_inicio_rel_laboral));

        $in_fecha_inicio_rel_laboral= (new em_empleado_html(html: $this->html_base))->input_fecha_inicio_rel_laboral(cols: 4,
            row_upd: $row_upd, value_vacio: false,disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicio_rel_laboral);
        }
        $texts->fecha_inicio_rel_laboral = $in_fecha_inicio_rel_laboral;

        $row_upd->fecha = date('Y-m-d',strtotime($fc_factura->fc_factura_fecha));

        $in_fecha = (new fc_factura_html(html: $this->html_base))->input_fecha(cols: 4, row_upd: $row_upd,
            value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha);
        }
        $texts->fecha = $in_fecha;

        $in_fecha_inicial_pago = $this->input_fecha_inicial_pago(cols: 4, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_inicial_pago);
        }
        $texts->fecha_inicial_pago = $in_fecha_inicial_pago;

        $in_fecha_final_pago = $this->input_fecha_final_pago(cols: 4, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_final_pago);
        }
        $texts->fecha_final_pago = $in_fecha_final_pago;

        $in_fecha_pago = $this->input_fecha_pago(cols: 4, row_upd: $row_upd, value_vacio: false);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_fecha_pago);
        }
        $texts->fecha_pago = $in_fecha_pago;

        $in_num_dias_pagados = $this->input_num_dias_pagados(cols: 4, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_num_dias_pagados);
        }
        $texts->num_dias_pagados = $in_num_dias_pagados;

        $row_upd->salario_diario = $em_empleado->em_empleado_salario_diario;

        $in_salario_diario = (new em_empleado_html(html: $this->html_base))->input_salario_diario(cols: 4,
            row_upd: $row_upd,value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_salario_diario);
        }
        $texts->salario_diario = $in_salario_diario;

        $row_upd->salario_diario_integrado = $em_empleado->em_empleado_salario_diario_integrado;

        $in_salario_diario_integrado = (new em_empleado_html(html: $this->html_base))->input_salario_diario_integrado(
            cols: 4, row_upd: $row_upd, value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_salario_diario_integrado);
        }
        $texts->salario_diario_integrado = $in_salario_diario_integrado;

        $row_upd->subtotal = $subtotal;

        $in_subtotal = (new fc_factura_html(html: $this->html_base))->input_subtotal(
            cols: 4, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_subtotal);
        }
        $texts->subtotal = $in_subtotal;

        $row_upd->descuento = $descuento;

        $in_descuento = (new fc_factura_html(html: $this->html_base))->input_descuento(
            cols: 4, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_descuento);
        }
        $texts->descuento = $in_descuento;

        $row_upd->total = $subtotal - $descuento;

        $in_total = (new fc_factura_html(html: $this->html_base))->input_total(
            cols: 4, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_total);
        }
        $texts->total = $in_total;

        return $texts;
    }

    public function input_neto(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false): array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html =$this->directivas->input_text_required(disabled: $disabled,name: 'neto',place_holder: 'Neto',
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
