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
use html\nom_conf_factura_html;
use gamboamartin\nomina\models\nom_conf_factura;
use PDO;
use stdClass;

class controlador_nom_conf_factura extends _ctl_base {

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new nom_conf_factura(link: $link);
        $html_ = new nom_conf_factura_html(html: $html);
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
        $init_data['cat_sat_forma_pago'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_metodo_pago'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_moneda'] = "gamboamartin\\cat_sat";
        $init_data['com_tipo_cambio'] = "gamboamartin\\comercial";
        $init_data['cat_sat_uso_cfdi'] = "gamboamartin\\cat_sat";
        $init_data['cat_sat_tipo_de_comprobante'] = "gamboamartin\\cat_sat";
        $init_data['com_producto'] = "gamboamartin\\comercial";

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    public function init_configuraciones(): controler
    {
        $this->seccion_titulo = 'Configuración de Factura';
        $this->titulo_lista = 'Registros de configuraciones de Factura';

        return $this;
    }

    public function init_datatable(): stdClass
    {
        $columns["nom_conf_factura_id"]["titulo"] = "Id";
        $columns["cat_sat_forma_pago_descripcion"]["titulo"] = "Forma Pago";
        $columns["cat_sat_metodo_pago_descripcion"]["titulo"] = "Método Pago";
        $columns["cat_sat_moneda_descripcion"]["titulo"] = "Moneda";
        $columns["com_tipo_cambio_descripcion"]["titulo"] = "Tipo Cambio";
        $columns["cat_sat_uso_cfdi_descripcion"]["titulo"] = "Uso CFDI";
        $columns["cat_sat_tipo_de_comprobante_descripcion"]["titulo"] = "Tipo Comprobante";
        $columns["com_producto_descripcion"]["titulo"] = "Producto";

        $filtro = array("nom_conf_factura.id","cat_sat_forma_pago.descripcion",
            "cat_sat_metodo_pago.descripcion","cat_sat_moneda.descripcion","com_tipo_cambio.descripcion",
            "cat_sat_uso_cfdi.descripcion","cat_sat_tipo_de_comprobante.descripcion","com_producto.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        return $datatables;
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
        $keys_selects = $this->init_selects(keys_selects: array(), key: "cat_sat_forma_pago_id", label: "Forma Pago");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_metodo_pago_id", label: "Método Pago");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_moneda_id", label: "Moneda");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "com_tipo_cambio_id", label: "Tipo Cambio");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_uso_cfdi_id", label: "Uso CFDI");
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "cat_sat_tipo_de_comprobante_id",
            label: "Tipo Comprobante");
        return $this->init_selects(keys_selects: $keys_selects, key: "com_producto_id",
            label: "Producto",cols: 12);
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

        $keys_selects['cat_sat_forma_pago_id']->id_selected = $this->registro['cat_sat_forma_pago_id'];
        $keys_selects['cat_sat_metodo_pago_id']->id_selected = $this->registro['cat_sat_metodo_pago_id'];
        $keys_selects['cat_sat_moneda_id']->id_selected = $this->registro['cat_sat_moneda_id'];
        $keys_selects['com_tipo_cambio_id']->id_selected = $this->registro['com_tipo_cambio_id'];
        $keys_selects['cat_sat_uso_cfdi_id']->id_selected = $this->registro['cat_sat_uso_cfdi_id'];
        $keys_selects['cat_sat_tipo_de_comprobante_id']->id_selected = $this->registro['cat_sat_tipo_de_comprobante_id'];
        $keys_selects['com_producto_id']->id_selected = $this->registro['com_producto_id'];

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(), params_ajustados: array());
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al integrar base', data: $base, header: $header, ws: $ws);
        }

        return $r_modifica;
    }
}
