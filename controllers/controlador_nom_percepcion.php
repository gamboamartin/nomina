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
use html\nom_percepcion_html;
use links\secciones\link_nom_percepcion;
use models\nom_percepcion;
use PDO;
use stdClass;

class controlador_nom_percepcion extends system {

    public array $keys_selects = array();

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_percepcion(link: $link);
        $html_ = new nom_percepcion_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id:  $this->registro_id);
        $this->rows_lista[] = 'aplica_subsidio';

        $columns["nom_percepcion_id"]["titulo"] = "Id";
        $columns["nom_percepcion_codigo"]["titulo"] = "Codigo";
        $columns["nom_percepcion_descripcion"]["titulo"] = "Descripcion";
        $columns["cat_sat_tipo_percepcion_nom_descripcion"]["titulo"] = "Tipo Percepcion";

        $filtro = array("nom_percepcion.id","nom_percepcion.codigo","nom_percepcion.descripcion",
            "cat_sat_tipo_percepcion_nom.descripcion");

        $datatables = new stdClass();
        $datatables->columns = $columns;
        $datatables->filtro = $filtro;

        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $this->titulo_lista = 'Percepcion';
        $this->acciones->aplica_subsidio = new stdClass();
        $this->acciones->aplica_subsidio->style = '';
        $this->acciones->aplica_subsidio->style_status = true;

        $propiedades = $this->inicializa_priedades();
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al inicializar propiedades',data:  $propiedades);
            print_r($error);
            die('Error');
        }
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta =  parent::alta(header: false, ws: false);
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

        $this->asignar_propiedad(identificador:'cat_sat_tipo_percepcion_nom_id',
            propiedades: ["id_selected"=>$this->row_upd->cat_sat_tipo_percepcion_nom_id]);

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
        $identificador = "cat_sat_tipo_percepcion_nom_id";
        $propiedades = array("label" => "Tipo Percepcion", "cols" => 12);
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

    public function cambiar_estado_subsidio(bool $header, bool $ws): array|stdClass
    {
        $modelo = new nom_percepcion(link: $this->link);

        $r_nom_percepcion = $modelo->registro_estado_subsidio();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener percepcion',data:  $r_nom_percepcion);
        }

        $id_nom_percepcion = $modelo->id_registro_estado_subsidio($r_nom_percepcion);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener el id de percepcion',data:  $id_nom_percepcion);
        }

        if ($id_nom_percepcion !== -1) {
            $this->link->beginTransaction();
            $upd = $this->modelo->status('aplica_subsidio', $id_nom_percepcion);
            if(errores::$error){
                $this->link->rollBack();
                return $this->retorno_error(mensaje: 'Error al modificar registro', data: $upd,header:  $header, ws: $ws);
            }
            $this->link->commit();
        }

        $this->link->beginTransaction();
        $upd = $this->modelo->status('aplica_subsidio', $this->registro_id);
        if(errores::$error){
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al modificar registro', data: $upd,header:  $header, ws: $ws);
        }
        $this->link->commit();

        if ($id_nom_percepcion !== $this->registro_id) {
            $_SESSION['exito'][]['mensaje'] = 'Se ajusto el estatus de manera el registro con el id '.$this->registro_id;
        }

        $this->header_out(result: $upd, header: $header,ws:  $ws);
        return $upd;
    }

}
