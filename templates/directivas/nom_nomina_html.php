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
use models\fc_factura;
use models\nom_nomina;
use PDO;
use stdClass;

class nom_nomina_html extends html_controler
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
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs->selects->nom_nomina_id;
        $controler->inputs->select->nom_percepcion_id = $inputs->selects->nom_percepcion_id;
        $controler->inputs->importe_gravado = $inputs->texts->importe_gravado;
        $controler->inputs->importe_exento = $inputs->texts->importe_exento;
        return $controler->inputs;
    }

    private function asigna_inputs_nueva_deduccion(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs->selects->nom_nomina_id;
        $controler->inputs->select->nom_deduccion_id = $inputs->selects->nom_deduccion_id;
        $controler->inputs->importe_gravado = $inputs->texts->importe_gravado;
        $controler->inputs->importe_exento = $inputs->texts->importe_exento;
        return $controler->inputs;
    }

    private function asigna_inputs_crea_nomina(controlador_nom_nomina $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->im_registro_patronal_id = $inputs->selects->im_registro_patronal_id;
        $controler->inputs->select->em_empleado_id = $inputs->selects->em_empleado_id;
        $controler->inputs->select->cat_sat_tipo_nomina_id = $inputs->selects->cat_sat_tipo_nomina_id;
        $controler->inputs->select->cat_sat_periodicidad_pago_nom_id = $inputs->selects->cat_sat_periodicidad_pago_nom_id;
        $controler->inputs->select->em_cuenta_bancaria_id = $inputs->selects->em_cuenta_bancaria_id;
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

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_nomina $controler, PDO $link): array|stdClass
    {
        $inputs = $this->init_alta(link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function genera_inputs_nueva_percepcion(controlador_nom_nomina $controler, PDO $link, 
                                                   stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = (new nom_par_percepcion_html(html: $this->html_base))->init_alta(
            link: $link, nom_nomina_id: $controler->registro_id, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_nueva_percepcion(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function genera_inputs_nueva_deduccion(controlador_nom_nomina $controler, PDO $link,
                                                  stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = (new nom_par_deduccion_html(html: $this->html_base))->init_alta(link: $link,
            nom_nomina_id: $controler->registro_id, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_nueva_deduccion(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    public function genera_inputs_crea_nomina(controlador_nom_nomina $controler, PDO $link): array|stdClass
    {
        $inputs = $this->init_alta_crea_nomina(link: $link);
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
        $inputs = $this->init_modifica(link: $link, row_upd: $controler->row_upd, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);

        }
        $inputs_asignados = $this->asigna_inputs_crea_nomina(controler: $controler, inputs: $inputs);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar inputs', data: $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function init_alta(PDO $link): array|stdClass
    {
        $selects = $this->selects_alta(link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar selects', data: $selects);
        }

        $texts = $this->texts_alta(row_upd: new stdClass(), value_vacio: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar texts', data: $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    private function init_alta_crea_nomina(PDO $link): array|stdClass
    {
        $selects = $this->selects_alta_crea_nomina(link: $link);
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
        $selects = $this->selects_selects_modifica_crea_nomina(link: $link, row_upd: $row_upd);
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
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar inputs', data: $inputs);
        }
        return $inputs;
    }

    public function input_num_dias_pagados(int $cols, stdClass $row_upd, bool $value_vacio, bool $disabled = false):
    array|string
    {
        $valida = $this->directivas->valida_cols(cols: $cols);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar columnas', data: $valida);
        }

        $html = $this->directivas->input_text_required(disable: $disabled, name: 'num_dias_pagados',
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

    private function selects_alta(PDO $link): array|stdClass
    {
        $selects = new stdClass();

        $select = (new dp_calle_pertenece_html(html: $this->html_base))->select_dp_calle_pertenece_id(
            cols: 6, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->dp_calle_pertenece_id = $select;

        $select = (new em_empleado_html(html: $this->html_base))->select_em_empleado_id(
            cols: 12, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new fc_factura_html(html: $this->html_base))->select_fc_factura_id(
            cols: 6, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->fc_factura_id = $select;

        $select = (new cat_sat_tipo_nomina_html(html: $this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: 6, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;

        $select = (new im_registro_patronal_html(html: $this->html_base))->select_im_registro_patronal_id(
            cols: 6, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->im_registro_patronal_id = $select;

        return $selects;
    }

    private function selects_alta_crea_nomina(PDO $link): array|stdClass
    {
        $selects = new stdClass();

        $select = (new im_registro_patronal_html(html: $this->html_base))->select_im_registro_patronal_id(
            cols: 12, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->im_registro_patronal_id = $select;

        $select = (new em_empleado_html(html: $this->html_base))->select_em_empleado_id(
            cols: 12, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new cat_sat_tipo_nomina_html(html: $this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: 6, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;

        $select = (new cat_sat_periodicidad_pago_nom_html(html: $this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
            cols: 6, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_periodicidad_pago_nom_id = $select;

        $select = (new em_cuenta_bancaria_html(html: $this->html_base))->select_em_cuenta_bancaria_id(
            cols: 12, con_registros: true, id_selected: -1, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_cuenta_bancaria_id = $select;

        return $selects;
    }

    private function selects_selects_modifica_crea_nomina(PDO $link, stdClass $row_upd): array|stdClass
    {

        $selects = new stdClass();

        $select = (new im_registro_patronal_html(html: $this->html_base))->select_im_registro_patronal_id(
            cols: 6, con_registros: true, id_selected: $row_upd->im_registro_patronal_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->im_registro_patronal_id = $select;

        $select = (new em_empleado_html(html: $this->html_base))->select_em_empleado_id(
            cols: 6, con_registros: true, id_selected: $row_upd->em_empleado_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new cat_sat_tipo_nomina_html(html: $this->html_base))->select_cat_sat_tipo_nomina_id(
            cols: 6, con_registros: true, id_selected: $row_upd->cat_sat_tipo_nomina_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_tipo_nomina_id = $select;

        $select = (new cat_sat_periodicidad_pago_nom_html(html: $this->html_base))->select_cat_sat_periodicidad_pago_nom_id(
            cols: 6, con_registros: true, id_selected: $row_upd->cat_sat_periodicidad_pago_nom_id, link: $link);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->cat_sat_periodicidad_pago_nom_id = $select;

        $filtro['em_empleado.id'] = $row_upd->em_empleado_id;
        $select = (new em_cuenta_bancaria_html(html: $this->html_base))->select_em_cuenta_bancaria_id(
            cols: 12, con_registros: true, id_selected: $row_upd->em_cuenta_bancaria_id, link: $link,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        $selects->em_cuenta_bancaria_id = $select;

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

    private function texts_alta(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()): array|stdClass
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

        $in_salario_diario = (new em_empleado_html(html: $this->html_base))->input_salario_diario(cols: 4,
            row_upd: $row_upd,value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_salario_diario);
        }
        $texts->salario_diario = $in_salario_diario;

        $in_salario_diario_integrado = (new em_empleado_html(html: $this->html_base))->input_salario_diario_integrado(
            cols: 4, row_upd: $row_upd, value_vacio: $value_vacio, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_salario_diario_integrado);
        }
        $texts->salario_diario_integrado = $in_salario_diario_integrado;

        $in_subtotal = (new fc_factura_html(html: $this->html_base))->input_subtotal(
            cols: 6, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_subtotal);
        }
        $texts->subtotal = $in_subtotal;

        return $texts;
    }

    private function texts_modifica_crea_nomina(PDO $link,stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()):
    array|stdClass
    {
        $fc_factura = (new nom_nomina($link))->registros_por_id(entidad:  new fc_factura($link), id: $row_upd->fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de factura ', data: $fc_factura);
        }

        $em_empleado = (new nom_nomina($link))->registros_por_id(entidad:  new em_empleado($link), id: $row_upd->em_empleado_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado ', data: $em_empleado);
        }

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

        $in_subtotal = (new fc_factura_html(html: $this->html_base))->input_subtotal(
            cols: 6, row_upd: $row_upd, value_vacio: false, disabled: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_subtotal);
        }
        $texts->subtotal = $in_subtotal;

        return $texts;
    }
}
