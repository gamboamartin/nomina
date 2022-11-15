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
use gamboamartin\system\actions;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\nom_conf_nomina_html;
use html\nom_conf_percepcion_html;
use html\nom_deduccion_html;
use models\nom_conf_nomina;
use models\nom_conf_percepcion;
use PDO;
use stdClass;
use Throwable;

class controlador_nom_conf_nomina extends system {

    public array $keys_selects = array();
    public controlador_nom_conf_percepcion $controlador_nom_conf_percepcion;

    public string $link_nom_conf_percepcion_alta_bd = '';
    public stdClass $percepciones;
    public int $nom_conf_percepcion_id = -1;

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_conf_nomina(link: $link);
        $html_ = new nom_conf_nomina_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        $columns["nom_conf_nomina_id"]["titulo"] = "Id";
        $columns["nom_conf_nomina_codigo"]["titulo"] = "Codigo";
        $columns["nom_conf_factura_descripcion"]["titulo"] = "Conf. Factura";
        $columns["cat_sat_periodicidad_pago_nom_descripcion"]["titulo"] = "Periodicidad Pago";
        $columns["cat_sat_tipo_nomina_descripcion"]["titulo"] = "Tipo Nomina";

        $filtro = array("nom_conf_nomina.id","nom_conf_nomina.codigo", "nom_conf_factura.descripcion",
            "cat_sat_periodicidad_pago_nom.descripcion","cat_sat_tipo_nomina.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->titulo_lista = 'Configuracion Nomina';

        $this->controlador_nom_conf_percepcion = new controlador_nom_conf_percepcion(link:$this->link, paths_conf: $paths_conf);

        $links = $this->inicializa_links();
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar links',data:  $links);
            print_r($error);
            die('Error');
        }

