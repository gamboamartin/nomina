<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */

namespace gamboamartin\nomina\controllers;

use config\generales;
use gamboamartin\errores\errores;
use gamboamartin\system\actions;
use gamboamartin\system\links_menu;
use gamboamartin\template\html;
use gamboamartin\validacion\validacion;
use html\nom_nomina_html;
use html\nom_par_deduccion_html;
use html\nom_par_otro_pago_html;
use html\nom_par_percepcion_html;
use JsonException;
use models\adm_accion_grupo;
use models\doc_documento;
use models\nom_concepto_imss;
use models\nom_nomina;
use models\nom_par_deduccion;
use models\nom_par_otro_pago;
use models\nom_par_percepcion;
use PDO;
use stdClass;
use Throwable;

class controlador_nom_nomina extends base_nom
{
    public string $link_crea_nomina = '';
    public string $link_nom_nomina_alta_bd = '';
    public string $link_nom_nomina_modifica_bd = '';
    public string $link_nom_nomina_recalcula_neto_bd = '';
    public string $link_nom_par_percepcion_alta_bd = '';
    public string $link_nom_par_deduccion_alta_bd = '';
    public string $link_nom_par_otro_pago_alta_bd = '';
    public string $link_nom_par_percepcion_modifica_bd = '';
    public string $link_nom_par_deduccion_modifica_bd = '';
    public string $link_nom_par_otro_pago_modifica_bd = '';
    public int $nom_par_percepcion_id = -1;
    public int $nom_par_deduccion_id = -1;
    public int $nom_par_otro_pago_id = -1;
    public stdClass $paths_conf;


    public stdClass $otros_pagos;
    public stdClass $cuotas_obrero_patronales;
    public float $cuota_total = 0.0;

