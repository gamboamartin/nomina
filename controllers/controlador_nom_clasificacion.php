<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\cobranza\controllers;

use gamboamartin\cobranza\html\cob_caja_html;
use gamboamartin\cobranza\html\cob_deuda_html;
use gamboamartin\cobranza\html\cob_pago_html;
use gamboamartin\cobranza\models\cob_caja;
use gamboamartin\cobranza\models\cob_pago;
use gamboamartin\errores\errores;
use gamboamartin\system\_ctl_base;
use gamboamartin\system\links_menu;

use gamboamartin\template\html;
use html\cat_sat_forma_pago_html;

use html\bn_cuenta_html;
use PDO;
use stdClass;

class controlador_cob_pago extends _ctl_base {

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){

        $modelo = new cob_pago(link: $link);
        $html_ = new cob_pago_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id:$this->registro_id);


        $datatables = new stdClass();
        $datatables->columns = array();
        $datatables->columns['cob_pago_id']['titulo'] = 'Id';
        $datatables->columns['cob_pago_codigo']['titulo'] = 'Cod';
        $datatables->columns['cob_pago_descripcion']['titulo'] = 'Observaciones';
        $datatables->columns['cob_pago_fecha_de_pago']['titulo'] = 'Fecha de Pago';
        $datatables->columns['cob_pago_monto']['titulo'] = 'Monto';

        $datatables->filtro = array();
        $datatables->filtro[] = 'cob_pago.id';
        $datatables->filtro[] = 'cob_pago.codigo';
        $datatables->filtro[] = 'cob_pago.descripcion';
        $datatables->filtro[] = 'cob_pago.fecha_de_pago';
        $datatables->filtro[] = 'cob_pago.monto';


        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link,
            datatables: $datatables, paths_conf: $paths_conf);

        $this->titulo_lista = 'Pago';

        $this->lista_get_data = true;
    }

    public function alta(bool $header, bool $ws = false): array|string
    {

        $r_alta = $this->init_alta();
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al inicializar alta',data:  $r_alta, header: $header,ws:  $ws);
        }


        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'cob_deuda_id',
            keys_selects: array(), id_selected: -1, label: 'Deuda');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'bn_cuenta_id',
            keys_selects: $keys_selects, id_selected: -1, label: 'Cuenta');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'cat_sat_forma_pago_id',
            keys_selects: $keys_selects, id_selected: -1, label: 'Forma de pago');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'cob_caja_id',
            keys_selects: $keys_selects, id_selected: -1, label: 'Caja');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }






        $keys_selects['descripcion'] = new stdClass();
        $keys_selects['descripcion']->cols = 6;

        $keys_selects['fecha de pago'] = new stdClass();
        $keys_selects['fecha de pago']->cols = 12;

        $keys_selects['Monto'] = new stdClass();
        $keys_selects['Monto']->cols = 6;



        $inputs = $this->inputs(keys_selects: $keys_selects);
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al obtener inputs',data:  $inputs, header: $header,ws:  $ws);
        }



        return $r_alta;
    }

    protected function campos_view(): array
    {
        $keys = new stdClass();
        $keys->inputs = array('codigo','descripcion','monto');
        $keys->selects = array();
        $keys->fechas = array('fecha_de_pago');

        $init_data = array();
        $init_data['cob_deuda'] = "gamboamartin\\cobranza";
        $init_data['bn_cuenta'] = "gamboamartin\\banco";
        $init_data['cat_sat_forma_pago'] = "gamboamartin\\cat_sat";
        $init_data['cob_caja'] = "gamboamartin\\cobranza";
        $campos_view = $this->campos_view_base(init_data: $init_data,keys:  $keys);

        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar campo view',data:  $campos_view);
        }


        return $campos_view;
    }

    protected function inputs_children(stdClass $registro): stdClass|array
    {
        $select_cob_deuda_id = (new cob_deuda_html(html: $this->html_base))->select_cob_deuda_id(
            cols:6,con_registros: true,id_selected:  -1,link:  $this->link);

        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener select_bn_tipo_cuenta_id',data:  $select_cob_deuda_id);
        }


        $select_bn_cuenta_id = (new bn_cuenta_html(html: $this->html_base))->select_bn_cuenta_id(
            cols:6,con_registros: true,id_selected:  -1,link:  $this->link);

        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener select_org_sucursal_id',data:  $select_bn_cuenta_id);
        }

        $select_cat_sat_forma_pago_id = (new cat_sat_forma_pago_html(html: $this->html_base))->select_cat_sat_forma_pago_id(
            cols:6,con_registros: true,id_selected:  -1,link:  $this->link);

        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener select_cat_sat_forma_pago_id',data:  $select_cat_sat_forma_pago_id);
        }

        $select_cob_caja_id = (new cob_caja_html(html: $this->html_base))->select_cob_caja_id(
            cols:6,con_registros: true,id_selected:  -1,link:  $this->link);

        if(errores::$error){
            return $this->errores->error(
                mensaje: 'Error al obtener select_cob_caja_id',data:  $select_cob_caja_id);
        }

        $this->inputs = new stdClass();
        $this->inputs->select = new stdClass();
        $this->inputs->select->cob_deuda_id = $select_cob_deuda_id;
        $this->inputs->select->bn_cuenta_id = $select_bn_cuenta_id;
        $this->inputs->select->cat_sat_forma_pago_id = $select_cat_sat_forma_pago_id;
        $this->inputs->select->cob_caja_id = $select_cob_caja_id;


        return $this->inputs;
    }


    protected function key_selects_txt(array $keys_selects): array
    {

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6, key: 'codigo', keys_selects: $keys_selects, place_holder: 'Cod');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al maquetar key_selects', data: $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 6,key: 'descripcion', keys_selects:$keys_selects, place_holder: 'Observaciones');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 12,key: 'fecha_de_pago', keys_selects:$keys_selects, place_holder: 'Fecha de Pago');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects);
        }

        $keys_selects = (new \base\controller\init())->key_select_txt(cols: 12,key: 'monto', keys_selects:$keys_selects, place_holder: 'Monto');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects);
        }

        return $keys_selects;
    }

    public function modifica(
        bool $header, bool $ws = false): array|stdClass
    {
        $r_modifica = $this->init_modifica(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(
                mensaje: 'Error al generar salida de template',data:  $r_modifica,header: $header,ws: $ws);
        }


        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'cob_deuda_id',
            keys_selects: array(), id_selected: $this->registro['cob_deuda_id'], label: 'Deuda');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'bn_cuenta_id',
            keys_selects: $keys_selects, id_selected: $this->registro['bn_cuenta_id'], label: 'Cuenta');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }

        $keys_selects = $this->key_select(cols:12, con_registros: true,filtro:  array(), key: 'cat_sat_forma_pago_id',
            keys_selects: $keys_selects, id_selected: $this->registro['cat_sat_forma_pago_id'], label: 'Forma de pago');
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar key_selects',data:  $keys_selects, header: $header,ws:  $ws);
        }


        $keys_selects['descripcion'] = new stdClass();
        $keys_selects['descripcion']->cols = 6;

        $keys_selects['codigo'] = new stdClass();
        $keys_selects['codigo']->disabled = true;

        $keys_selects['Fecha de Pago'] = new stdClass();
        $keys_selects['Fecha de Pago']->cols = 6;

        $keys_selects['Monto'] = new stdClass();
        $keys_selects['Monto']->cols = 6;

        $base = $this->base_upd(keys_selects: $keys_selects, params: array(),params_ajustados: array());
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al integrar base',data:  $base, header: $header,ws:  $ws);
        }




        return $r_modifica;
    }




}