        $propiedades = $this->inicializa_propiedades();
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar propiedades',data:  $propiedades);
            print_r($error);
            die('Error');
        }

        $ids = $this->inicializa_ids();
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar ids',data:  $ids);
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

    public function asigna_percepcion(bool $header, bool $ws = false): array|stdClass
    {
        $columns["nom_conf_percepcion_id"]["titulo"] = "Id";
        $columns["nom_conf_nomina_descripcion"]["titulo"] = "Conf. Nomina";
        $columns["nom_percepcion_descripcion"]["titulo"] = "Percepcion";
        $columns["nom_conf_percepcion_importe_gravado"]["titulo"] = "Importe Gravado";
        $columns["nom_conf_percepcion_importe_exento"]["titulo"] = "Importe Exento";
        $columns["nom_conf_percepcion_fecha_inicio"]["titulo"] = "Fecha Inicio";
        $columns["nom_conf_percepcion_fecha_fin"]["titulo"] = "Fecha Fin";
        $columns["modifica"]["titulo"] = "Acciones";
        $columns["modifica"]["type"] = "button";
        $columns["modifica"]["campos"] = array("elimina_bd");

        $colums_rs =$this->datatable_init(columns: $columns,identificador: "#nom_conf_percepcion",
            data: array("nom_conf_nomina.id" => $this->registro_id));
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar links', data: $colums_rs);
            print_r($error);
            die('Error');
        }

        $alta = $this->controlador_nom_conf_percepcion->alta(header: false);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $alta, header: $header, ws: $ws);
        }

        $this->controlador_nom_conf_percepcion->asignar_propiedad(identificador: 'nom_conf_nomina_id',
            propiedades: ["id_selected" => $this->registro_id, "disabled" => true,
                "filtro" => array('nom_conf_nomina.id' => $this->registro_id)]);

        $this->inputs = $this->controlador_nom_conf_percepcion->genera_inputs(
            keys_selects:  $this->controlador_nom_conf_percepcion->keys_selects);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $this->inputs);
            print_r($error);
            die('Error');
        }

        return $this->inputs;
    }

    public function asigna_percepcion_alta_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }
        $_POST['nom_conf_nomina_id'] = $this->registro_id;

        $alta = (new nom_conf_percepcion($this->link))->alta_registro(registro: $_POST);
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al dar de alta percepcion', data: $alta,
                header: $header, ws: $ws);
        }


        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $alta,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($alta, JSON_THROW_ON_ERROR);
            exit;
        }
        $alta->siguiente_view = $siguiente_view;

        return $alta;
    }

    private function base(): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false, ws: false);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $this->asignar_propiedad(identificador:'nom_conf_factura_id',
            propiedades: ["id_selected"=>$this->row_upd->nom_conf_factura_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'cat_sat_periodicidad_pago_nom_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_periodicidad_pago_nom_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'cat_sat_tipo_nomina_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_tipo_nomina_id]);
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

    private function inicializa_ids(): array
    {
        if (isset($_GET['nom_conf_percepcion_id'])){
            $this->nom_conf_percepcion_id = $_GET['nom_conf_percepcion_id'];
        }

        return $_GET;
    }

    private function inicializa_links(): array|string
    {
        $this->obj_link->genera_links($this);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar links para conf. nomina',data:  $this->obj_link);
        }

        $link = $this->obj_link->get_link($this->seccion,"asigna_percepcion_alta_bd");
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener link partida conf_percepcion_alta_bd',data:  $link);
        }
        $this->link_nom_conf_percepcion_alta_bd = $link;

        return $link;
    }

    private function inicializa_propiedades(): array
    {
        $identificador = "nom_conf_factura_id";
        $propiedades = array("label" => "Conf. Factura");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "cat_sat_periodicidad_pago_nom_id";
        $propiedades = array("label" => "Periodicidad Pago");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "cat_sat_tipo_nomina_id";
        $propiedades = array("label" => "Tipo Nomina");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "codigo";
        $propiedades = array("place_holder" => "Codigo");
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




    public function asigna_percepcion_elimina_bd(bool $header, bool $ws = false): array|stdClass
    {
        $this->link->beginTransaction();
        $r_elimina = (new nom_conf_percepcion($this->link))->elimina_bd(id: $this->nom_conf_percepcion_id);
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al eliminar otro pago', data: $r_elimina, header: $header,
                ws: $ws);
        }
        $this->link->commit();

        $this->out(header: $header,result:  $r_elimina,siguiente_view:  'asigna_percepcion',ws:  $ws);
        $r_elimina->siguiente_view = 'asigna_percepcion';

        return $r_elimina;
    }

    public function asigna_percepcion_modifica(bool $header, bool $ws = false): array|stdClass|string
    {
        $controlador = new controlador_nom_conf_percepcion($this->link);
        $controlador->registro_id = $this->nom_conf_percepcion_id;

        $r_modifica = $controlador->modifica(header: false, ws: false);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar template', data: $r_modifica);
        }

        $inputs = (new nom_conf_percepcion_html(html: $this->html_base))->inputs_nom_conf_percepcion(
            controlador: $controlador);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }
        $this->inputs = $inputs;

        return $r_modifica;
    }

    private function data_percepcion_btn(array $percepcion): array
    {
        $params['nom_conf_percepcion_id'] = $percepcion['nom_conf_percepcion_id'];

        $btn_elimina = $this->html_base->button_href(accion: 'asigna_percepcion_elimina_bd', etiqueta: 'Elimina',
            registro_id: $this->registro_id, seccion: 'nom_conf_nomina', style: 'danger', params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $percepcion['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'asigna_percepcion_modifica', etiqueta: 'Modifica',
            registro_id: $this->registro_id, seccion: 'nom_conf_nomina', style: 'warning', params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $percepcion['link_modifica'] = $btn_modifica;

        return $percepcion;
    }



    private function limpia_btn(): array
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }
        return $_POST;
    }





    private function out(bool $header, mixed $result, string $siguiente_view, bool $ws){
        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $result,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            try {
                echo json_encode($result, JSON_THROW_ON_ERROR);
            }
            catch (Throwable $e){
                $error = $this->errores->error(mensaje: 'Error en json', data: $e);
                print_r($error);
            }
            exit;
        }
        return $result;
    }
}
