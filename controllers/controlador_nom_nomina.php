<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;

use gamboamartin\comercial\controllers\controlador_com_sucursal;
use gamboamartin\errores\errores;
use gamboamartin\organigrama\controllers\controlador_org_sucursal;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\com_sucursal_html;
use html\nom_nomina_html;
use html\org_sucursal_html;
use html\selects;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_percepcion;
use models\org_sucursal;
use PDO;
use stdClass;

class controlador_nom_nomina extends system {

    public string $link_nom_nomina_alta_bd = '';
    public stdClass $paths_conf;
    public stdClass $deducciones;
    public stdClass $percepciones;

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_nomina(link: $link);
        $html_ = new nom_nomina_html(html: $html);
        $obj_link = new links_menu($this->registro_id);
        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Nominas';
        $this->link_nom_nomina_alta_bd =$obj_link->links->nom_nomina->alta_bd;
        $this->paths_conf = $paths_conf;
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta =  parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $r_alta, header: $header,ws:$ws);
        }

        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_alta(controler: $this, link: $this->link);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    private function base(stdClass $params = new stdClass()): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false,aplica_form:  false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $inputs = (new nom_nomina_html(html: $this->html_base))->inputs_nom_nomina (
            controlador:$this, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }

        $data = new stdClass();
        $data->template = $r_modifica;
        $data->inputs = $inputs;

        return $data;
    }

    public function crea_nomina(bool $header, bool $ws = false): array|string
    {
        $r_alta =  parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $r_alta, header: $header,ws:$ws);
        }

        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_crea_nomina(controler: $this, link: $this->link);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $inputs);
            print_r($error);
            die('Error');
        }



        return $r_alta;
    }

    private function data_percepcion_btn(array $percepcion): array
    {
        $params['nom_nomina_id'] = $percepcion['nom_nomina_id'];

        $btn_elimina = $this->html_base->button_href(accion:'elimina_bd',etiqueta:  'Elimina',
            registro_id:  $percepcion['nom_percepcion_id'], seccion: 'nom_percepcion',style:  'danger');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar btn',data:  $btn_elimina);
        }
        $percepcion['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion:'modifica',etiqueta:  'Modifica',
            registro_id:  $percepcion['nom_percepcion_id'], seccion: 'nom_percepcion',style:  'warning', params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar btn',data:  $btn_modifica);
        }
        $percepcion['link_modifica'] = $btn_modifica;

        return $percepcion;
    }

    private function data_deduccion_btn(array $deduccion): array
    {
        $params['nom_nomina_id'] = $deduccion['nom_nomina_id'];

        $btn_elimina = $this->html_base->button_href(accion:'elimina_bd',etiqueta:  'Elimina',
            registro_id:  $deduccion['nom_deduccion_id'], seccion: 'nom_deduccion',style:  'danger');
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar btn',data:  $btn_elimina);
        }
        $deduccion['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion:'modifica',etiqueta:  'Modifica',
            registro_id:  $deduccion['nom_deduccion_id'], seccion: 'nom_deduccion',style:  'warning', params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar btn',data:  $btn_modifica);
        }
        $deduccion['link_modifica'] = $btn_modifica;

        return $deduccion;
    }

    public function modifica(bool $header, bool $ws = false, string $breadcrumbs = '', bool $aplica_form = true,
                             bool $muestra_btn = true): array|string
    {
        $base = $this->base();
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar datos',data:  $base,
                header: $header,ws:$ws);
        }

        $filtro['nom_nomina.id'] = 7;
        $deducciones = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener deducciones',data:  $deducciones, header: $header,ws:$ws);
        }

        foreach ($deducciones->registros as $indice => $deduccion){

            $deduccion = $this->data_deduccion_btn(deduccion: $deduccion);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al asignar botones',data:  $deduccion, header: $header,ws:$ws);
            }
            $deducciones->registros[$indice] = $deduccion;
        }
        $this->deducciones = $deducciones;

        $filtro['nom_nomina.id'] = 7;
        $percepciones = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener percepciones',data:  $percepciones, header: $header,ws:$ws);
        }

        foreach ($percepciones->registros as $indice => $percepcion){

            $percepcion = $this->data_percepcion_btn(percepcion: $percepcion);
            if(errores::$error){
                return $this->retorno_error(mensaje: 'Error al asignar botones',data:  $percepcion, header: $header,ws:$ws);
            }
            $percepciones->registros[$indice] = $percepcion;
        }
        $this->percepciones = $percepciones;

        return $base->template;
    }
}
