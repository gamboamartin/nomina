<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;

use gamboamartin\empleado\models\em_anticipo;
use gamboamartin\errores\errores;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\em_anticipo_html;
use html\nom_conf_abono_html;
use models\nom_conf_abono;
use PDO;
use stdClass;

class controlador_nom_conf_abono extends system {

    public array $keys_selects = array();

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_conf_abono(link: $link);
        $html_ = new nom_conf_abono_html(html: $html);
        $obj_link = new links_menu(link: $link,registro_id: $this->registro_id);

        $columns["nom_conf_abono_id"]["titulo"] = "Id";
        $columns["nom_conf_abono_codigo"]["titulo"] = "Codigo";
        $columns["nom_conf_abono_descripcion"]["titulo"] = "Descripcion";
        $columns["em_tipo_anticipo_descripcion"]["titulo"] = "Tipo Anticipo";
        $columns["em_tipo_abono_anticipo_descripcion"]["titulo"] = "Tipo Abono";
        $columns["nom_deduccion_descripcion"]["titulo"] = "Deduccion";
        $columns["adm_campo_descripcion"]["titulo"] = "Campo";

        $filtro = array("nom_conf_abono.id","nom_conf_abono.codigo","nom_conf_abono.descripcion",
            "em_tipo_anticipo.descripcion","em_tipo_abono_anticipo.descripcion","nom_deduccion.descripcion",
            "adm_campo.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->titulo_lista = 'Conf. Abono';

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

        $this->asignar_propiedad(identificador:'em_tipo_abono_anticipo_id',
            propiedades: ["id_selected"=>$this->row_upd->em_tipo_abono_anticipo_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'em_tipo_anticipo_id',
            propiedades: ["id_selected"=>$this->row_upd->em_tipo_anticipo_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'adm_campo_id',
            propiedades: ["id_selected"=>$this->row_upd->adm_campo_id]);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al asignar propiedad', data: $this);
            print_r($error);
            die('Error');
        }

        $this->asignar_propiedad(identificador:'nom_deduccion_id',
            propiedades: ["id_selected"=>$this->row_upd->nom_deduccion_id]);
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
        $identificador = "em_tipo_abono_anticipo_id";
        $propiedades = array("label" => "Tipo Abono");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "nom_deduccion_id";
        $propiedades = array("label" => "Deduccion");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "em_tipo_anticipo_id";
        $propiedades = array("label" => "Tipo Anticipo");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "adm_campo_id";
        $propiedades = array("label" => "Campo");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        $identificador = "codigo";
        $propiedades = array("place_holder" => "Codigo");
        $this->asignar_propiedad(identificador:$identificador, propiedades: $propiedades);

        return $this->keys_selects;
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

}