    protected stdClass $params_actions;

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new nom_nomina(link: $link);
        $html_ = new nom_nomina_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);


        $columns = array();
        $columns['nom_nomina_id']['titulo'] = 'Id';
        $columns['em_empleado_codigo']['titulo'] = 'Codigo';

        $columns['em_empleado_rfc']['titulo'] = 'RFC';


        $columns['em_empleado_nombre']['titulo'] = 'Nombre';

        $columns['em_empleado_ap']['titulo'] = 'AP';
        $columns['em_empleado_am']['titulo'] = 'AM';
        $columns['nom_nomina_fecha_inicial_pago']['titulo'] = 'F Ini';
        $columns['nom_nomina_fecha_final_pago']['titulo'] = 'F Ini';
        $columns['nom_nomina_fecha_pago']['titulo'] = 'F Pago';
        $columns['nom_periodo_codigo']['titulo'] = 'Periodo Cod';
        $columns['nom_nomina_total_percepcion_total']['titulo'] = 'Percepcion Total';
        $columns['nom_nomina_total_otro_pago_total']['titulo'] = 'Otro Pago Total';
        $columns['nom_nomina_total_deduccion_total']['titulo'] = 'Deduccion Total';
        $columns['nom_nomina_total']['titulo'] = 'Total';
        $columns['cat_sat_tipo_nomina_descripcion']['titulo'] = 'Tipo Nom';
        $columns['org_empresa_rfc']['titulo'] = 'RFC Empresa';


        $filtro = array();
        $filtro[] = 'em_empleado.rfc';
        $filtro[] = 'em_empleado.nombre';
        $filtro[] = 'nom_nomina.fecha_inicial_pago';
        $filtro[] = 'nom_nomina.fecha_final_pago';
        $filtro[] = 'nom_nomina.fecha_pago';
        $filtro[] = 'nom_periodo.codigo';
        $filtro[] = 'cat_sat_tipo_nomina.descripcion';
        $filtro[] = 'org_empresa.rfc';

        $datatables = new stdClass();
        $datatables->filtro = $filtro;
        $datatables->columns = $columns;

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, datatables: $datatables,
            paths_conf: $paths_conf);

        $link_nom_nomina_modifica_bd = $obj_link->link_con_id(accion: 'modifica_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_nomina_modifica_bd);
            print_r($error);
            die('Error');
        }

        $link_nom_nomina_recalcula_neto_bd = $obj_link->link_con_id(accion: 'recalcula_neto_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_nomina_recalcula_neto_bd);
            print_r($error);
            die('Error');
        }

        $link_nom_par_percepcion_alta_bd = $obj_link->link_con_id(accion: 'nueva_percepcion_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_par_percepcion_alta_bd);
            print_r($error);
            die('Error');
        }

        $link_nom_par_deduccion_alta_bd = $obj_link->link_con_id(accion: 'nueva_deduccion_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_par_deduccion_alta_bd);
            print_r($error);
            die('Error');
        }

        $link_nom_par_otro_pago_alta_bd = $obj_link->link_con_id(accion: 'otro_pago_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_par_otro_pago_alta_bd);
            print_r($error);
            die('Error');
        }

        $link_nom_par_percepcion_modifica_bd = $obj_link->link_con_id(accion: 'modifica_percepcion_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_par_percepcion_modifica_bd);
            print_r($error);
            die('Error');
        }

        $link_nom_par_deduccion_modifica_bd = $obj_link->link_con_id(accion: 'modifica_deduccion_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_par_deduccion_modifica_bd);
            print_r($error);
            die('Error');
        }

        $link_nom_par_otro_pago_modifica_bd = $obj_link->link_con_id(accion: 'modifica_otro_pago_bd',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_nom_par_otro_pago_modifica_bd);
            print_r($error);
            die('Error');
        }

        $link_crea_nomina = "index.php?seccion=$this->tabla&accion=crea_nomina&session_id=$this->session_id";
        $this->link_crea_nomina = $link_crea_nomina;

        $this->titulo_lista = 'Nominas';

        $this->link_nom_nomina_alta_bd = $obj_link->links->nom_nomina->alta_bd;
        $this->link_nom_nomina_modifica_bd = $link_nom_nomina_modifica_bd;
        $this->link_nom_nomina_recalcula_neto_bd = $link_nom_nomina_recalcula_neto_bd;
        $this->link_nom_par_percepcion_alta_bd = $link_nom_par_percepcion_alta_bd;
        $this->link_nom_par_deduccion_alta_bd = $link_nom_par_deduccion_alta_bd;
        $this->link_nom_par_otro_pago_alta_bd = $link_nom_par_otro_pago_alta_bd;
        $this->link_nom_par_percepcion_modifica_bd = $link_nom_par_percepcion_modifica_bd;
        $this->link_nom_par_deduccion_modifica_bd = $link_nom_par_deduccion_modifica_bd;
        $this->link_nom_par_otro_pago_modifica_bd = $link_nom_par_otro_pago_modifica_bd;
        $this->paths_conf = $paths_conf;
        $this->nom_nomina_id = $this->registro_id;


        $init = $this->init_partidas_ids();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al inicializar ids', data: $init);
            print_r($error);
            die('Error');
        }

        $keys_rows_lista = $this->keys_rows_lista();
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar keys de lista', data: $init);
            print_r($error);
            die('Error');
        }
        $this->keys_row_lista = $keys_rows_lista;

        $this->cuotas_obrero_patronales = new stdClass();


    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects = array();

        $keys_selects['dp_calle_pertenece'] = new stdClass();
        $keys_selects['dp_calle_pertenece']->label = 'Calle';
        $keys_selects['dp_calle_pertenece']->cols = 6;

        $keys_selects['em_empleado'] = new stdClass();
        $keys_selects['em_empleado']->label = 'Empleado';

        $keys_selects['fc_factura'] = new stdClass();
        $keys_selects['fc_factura']->label = 'Factura';
        $keys_selects['fc_factura']->cols = 6;

        $keys_selects['cat_sat_tipo_nomina'] = new stdClass();
        $keys_selects['cat_sat_tipo_nomina']->label = 'Tipo Nomina';
        $keys_selects['cat_sat_tipo_nomina']->cols = 6;

        $keys_selects['im_registro_patronal'] = new stdClass();
        $keys_selects['im_registro_patronal']->label = 'Registro Patronal';
        $keys_selects['im_registro_patronal']->cols = 6;


        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_alta(controler: $this,
            keys_selects: $keys_selects, link: $this->link);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    private function asigna_link_genera_xml_row(stdClass $row): array|stdClass
    {
        $keys = array('nom_nomina_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_genera_xml = $this->obj_link->link_con_id(accion:'genera_xml',link: $this->link,registro_id:  $row->nom_nomina_id,
            seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_genera_xml);
        }

        $row->link_genera_xml = $link_genera_xml;
        $row->link_genera_xml_style = 'info';

        return $row;
    }

    private function asigna_link_timbra_row(stdClass $row): array|stdClass
    {
        $keys = array('nom_nomina_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_timbra = $this->obj_link->link_con_id(accion:'timbra',link: $this->link,registro_id:  $row->nom_nomina_id,
            seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_timbra);
        }

        $row->link_timbra = $link_timbra;
        $row->link_timbra_style = 'info';

        return $row;
    }

    private function base(stdClass $params = new stdClass()): array|stdClass
    {
        $r_modifica = parent::modifica(header: false, aplica_form: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar template', data: $r_modifica);
        }

        $keys = array('cat_sat_tipo_nomina_id','em_empleado_id','im_registro_patronal_id','nom_conf_empleado_id',
            'nom_periodo_id','org_puesto_id','cat_sat_tipo_contrato_nom_id');

        foreach ($keys as $key){
            if(!isset($this->row_upd->$key)){
                $this->row_upd->$key = -1;
            }
        }

        $inputs = (new nom_nomina_html(html: $this->html_base))->inputs_nom_nomina(
            controlador: $this, params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al inicializar inputs', data: $inputs);
        }

        $data = new stdClass();
        $data->template = $r_modifica;
        $data->inputs = $inputs;

        return $data;
    }

    public function calcula_cuota_obrero_patronal(stdClass $row){
        $campos['cuotas'] = 'nom_concepto_imss.monto';
        $filtro_sum['nom_nomina.id'] = $row->nom_nomina_id;
        $total_cuota = (new nom_concepto_imss($this->link))->suma(campos: $campos,filtro: $filtro_sum);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener suma', data: $total_cuota);
        }
        $row->total_cuota_patronal = $total_cuota['cuotas'];

        return $row;
    }

    public function crea_nomina(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $params = $this->params_actions->crea_nomina ?? new stdClass();

        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_crea_nomina(controler: $this,
            link: $this->link, params: $params);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    public function selecciona_percepcion(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $params = $this->params_actions->selecciona_percepcion ?? new stdClass();

        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_selecciona_percepcion(controler: $this,
            link: $this->link, params: $params);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    private function cuotas_obrero_patronales(): array|stdClass
    {
        $filtro['nom_nomina.id'] = $this->registro_id;
        $cuotas = (new nom_concepto_imss($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener cuotas', data: $cuotas);
        }

        $this->cuotas_obrero_patronales = $cuotas;

        $campos['cuotas'] = 'nom_concepto_imss.monto';
        $filtro_sum['nom_nomina.id'] = $this->registro_id;
        $total_cuota = (new nom_concepto_imss($this->link))->suma(campos: $campos,filtro: $filtro_sum);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener suma', data: $total_cuota);
        }

        $this->cuota_total = $total_cuota['cuotas'];

        return  $this->cuotas_obrero_patronales;
    }

    private function data_deduccion_btn(array $deduccion): array
    {
        $params['nom_par_deduccion_id'] = $deduccion['nom_par_deduccion_id'];

        $btn_elimina = $this->html_base->button_href(accion: 'elimina_deduccion_bd', etiqueta: 'Elimina',
            registro_id: $this->registro_id, seccion: 'nom_nomina', style: 'danger', params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $deduccion['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'modifica_deduccion', etiqueta: 'Modifica',
            registro_id: $this->registro_id, seccion: 'nom_nomina', style: 'warning', params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $deduccion['link_modifica'] = $btn_modifica;

        return $deduccion;
    }

    public function elimina_deduccion_bd(bool $header, bool $ws = false): array|stdClass
    {
        $r_elimina = (new nom_par_deduccion($this->link))->elimina_bd(id: $this->nom_par_deduccion_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al eliminar otro pago', data: $r_elimina, header: $header,
                ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        $limpia = $this->limpia_btn();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al limpiar boton', data: $limpia, header: $header, ws: $ws);
        }

        $this->out(header: $header,result:  $r_elimina,siguiente_view:  $siguiente_view,ws:  $ws);


        $r_elimina->siguiente_view = $siguiente_view;

        return $r_elimina;
    }

    public function elimina_otro_pago_bd(bool $header, bool $ws = false): array|stdClass
    {
        $r_elimina = (new nom_par_otro_pago($this->link))->elimina_bd(id: $this->nom_par_otro_pago_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al eliminar otro pago', data: $r_elimina, header: $header,
                ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        $limpia = $this->limpia_btn();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al limpiar boton', data: $limpia, header: $header, ws: $ws);
        }

        $this->out(header: $header,result:  $r_elimina,siguiente_view:  $siguiente_view,ws:  $ws);
        $r_elimina->siguiente_view = $siguiente_view;

        return $r_elimina;
    }

    public function elimina_percepcion_bd(bool $header, bool $ws = false): array|stdClass
    {
        $r_elimina = (new nom_par_percepcion($this->link))->elimina_bd(id: $this->nom_par_percepcion_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al eliminar otro pago', data: $r_elimina, header: $header,
                ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }


        $limpia = $this->limpia_btn();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al limpiar boton', data: $limpia, header: $header, ws: $ws);
        }

        $this->out(header: $header,result:  $r_elimina,siguiente_view:  $siguiente_view,ws:  $ws);
        $r_elimina->siguiente_view = $siguiente_view;

        return $r_elimina;
    }

    private function genera_ruta_archivo_tmp(): array|string
    {
        $ruta_archivos = $this->ruta_archivos();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos);
        }

        $ruta_archivos_tmp = $this->ruta_archivos_tmp(ruta_archivos: $ruta_archivos);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos_tmp);
        }
        return $ruta_archivos_tmp;
    }

    public function genera_xml(bool $header, bool $ws = false): array|stdClass
    {
        $nom_nomina = $this->modelo->registro(registro_id: $this->registro_id, retorno_obj: true);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener nomina', data: $nom_nomina, header: $header, ws: $ws);
        }

        $keys = array('fc_factura_id');
        $valida = (new validacion())->valida_ids(keys: $keys, registro: $nom_nomina);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al validar nomina', data: $valida, header: $header, ws: $ws);
        }

        $xml = (new xml_nom())->xml(link: $this->link, nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar xml', data: $xml, header: $header, ws: $ws);
        }


        $ruta_archivos_tmp = $this->genera_ruta_archivo_tmp();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar ruta de archivos', data: $ruta_archivos_tmp, header: $header, ws: $ws);
        }


        $documento = array();
        $file = array();


        $file_xml_st = $ruta_archivos_tmp.'/'.$this->registro_id.'.st.xml';

        file_put_contents($file_xml_st, $xml);


        $file['name'] = $file_xml_st;
        $file['tmp_name'] = $file_xml_st;
        $documento['doc_tipo_documento_id'] = 1;

        $documento = (new doc_documento(link: $this->link))->alta_registro(registro: $documento, file: $file);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al guardar xml', data: $documento, header: $header, ws: $ws);
        }
        unlink($file_xml_st);
        ob_clean();
        echo trim(file_get_contents($documento->registro['doc_documento_ruta_absoluta']));
        header('Content-Type: text/xml');
        exit;


        return $nom_nomina;
    }

    private function init_partidas_ids(): array
    {
        if (isset($_GET['nom_par_percepcion_id'])){
            $this->nom_par_percepcion_id = $_GET['nom_par_percepcion_id'];
        }

        if (isset($_GET['nom_par_deduccion_id'])){
            $this->nom_par_deduccion_id = $_GET['nom_par_deduccion_id'];
        }

        if (isset($_GET['nom_par_otro_pago_id'])){
            $this->nom_par_otro_pago_id = $_GET['nom_par_otro_pago_id'];
        }
        return $_GET;
    }

    private function keys_rows_lista(): array
    {
        $keys_rows_lista = array();
        $keys = array('nom_nomina_id','nom_nomina_codigo','nom_nomina_fecha_inicial_pago','nom_nomina_fecha_final_pago',
            'em_empleado_codigo','em_empleado_nombre','em_empleado_ap','em_empleado_am','org_empresa_rfc');

        foreach ($keys as $campo) {
            $keys_rows_lista = $this->key_row_lista_init(campo: $campo,keys_rows_lista: $keys_rows_lista);
            if (errores::$error){
                return $this->errores->error(mensaje: "error al inicializar key",data: $keys_rows_lista);
            }
        }

        return $keys_rows_lista;
    }

    private function key_row_lista_init(string $campo, array $keys_rows_lista): array
    {
        $data = new stdClass();
        $data->campo = $campo;

        $campo = str_replace(array("nom_nomina_", "nom_", "_"), '', $campo);
        $campo = ucfirst(strtolower($campo));

        $data->name_lista = $campo;
        $keys_rows_lista[] = $data;

        return $keys_rows_lista;
    }

    private function limpia_btn(): array
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }
        return $_POST;
    }

    private function maqueta_registros_lista(array $registros): array
    {
        foreach ($registros as $indice=> $row){
            $row = $this->asigna_link_genera_xml_row(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }
            $registros[$indice] = $row;

            $row = $this->asigna_link_timbra_row(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }
            $registros[$indice] = $row;

            $row = $this->calcula_cuota_obrero_patronal(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }
            $registros[$indice] = $row;

        }
        return $registros;
    }

    public function modifica(bool $header, bool $ws = false, string $breadcrumbs = '', bool $aplica_form = true,
                             bool $muestra_btn = true): array|string
    {
        $params = $this->params_actions->modifica ?? new stdClass();
        $base = $this->base(params: $params);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al maquetar datos', data: $base,
                header: $header, ws: $ws);
        }

        $partidas = $this->partidas();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener partidas', data: $partidas, header: $header, ws: $ws);
        }

        $partidas = $this->cuotas_obrero_patronales();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener partidas', data: $partidas, header: $header, ws: $ws);
        }

        return $base->template;
    }

    public function modifica_deduccion(bool $header, bool $ws = false): array|stdClass|string
    {
        $controlador = new controlador_nom_par_deduccion($this->link);
        $controlador->registro_id = $this->nom_par_deduccion_id;

        $r_modifica = $controlador->modifica(header: false, aplica_form: false);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar template', data: $r_modifica);
        }

        $params = new stdClass();
        $params->nom_nomina_id = new stdClass();
        $params->nom_nomina_id->cols = 12;
        $params->nom_nomina_id->disabled = true;
        $params->nom_nomina_id->filtro = array('nom_nomina.id' => $this->registro_id);

        $params->nom_deduccion_id = new stdClass();
        $params->nom_deduccion_id->cols = 12;

        $inputs = (new nom_par_deduccion_html(html: $this->html_base))->inputs_nom_par_deduccion (
            controlador: $controlador, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }
        $this->inputs = $inputs;

        return $r_modifica;
    }

    public function modifica_deduccion_bd(bool $header, bool $ws = false): array|stdClass
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $registros = $_POST;

        $r_modifica = (new nom_par_deduccion($this->link))->modifica_bd(registro: $registros,
            id: $this->nom_par_deduccion_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al modificar deduccion', data: $r_modifica, header: $header, ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_modifica,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_modifica, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_modifica->siguiente_view = $siguiente_view;

        return $r_modifica;
    }

    public function modifica_otro_pago(bool $header, bool $ws = false): array|stdClass|string
    {
        $controlador = new controlador_nom_par_otro_pago($this->link);
        $controlador->registro_id = $this->nom_par_otro_pago_id;

        $r_modifica = $controlador->modifica(header: false, aplica_form: false);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar template', data: $r_modifica);
        }

        $params = new stdClass();
        $params->nom_nomina_id = new stdClass();
        $params->nom_nomina_id->cols = 12;
        $params->nom_nomina_id->disabled = true;
        $params->nom_nomina_id->filtro = array('nom_nomina.id' => $this->registro_id);

        $params->nom_otro_pago_id = new stdClass();
        $params->nom_otro_pago_id->cols = 12;

        $inputs = (new nom_par_otro_pago_html(html: $this->html_base))->inputs_nom_par_otro_pago(
            controlador: $controlador, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }
        $this->inputs = $inputs;

        return $r_modifica;
    }

    public function modifica_otro_pago_bd(bool $header, bool $ws = false): array|stdClass
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $registros = $_POST;

        $r_modifica = (new nom_par_otro_pago($this->link))->modifica_bd( registro:$registros,
            id: $this->nom_par_otro_pago_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al modificar deduccion', data: $r_modifica, header: $header, ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_modifica,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_modifica, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_modifica->siguiente_view = $siguiente_view;

        return $r_modifica;
    }

    public function modifica_percepcion(bool $header, bool $ws = false): array|stdClass|string
    {
        $controlador = new controlador_nom_par_percepcion($this->link);
        $controlador->registro_id = $this->nom_par_percepcion_id;

        $r_modifica = $controlador->modifica(header: false, aplica_form: false);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar template', data: $r_modifica);
        }

        $params = new stdClass();
        $params->nom_nomina_id = new stdClass();
        $params->nom_nomina_id->cols = 12;
        $params->nom_nomina_id->disabled = true;
        $params->nom_nomina_id->filtro = array('nom_nomina.id' => $this->registro_id);

        $params->nom_percepcion_id = new stdClass();
        $params->nom_percepcion_id->cols = 12;

        $inputs = (new nom_par_percepcion_html(html: $this->html_base))->inputs_nom_par_percepcion(
            controlador: $controlador, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }
        $this->inputs = $inputs;

        return $r_modifica;
    }

    public function modifica_percepcion_bd(bool $header, bool $ws = false): array|stdClass
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $registros = $_POST;

        $r_modifica = (new nom_par_percepcion($this->link))->modifica_bd(registro: $registros,
            id: $this->nom_par_percepcion_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al modificar deduccion', data: $r_modifica, header: $header, ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_modifica,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_modifica, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_modifica->siguiente_view = $siguiente_view;

        return $r_modifica;
    }

    public function nueva_deduccion(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects =array();
        $keys_selects['nom_nomina'] = new stdClass();
        $keys_selects['nom_nomina']->cols = 12;
        $keys_selects['nom_nomina']->disabled = true;
        $keys_selects['nom_nomina']->filtro = array('nom_nomina.id' => $this->registro_id);

        $keys_selects['nom_deduccion'] = new stdClass();
        $keys_selects['nom_deduccion']->cols = 12;


        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_nueva_deduccion(
            controler: $this, keys_selects: $keys_selects, link: $this->link);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    public function nueva_deduccion_bd(bool $header, bool $ws = false): array|stdClass
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $_POST['nom_nomina_id'] = $this->registro_id;
        $_POST['codigo'] = $_POST['nom_nomina_id'] . $_POST['nom_deduccion_id'] . $_POST['descripcion'] .
            $_POST['importe_gravado'] . $_POST['importe_exento'];
        $_POST['codigo_bis'] = $_POST['codigo'];

        $r_alta_nom_par_deduccion = (new nom_par_deduccion($this->link))->alta_registro(registro: $_POST);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al dar de alta deduccion', data: $r_alta_nom_par_deduccion,
                header: $header, ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_alta_nom_par_deduccion,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_alta_nom_par_deduccion, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_alta_nom_par_deduccion->siguiente_view = $siguiente_view;

        return $r_alta_nom_par_deduccion;
    }

    public function nueva_percepcion(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }


        $keys_selects = array();
        $keys_selects['nom_nomina'] = new stdClass();
        $keys_selects['nom_nomina']->cols = 12;
        $keys_selects['nom_nomina']->disabled = true;
        $keys_selects['nom_nomina']->filtro = array('nom_nomina.id' => $this->registro_id);

        $keys_selects['nom_percepcion'] = new stdClass();
        $keys_selects['nom_percepcion']->cols = 12;



        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_nueva_percepcion(controler: $this, keys_selects: $keys_selects,
            link: $this->link);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }

        return $r_alta;
    }

    /**
     * @throws JsonException
     */
    public function nueva_percepcion_bd(bool $header, bool $ws = false): array|stdClass
    {

        $this->link->beginTransaction();
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $_POST['nom_nomina_id'] = $this->registro_id;
        $_POST['codigo'] = $_POST['nom_nomina_id'] . $_POST['nom_percepcion_id'] . $_POST['descripcion'] .
            $_POST['importe_gravado'] . $_POST['importe_exento'];
        $_POST['codigo_bis'] = $_POST['codigo'];

        $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $_POST);
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al dar de alta percepcion', data: $r_alta_nom_par_percepcion,
                header: $header, ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            $this->link->rollBack();
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }
        $this->link->commit();

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_alta_nom_par_percepcion,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_alta_nom_par_percepcion, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_alta_nom_par_percepcion->siguiente_view = $siguiente_view;

        return $r_alta_nom_par_percepcion;
    }

    public function otro_pago(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects = array();
        $keys_selects['nom_nomina'] = new stdClass();
        $keys_selects['nom_nomina']->cols = 12;
        $keys_selects['nom_nomina']->disabled = true;
        $keys_selects['nom_nomina']->filtro = array('nom_nomina.id' => $this->registro_id);

        $keys_selects['nom_otro_pago'] = new stdClass();
        $keys_selects['nom_otro_pago']->cols = 12;


        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_otro_pago(
            controler: $this, keys_selects: $keys_selects, link: $this->link);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }

        return $r_alta;
    }

    public function crea_nomina_neto(bool $header, bool $ws = false): array|string
    {
        $r_alta = parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar template', data: $r_alta, header: $header, ws: $ws);
        }

        $keys_selects = array();
        $keys_selects['nom_nomina'] = new stdClass();
        $keys_selects['nom_nomina']->cols = 12;
        $keys_selects['nom_nomina']->disabled = true;
        $keys_selects['nom_nomina']->filtro = array('nom_nomina.id' => $this->registro_id);

        $keys_selects['nom_otro_pago'] = new stdClass();
        $keys_selects['nom_otro_pago']->cols = 12;

        $inputs = (new nom_nomina_html(html: $this->html_base))->genera_inputs_nomina_extraordinaria(
            controler: $this, keys_selects: $keys_selects, link: $this->link);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar inputs', data: $inputs);
            print_r($error);
            die('Error');
        }

        return $r_alta;
    }

    public function otro_pago_bd(bool $header, bool $ws = false): array|stdClass
    {
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $_POST['nom_nomina_id'] = $this->registro_id;
        $_POST['codigo'] = $_POST['nom_nomina_id'] . $_POST['nom_otro_pago_id'] . $_POST['descripcion'] .
            $_POST['importe_gravado'] . $_POST['importe_exento'];
        $_POST['codigo_bis'] = $_POST['codigo'];

        $r_alta_nom_par_otro_paago = (new nom_par_otro_pago($this->link))->alta_registro(registro: $_POST);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al dar de alta deduccion', data: $r_alta_nom_par_otro_paago,
                header: $header, ws: $ws);
        }

        $siguiente_view = (new actions())->init_alta_bd();
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener siguiente view', data: $siguiente_view,
                header: $header, ws: $ws);
        }

        if ($header) {
            $this->retorno_base(registro_id:$this->registro_id, result: $r_alta_nom_par_otro_paago,
                siguiente_view: $siguiente_view, ws:  $ws);
        }
        if ($ws) {
            header('Content-Type: application/json');
            echo json_encode($r_alta_nom_par_otro_paago, JSON_THROW_ON_ERROR);
            exit;
        }
        $r_alta_nom_par_otro_paago->siguiente_view = $siguiente_view;

        return $r_alta_nom_par_otro_paago;
    }

    public function recalcula_neto_bd(bool $header, bool $ws = false){
        if (isset($_POST['btn_action_next'])) {
            unset($_POST['btn_action_next']);
        }

        $monto = (new nom_nomina($this->link))->calculo_bruto(registro: $_POST, registro_id: $this->registro_id);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al calcular bruto sobre neto', data: $monto,
                header: $header, ws: $ws);
        }

        $_POST['bruto'] = $monto;

        $link = "./index.php?seccion=nom_nomina&accion=selecciona_percepcion&registro_id=".$this->registro_id;
        $link.="&session_id=$this->session_id";
        header('Location:' . $link);
        exit;
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

    private function ruta_archivos(): array|string
    {
        $ruta_archivos = (new generales())->path_base.'archivos';
        if(!file_exists($ruta_archivos)){
            mkdir($ruta_archivos,0777,true);
        }
        if(!file_exists($ruta_archivos)){
            return $this->errores->error(mensaje: 'Error no existe '.$ruta_archivos, data: $ruta_archivos);
        }
        return $ruta_archivos;
    }

    private function ruta_archivos_tmp(string $ruta_archivos): array|string
    {
        $ruta_archivos_tmp = $ruta_archivos.'/tmp';

        if(!file_exists($ruta_archivos_tmp)){
            mkdir($ruta_archivos_tmp,0777,true);
        }
        if(!file_exists($ruta_archivos_tmp)){
            return $this->errores->error(mensaje: 'Error no existe '.$ruta_archivos_tmp, data: $ruta_archivos_tmp);
        }
        return $ruta_archivos_tmp;
    }

    public function timbra(bool $header, bool $ws = false): array|stdClass
    {
        $nom_nomina = $this->modelo->registro(registro_id: $this->registro_id, retorno_obj: true);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al obtener nomina', data: $nom_nomina, header: $header, ws: $ws);
        }


        print_r($nom_nomina);exit;

        return $nom_nomina;
    }


}
