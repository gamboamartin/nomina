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
use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\links_menu;
use gamboamartin\template\html;
use html\nom_conf_empleado_html;
use gamboamartin\nomina\models\nom_conf_empleado;
use PDO;
use stdClass;

class controlador_nom_conf_empleado extends _ctl_base
{

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new nom_conf_empleado(link: $link);
        $html_ = new nom_conf_empleado_html(html: $html);
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
        $init_data['em_empleado'] = "gamboamartin\\empleado";
        $init_data['em_cuenta_bancaria'] = "gamboamartin\\empleado";
        $init_data['nom_conf_nomina'] = "gamboamartin\\nomina";

        $campos_view = $this->campos_view_base(init_data: $init_data, keys: $keys);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar campo view', data: $campos_view);
        }

        return $campos_view;
    }

    public function get_configuraciones_empleado(bool $header, bool $ws = true): array|stdClass
    {
        $keys['em_empleado'] = array("id");

        $salida = $this->get_out(header: $header, keys: $keys, ws: $ws);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar salida', data: $salida, header: $header, ws: $ws);
        }
        return $salida;
    }

    protected function init_configuraciones(): controler
    {
        $this->seccion_titulo = 'Configuraciones';
        $this->titulo_lista = 'Configuracion de Empleados';

        $this->lista_get_data = true;

        return $this;
    }

    protected function init_datatable(): stdClass
    {
        $columns["nom_conf_empleado_id"]["titulo"] = "Id";
        $columns["em_empleado_nombre"]["titulo"] = "Empleado";
        $columns["em_empleado_nombre"]["campos"] = array("em_empleado_ap", "em_empleado_am");
        $columns["em_cuenta_bancaria_num_cuenta"]["titulo"] = "Num. Cuenta";
        $columns["nom_conf_nomina_id"]["titulo"] = "Nómina";

        $filtro = array("nom_conf_empleado.id");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        return $datatables;
    }

    protected function init_selects(array $keys_selects, string $key, string $label, int $id_selected = -1, int $cols = 6,
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
        $keys_selects = $this->init_selects(keys_selects: array(), key: "em_empleado_id", label: "Empleado",
            cols: 12);
        $keys_selects = $this->init_selects(keys_selects: $keys_selects, key: "em_cuenta_bancaria_id", label: "Cuenta Bancaria",
            con_registros: false);
        return $this->init_selects(keys_selects: $keys_selects, key: "nom_conf_nomina_id", label: "Conf. Nomina");
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

        $em_cuenta_bancaria = (new em_cuenta_bancaria($this->link))->registro(registro_id: $this->registro['em_cuenta_bancaria_id']);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener cuenta bancaria', data: $keys_selects, header: $header,
                ws: $ws);
        }

        $keys_selects['em_empleado_id']->id_selected = $em_cuenta_bancaria['em_empleado_id'];

        $keys_selects['em_cuenta_bancaria_id']->con_registros = true;
        $keys_selects['em_cuenta_bancaria_id']->filtro = array("em_empleado.id" => $em_cuenta_bancaria['em_empleado_id']);
        $keys_selects['em_cuenta_bancaria_id']->id_selected = $this->registro['em_cuenta_bancaria_id'];

        $keys_selects['nom_conf_nomina_id']->id_selected = $this->registro['nom_conf_nomina_id'];

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(), params_ajustados: array());
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al integrar base', data: $base, header: $header, ws: $ws);
        }

        return $r_modifica;
    }


}
