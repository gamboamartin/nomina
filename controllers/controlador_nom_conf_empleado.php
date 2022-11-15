<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;

use gamboamartin\empleado\models\em_cuenta_bancaria;
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

    public array $keys_selects = array();

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_conf_empleado(link: $link);
        $html_ = new nom_conf_empleado_html(html: $html);
        $obj_link = new links_menu(link: $link,registro_id: $this->registro_id);

        $columns["nom_conf_empleado_id"]["titulo"] = "Id";
        $columns["nom_conf_empleado_descripcion"]["titulo"] = "Descripcion";
        $columns["em_cuenta_bancaria_descripcion"]["titulo"] = "Cuenta Bancaria";
        $columns["nom_conf_nomina_descripcion"]["titulo"] = "Conf. Nomina";

        $filtro = array("nom_conf_empleado.id","nom_conf_empleado.descripcion", "nom_conf_empleado.descripcion",
            "em_cuenta_bancaria.descripcion","nom_conf_nomina.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->titulo_lista = 'Configuracion Empleado';

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
        $r_modifica =  parent::modifica(header: false,aplica_form:  false);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $em_empleado = (new em_cuenta_bancaria($this->link))->get_empleado(
            em_cuenta_bancaria_id: $this->row_upd->em_cuenta_bancaria_id);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al obtener al empleado', data: $em_empleado);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'em_empleado_id',
            propiedades: ["id_selected"=> $em_empleado["em_cuenta_bancaria_em_empleado_id"],"disabled" => true,
                "filtro" => array('em_empleado.id' => $em_empleado["em_cuenta_bancaria_em_empleado_id"])]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'em_cuenta_bancaria_id',
            propiedades: ["id_selected"=>$this->row_upd->em_cuenta_bancaria_id,"con_registros" => true,
                "filtro" => array('em_empleado.id' => $em_empleado["em_cuenta_bancaria_em_empleado_id"])]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'nom_conf_nomina_id',
            propiedades: ["id_selected"=>$this->row_upd->nom_conf_nomina_id]);
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
        $identificador = "em_cuenta_bancaria_id";
        $propiedades = array("label" => "Cuenta Bancaria","con_registros" => false);
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "nom_conf_nomina_id";
        $propiedades = array("label" => "Conf. Nomina");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "em_empleado_id";
        $propiedades = array("label" => "Empleado");
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

    public function get_configuraciones_empleado(bool $header, bool $ws = true): array|stdClass
    {
        $keys['em_empleado'] = array("id");

        $salida = $this->get_out(header: $header,keys: $keys, ws: $ws);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar salida',data:  $salida,header: $header,ws: $ws);
        }
        return $salida;
    }

}
