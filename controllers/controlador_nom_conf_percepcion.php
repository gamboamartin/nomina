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
use html\nom_conf_deduccion_html;
use html\nom_conf_nomina_html;
use html\nom_conf_percepcion_html;
use html\nom_deduccion_html;
use models\nom_conf_deduccion;
use models\nom_conf_nomina;
use models\nom_conf_percepcion;
use PDO;
use stdClass;

class controlador_nom_conf_percepcion extends system {

    public array $keys_selects = array();

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_conf_percepcion(link: $link);
        $html_ = new nom_conf_percepcion_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        $columns["nom_conf_percepcion_id"]["titulo"] = "Id";
        $columns["nom_conf_nomina_descripcion"]["titulo"] = "Conf. Nomina";
        $columns["nom_percepcion_descripcion"]["titulo"] = "Percepcion";
        $columns["nom_conf_percepcion_importe_gravado"]["titulo"] = "Importe Gravado";
        $columns["nom_conf_percepcion_importe_exento"]["titulo"] = "Importe Exento";
        $columns["nom_conf_percepcion_fecha_inicio"]["titulo"] = "Fecha Inicio";
        $columns["nom_conf_percepcion_fecha_fin"]["titulo"] = "Fecha Fin";

        $filtro = array("nom_conf_percepcion.id", "nom_conf_nomina.descripcion", "nom_percepcion.descripcion",
            "nom_conf_percepcion.importe_gravado","nom_conf_percepcion.importe_exento",
            "nom_conf_percepcion.fecha_inicio","nom_conf_percepcion.fecha_fin");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->titulo_lista = 'Configuracion Percepcion';

        $propiedades = $this->inicializa_propiedades();
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

        $this->row_upd->fecha_inicio = date('Y-m-d');
        $this->row_upd->fecha_fin = date('Y-m-d');
        $this->row_upd->importe_gravado = 0;
        $this->row_upd->importe_exento = 0;

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

        $this->asignar_propiedad(identificador:'nom_conf_nomina_id',
            propiedades: ["id_selected"=>$this->row_upd->nom_conf_nomina_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'nom_percepcion_id',
            propiedades: ["id_selected"=>$this->row_upd->nom_percepcion_id]);
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

    private function inicializa_propiedades(): array
    {
        $identificador = "nom_conf_nomina_id";
        $propiedades = array("label" => "Conf. Nomina");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "nom_percepcion_id";
        $propiedades = array("label" => "Percepcion");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "importe_gravado";
        $propiedades = array("place_holder" => "Importe Gravado");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "importe_exento";
        $propiedades = array("place_holder" => "Importe Exento");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "fecha_inicio";
        $propiedades = array("place_holder" => "Fecha Inicio");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "fecha_fin";
        $propiedades = array("place_holder" => "Fecha Fin");
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
