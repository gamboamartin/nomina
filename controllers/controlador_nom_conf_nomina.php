<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;

use base\controller\controler;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\links_menu;
use gamboamartin\template\html;
use html\nom_conf_nomina_html;
use gamboamartin\nomina\models\nom_conf_nomina;
use PDO;
use stdClass;
use Throwable;

class controlador_nom_conf_nomina extends _ctl_base {

    public controlador_nom_conf_percepcion $controlador_nom_conf_percepcion;
    public string $link_nom_conf_percepcion_alta_bd = '';

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new nom_conf_nomina(link: $link);
        $html_ = new nom_conf_nomina_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        $datatables = $this->init_datatable();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar datatable', data: $datatables);
            print_r($error);
            die('Error');
        }

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $configuraciones = $this->init_configuraciones();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar configuraciones', data: $configuraciones);
            print_r($error);
            die('Error');
        }

        $init_controladores = $this->init_controladores(paths_conf: $paths_conf);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar controladores', data: $init_controladores);
            print_r($error);
            die('Error');
        }

        $init_links = $this->init_links();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar links', data: $init_links);
            print_r($error);
            die('Error');
        }

    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta = $this->init_alta();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar alta', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $inputs = $this->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs', data: $inputs, header: $header, ws: $ws);
        }

        return $r_alta;
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('codigo', 'descripcion');
        $keys->selects = array();

        $init_data = array();
        $init_data['nom_conf_factura'] = "gamboamartin\\nomina";
        $init_data['cat_sat_periodicidad_pago_nom'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_tipo_nomina'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_tipo_producto'] = "gamboamartin\\cat_sat";

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    public function conf_percepciones(bool $header = true, bool $ws = false, array $not_actions = array()): array|string
    {
        $seccion = "nom_conf_percepcion";

        $data_view = new stdClass();
        $data_view->names = array('Id', 'Conf. Percepción', 'Importe Gravado','Importe Exento','Fecha Inicio',
            'Fecha Fin', 'Acciones');
        $data_view->keys_data = array($seccion . "_id", $seccion . '_descripcion', $seccion . '_importe_gravado',
            $seccion . '_importe_exento', $seccion . '_fecha_inicio', $seccion . '_fecha_fin');
        $data_view->key_actions = 'acciones';
        $data_view->namespace_model = 'gamboamartin\\nomina\\models';
        $data_view->name_model_children = $seccion;

        $contenido_table = $this->contenido_children(data_view: $data_view, next_accion: __FUNCTION__,
            not_actions: $not_actions);
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al obtener tbody', data: $contenido_table, header: $header, ws: $ws);
        }

        return $contenido_table;
    }

    public function init_configuraciones(): controler
    {
        $this->seccion_titulo = 'Configuración de Nominas';
        $this->titulo_lista = 'Registros de configuraciones de Nomina';

        return $this;
    }

    public function init_controladores(stdClass $paths_conf): controler
    {
        $this->controlador_nom_conf_percepcion = new controlador_nom_conf_percepcion(link: $this->link,
            paths_conf: $paths_conf);

        return $this;
    }

    public function init_datatable(): stdClass
    {
        $columns["nom_conf_nomina_id"]["titulo"] = "Id";
        $columns["nom_conf_factura_descripcion"]["titulo"] = "Conf. Factura";
        $columns["cat_sat_periodicidad_pago_nom_descripcion"]["titulo"] = "Periodicidad Pago";
        $columns["cat_sat_tipo_nomina_descripcion"]["titulo"] = "Tipo Nomina";
        $columns["nom_conf_nomina_n_conf_percepciones"]["titulo"] = "Conf. Percepciones";

        $filtro = array("nom_conf_nomina.id", "nom_conf_factura.descripcion",
            "cat_sat_periodicidad_pago_nom.descripcion", "cat_sat_tipo_nomina.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        return $datatables;
    }

    public function init_links(): array|string
    {
        $this->link_nom_conf_percepcion_alta_bd = $this->obj_link->link_alta_bd(link: $this->link,
            seccion: 'nom_conf_percepcion');
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener link',
                data: $this->link_nom_conf_percepcion_alta_bd);
            print_r($error);
            exit;
        }

        return $this->link_nom_conf_percepcion_alta_bd;
    }

    public function init_selects(array $keys_selects, string $key, string $label, int $id_selected = -1, int $cols = 6,
                                  bool  $con_registros = true, array $filtro = array()): array
    {
        $keys_selects = $this->key_select(cols: $cols, con_registros: $con_registros, filtro: $filtro, key: $key,
            keys_selects: $keys_selects, id_selected: $id_selected, label: $label);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        return $keys_selects;
    }

    public function init_selects_inputs(): array
    {
        $keys_selects = $this->init_selects(keys_selects: array(), key: "nom_conf_factura_id", label: "Conf. Factura",
            cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_periodicidad_pago_nom_id",
            label: "Periodicidad Pago");
        return $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_tipo_nomina_id", label: "Tipo Nomina");
    }

    protected function inputs_children(stdClass $registro): array|stdClass
    {
        $r_template = $this->controlador_nom_conf_percepcion->alta(header: false);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener template', data: $r_template);
        }

        $keys_selects = $this->controlador_nom_conf_percepcion->init_selects_inputs();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar selects', data: $keys_selects);
        }

        $keys_selects['nom_conf_nomina_id']->id_selected = $this->registro_id;
        $keys_selects['nom_conf_nomina_id']->filtro = array("nom_conf_nomina.id" => $this->registro_id);
        $keys_selects['nom_conf_nomina_id']->disabled = true;

        $inputs = $this->controlador_nom_conf_percepcion->inputs(keys_selects: $keys_selects);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener inputs', data: $inputs);
        }

        $this->inputs = $inputs;

        return $this->inputs;
    }

    protected function key_selects_txt(array $keys_selects): array
    {
        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 12, key: 'descripcion',
            keys_selects: $keys_selects, place_holder: 'Descripción');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        return $keys_selects;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $r_modifica = $this->init_modifica();
        if (errores::$error) {
            return $this->retorno_error(
                mensaje: 'Error al generar salida de template', data: $r_modifica, header: $header, ws: $ws);
        }

        $keys_selects = $this->init_selects_inputs();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al inicializar selects', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $keys_selects['nom_conf_factura_id']->id_selected = $this->registro['nom_conf_factura_id'];
        $keys_selects['cat_sat_periodicidad_pago_nom_id']->id_selected = $this->registro['cat_sat_periodicidad_pago_nom_id'];
        $keys_selects['cat_sat_tipo_nomina_id']->id_selected = $this->registro['cat_sat_tipo_nomina_id'];

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(), params_ajustados: array());
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al integrar base', data: $base, header: $header, ws: $ws);
        }

        return $r_modifica;
    }
}
