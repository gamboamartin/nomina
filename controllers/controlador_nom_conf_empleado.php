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
use html\nom_conf_empleado_html;
use html\nom_conf_factura_html;
use html\nom_percepcion_html;
use models\com_cliente;
use models\com_producto;
use models\com_sucursal;
use models\em_empleado;
use models\nom_conf_empleado;
use models\nom_conf_factura;
use models\nom_percepcion;
use PDO;
use stdClass;

class controlador_nom_conf_empleado extends system {

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_conf_empleado(link: $link);
        $html_ = new nom_conf_empleado_html(html: $html);
        $obj_link = new links_menu($this->registro_id);
        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Configuracion Empleado';
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta =  parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $r_alta, header: $header,ws:$ws);
        }

        $inputs = (new nom_conf_empleado_html(html: $this->html_base))->genera_inputs_alta(controler: $this, link: $this->link);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    public function modifica(bool $header, bool $ws = false, string $breadcrumbs = '', bool $aplica_form = true,
                             bool $muestra_btn = true): array|string
    {
        $base = $this->base();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar datos',data:  $base,
                header: $header,ws:$ws);
        }

        return $base->template;
    }

    private function base(stdClass $params = new stdClass()): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false,aplica_form:  false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $inputs = (new nom_conf_empleado_html(html: $this->html_base))->inputs_nom_conf_empleado (
            controlador:$this, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }

        $data = new stdClass();
        $data->template = $r_modifica;
        $data->inputs = $inputs;

        return $data;
    }

}
