<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\cat_sat_moneda_html;
use html\com_cliente_html;
use html\com_producto_html;
use html\com_sucursal_html;
use html\em_empleado_html;
use html\nom_conf_factura_html;
use html\nom_percepcion_html;
use gamboamartin\nomina\models\com_cliente;
use gamboamartin\nomina\models\com_producto;
use gamboamartin\nomina\models\com_sucursal;
use gamboamartin\nomina\models\em_empleado;
use gamboamartin\nomina\models\nom_conf_factura;
use gamboamartin\nomina\models\nom_percepcion;
use PDO;
use stdClass;

class controlador_nom_conf_factura extends system {

    public array $keys_selects = array();

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_conf_factura(link: $link);
        $html_ = new nom_conf_factura_html(html: $html);
        $obj_link = new links_menu(link: $link,registro_id: $this->registro_id);

        $columns["nom_conf_factura_id"]["titulo"] = "Id";
        $columns["cat_sat_forma_pago_descripcion"]["titulo"] = "Forma Pago";
        $columns["cat_sat_metodo_pago_descripcion"]["titulo"] = "Metodo Pago";
        $columns["cat_sat_moneda_descripcion"]["titulo"] = "Moneda";
        $columns["com_tipo_cambio_descripcion"]["titulo"] = "Tipo Cambio";
        $columns["cat_sat_uso_cfdi_descripcion"]["titulo"] = "CFDI";
        $columns["cat_sat_tipo_de_comprobante_descripcion"]["titulo"] = "Tipo Comprobante";
        $columns["com_producto_descripcion"]["titulo"] = "Producto";

        $filtro = array("nom_conf_factura.id","cat_sat_forma_pago.descripcion",
            "cat_sat_metodo_pago.descripcion","cat_sat_moneda.descripcion","com_tipo_cambio.descripcion",
            "cat_sat_uso_cfdi.descripcion","cat_sat_tipo_de_comprobante.descripcion","com_producto.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->titulo_lista = 'Configuracion Factura';

        $propiedades = $this->inicializa_priedades();
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar propiedades',data:  $propiedades);
            print_r($error);
            die('Error');
        }
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta =  parent::alta(header: false);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $r_alta, header: $header,ws:$ws);
        }

        $inputs = $this->genera_inputs(keys_selects:  $this->keys_selects);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $inputs);
            print_r($error);
            die('Error');
        }

        return $r_alta;
    }

    public function asignar_propiedad(string $identificador, mixed $propiedades)
    {
        if (!array_key_exists($identificador,$this->keys_selects)){
            $this->keys_selects[$identificador] = new stdClass();
        }

        foreach ($propiedades as $key => $value){
            $this->keys_selects[$identificador]->$key = $value;
        }
    }

    private function base(): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false, ws: false);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $this->asignar_propiedad(identificador:'cat_sat_forma_pago_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_forma_pago_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'cat_sat_metodo_pago_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_metodo_pago_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'cat_sat_moneda_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_moneda_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'com_tipo_cambio_id',
            propiedades: ["id_selected"=>$this->row_upd->com_tipo_cambio_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'cat_sat_uso_cfdi_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_uso_cfdi_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'cat_sat_tipo_de_comprobante_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_tipo_de_comprobante_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'com_producto_id',
            propiedades: ["id_selected"=>$this->row_upd->com_producto_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $inputs = $this->genera_inputs(keys_selects:  $this->keys_selects);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }

        $data = new stdClass();
        $data->template = $r_modifica;
        $data->inputs = $inputs;

        return $data;
    }

    private function inicializa_priedades(): array
    {
        $identificador = "cat_sat_forma_pago_id";
        $propiedades = array("label" => "Forma Pago");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "cat_sat_metodo_pago_id";
        $propiedades = array("label" => "Metodo Pago");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "cat_sat_moneda_id";
        $propiedades = array("label" => "Moneda");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "com_tipo_cambio_id";
        $propiedades = array("label" => "Tipo Cambio");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "cat_sat_uso_cfdi_id";
        $propiedades = array("label" => "CFDI");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "cat_sat_tipo_de_comprobante_id";
        $propiedades = array("label" => "Tipo Comprobante");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "com_producto_id";
        $propiedades = array("label" => "Producto");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "codigo";
        $propiedades = array("place_holder" => "Codigo");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "codigo_bis";
        $propiedades = array("place_holder" => "Codigo BIS");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        return $this->keys_selects;
    }

    public function modifica(bool $header, bool $ws = false): array|stdClass
    {
        $base = $this->base();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar datos',data:  $base,
                header: $header,ws:$ws);
        }

        return $base->template;
    }
}
