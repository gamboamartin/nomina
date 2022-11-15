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
use gamboamartin\plugins\files;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\cat_sat_moneda_html;
use html\com_cliente_html;
use html\com_producto_html;
use html\com_sucursal_html;
use html\em_empleado_html;
use html\nom_deduccion_html;
use html\nom_percepcion_html;
use html\nom_periodo_html;
use models\com_cliente;
use models\com_producto;
use models\com_sucursal;
use models\doc_documento;
use models\em_empleado;
use models\nom_concepto_imss;
use models\nom_deduccion;
use models\nom_nomina;
use models\nom_percepcion;
use models\nom_periodo;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;

class controlador_nom_periodo extends system {

    public stdClass $nominas;
    public int $nom_periodo_id = -1;
    public string $link_lee_archivo = '';

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_periodo(link: $link);
        $html_ = new nom_periodo_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);
        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Periodos';

        $link_lee_archivo = $obj_link->link_con_id(accion: 'lee_archivo',link: $link,
            registro_id: $this->registro_id, seccion: $this->seccion);
        if (errores::$error) {
            $error = $this->errores->error(mensaje: 'Error al generar link', data: $link_lee_archivo);
            print_r($error);
            die('Error');
        }

        $this->link_lee_archivo = $link_lee_archivo;

        $keys_row_lista = $this->keys_rows_lista();
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar keys de lista',data:  $keys_row_lista);
            print_r($error);
            exit;
        }
        $this->keys_row_lista = $keys_row_lista;
        $this->nom_periodo_id = $this->registro_id;
    }

    public function alta(bool $header, bool $ws = false): array|string
    {
        $r_alta =  parent::alta(header: false, ws: false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar template',data:  $r_alta, header: $header,ws:$ws);
        }

        $inputs = (new nom_periodo_html(html: $this->html_base))->genera_inputs_alta(controler: $this, link: $this->link);
        if(errores::$error){
            $error = $this->errores->error(mensaje: 'Error al generar inputs',data:  $inputs);
            print_r($error);
            die('Error');
        }
        return $r_alta;
    }

    private function asigna_link_periodo_nominas_row(stdClass $row): array|stdClass
    {
        $keys = array('nom_periodo_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_periodo_nominas = $this->obj_link->link_con_id(accion:'nominas',link: $this->link,
            registro_id:  $row->nom_periodo_id,
            seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_periodo_nominas);
        }

        $row->link_periodo_nominas = $link_periodo_nominas;
        $row->link_periodo_nominas_style = 'info';

        return $row;
    }

    private function asigna_link_sube_archivo_row(stdClass $row): array|stdClass
    {
        $keys = array('nom_periodo_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_sube_archivo = $this->obj_link->link_con_id(accion:'sube_archivo',link: $this->link,
            registro_id:  $row->nom_periodo_id,
            seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_sube_archivo);
        }

        $row->link_sube_archivo = $link_sube_archivo;
        $row->link_sube_archivo_style = 'info';

        return $row;
    }

    private function asigna_link_procesa_nomina_row(stdClass $row): array|stdClass
    {
        $keys = array('nom_periodo_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $row);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al validar row',data:  $valida);
        }

        $link_procesa_nomina = $this->obj_link->link_con_id(accion:'procesa_nomina',link:$this->link,
            registro_id:  $row->nom_periodo_id, seccion:  $this->tabla);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al genera link',data:  $link_procesa_nomina);
        }

        $row->link_procesa_nomina = $link_procesa_nomina;
        $row->link_procesa_nomina_style = 'info';

        return $row;
    }

    private function base(stdClass $params = new stdClass()): array|stdClass
    {
        $r_modifica =  parent::modifica(header: false,aplica_form:  false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        $keys = array('cat_sat_periodicidad_pago_nom_id','im_registro_patronal_id','cat_sat_tipo_nomina_id',
            'nom_tipo_periodo_id');

        foreach ($keys as $key){
            if(!isset($this->row_upd->$key)){
                $this->row_upd->$key = -1;
            }
        }

        $inputs = (new nom_periodo_html(html: $this->html_base))->inputs_nom_periodo(
            controlador:$this, params: $params);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al inicializar inputs',data:  $inputs);
        }

        $data = new stdClass();
        $data->template = $r_modifica;
        $data->inputs = $inputs;

        return $data;
    }

    private function data_nomina_btn(array $nomina): array
    {
        $btn_elimina = $this->html_base->button_href(accion: 'elimina_bd', etiqueta: 'Elimina',
            registro_id: $nomina['nom_nomina_id'], seccion: 'nom_nomina', style: 'danger');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $nomina['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'modifica', etiqueta: 'Modifica',
            registro_id: $nomina['nom_nomina_id'], seccion: 'nom_nomina', style: 'warning');
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $nomina['link_modifica'] = $btn_modifica;

        return $nomina;
    }

    public function lista(bool $header, bool $ws = false): array
    {
        $r_lista = parent::lista($header, $ws); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar datos',data:  $r_lista, header: $header,ws:$ws);
        }

        $registros = $this->maqueta_registros_lista(registros: $this->registros);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al maquetar registros',data:  $registros, header: $header,ws:$ws);
        }
        $this->registros = $registros;



        return $r_lista;
    }

    private function keys_rows_lista(): array
    {
        $keys_row_lista = array();

        $keys = array('nom_periodo_id','nom_periodo_codigo','nom_periodo_descripcion','nom_periodo_fecha_inicial_pago',
            'nom_periodo_fecha_final_pago','nom_periodo_fecha_pago');

        foreach ($keys as $campo){
            $keys_row_lista = $this->key_row_lista_init(campo: $campo, keys_row_lista: $keys_row_lista);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al inicializar key',data: $keys_row_lista);
            }
        }

        return $keys_row_lista;
    }

    private function key_row_lista_init(string $campo, array $keys_row_lista): array
    {
        $data = new stdClass();
        $data->campo = $campo;

        $campo = str_replace(array('nom_periodo_', '_'), array('', ' '), $campo);
        $campo = ucfirst(strtolower($campo));

        $data->name_lista = $campo;
        $keys_row_lista[]= $data;
        return $keys_row_lista;
    }

    private function maqueta_registros_lista(array $registros): array
    {
        foreach ($registros as $indice=> $row){
            $row = $this->asigna_link_procesa_nomina_row(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }
            $registros[$indice] = $row;

            $row = $this->asigna_link_periodo_nominas_row(row: $row);
            if(errores::$error){
                return $this->errores->error(mensaje: 'Error al maquetar row',data:  $row);
            }
            $registros[$indice] = $row;

            $row = $this->asigna_link_sube_archivo_row(row: $row);
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

    public function calcula_cuota_obrero_patronal(stdClass $row){

        $filtro['nom_periodo.id'] = $row->nom_periodo_id;
        $nominas = (new nom_nomina($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al obtener nominas del periodo',data:  $nominas);
        }

        $cuotas = 0;
        foreach ($nominas->registros as $nomina){
            $campos['cuotas'] = 'nom_concepto_imss.monto';
            $filtro_sum['nom_nomina.id'] = $nomina['nom_nomina_id'];
            $total_cuota = (new nom_concepto_imss($this->link))->suma(campos: $campos,filtro: $filtro_sum);
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al obtener suma', data: $total_cuota);
            }
            $cuotas += (float)$total_cuota['cuotas'];
        }

        $row->total_cuota_patronal = $cuotas;

        return $row;
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

    public function sube_archivo(bool $header, bool $ws = false){
        $r_modifica =  parent::modifica(header: false,aplica_form:  false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error al generar template',data:  $r_modifica);
        }

        return $r_modifica;
    }

    /**
     * @throws \JsonException
     */
    public function lee_archivo(bool $header, bool $ws = false)
    {
        $doc_documento_modelo = new doc_documento($this->link);
        $doc_documento_modelo->registro['doc_tipo_documento_id'] = 1;
        $doc_documento = $doc_documento_modelo->alta_bd(file: $_FILES['archivo']);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al dar de alta el documento', data: $doc_documento);
        }

        $empleados_excel = $this->obten_empleados_excel(ruta_absoluta: $doc_documento->registro['doc_documento_ruta_absoluta']);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener empleados',data:  $empleados_excel);
        }

        $resultado = (new nom_periodo($this->link))->genera_registro_nomina_excel(nom_periodo_id: $this->registro_id,
        empleados_excel: $empleados_excel);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar mensaje', data: $resultado,header:  $header,ws:  $ws);
        }

        $link = "./index.php?seccion=nom_periodo&accion=nominas&registro_id=".$this->registro_id;
        $link.="&session_id=$this->session_id";
        header('Location:' . $link);
        exit;
    }

    public function obten_columna_faltas(Spreadsheet $documento){
        $totalDeHojas = $documento->getSheetCount();

        $columna = -1;
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $valorRaw = $celda->getValue();
                    if($valorRaw === 'FALTAS') {
                        $columna = $celda->getColumn();
                    }
                }
            }
        }

        return $columna;
    }

    public function obten_empleados_excel(string $ruta_absoluta){
        $documento = IOFactory::load($ruta_absoluta);
        $totalDeHojas = $documento->getSheetCount();

        $columna_faltas = $this->obten_columna_faltas(documento: $documento);
        if(errores::$error){
            return $this->errores->error(mensaje: 'Error obtener columna de faltas',data:  $columna_faltas);
        }

        $empleados = array();
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {
            $hojaActual = $documento->getSheet($indiceHoja);
            $registros = array();
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $fila = $celda->getRow();
                    $valorRaw = $celda->getValue();
                    $columna = $celda->getColumn();

                    if($fila >= 7){
                        if($columna === "A" && is_numeric($valorRaw)){
                            $reg = new stdClass();
                            $reg->fila = $fila;
                            $registros[] = $reg;
                        }
                    }
                }
            }

            foreach ($registros as $registro){
                $reg = new stdClass();
                $reg->codigo = $hojaActual->getCell('A'.$registro->fila)->getValue();
                $reg->nombre = $hojaActual->getCell('B'.$registro->fila)->getValue();
                $reg->ap = $hojaActual->getCell('C'.$registro->fila)->getValue();
                $reg->am = $hojaActual->getCell('D'.$registro->fila)->getValue();
                $reg->faltas = $hojaActual->getCell($columna_faltas.$registro->fila)->getValue();
                $empleados[] = $reg;
            }
        }

        return $empleados;
    }

    public function guarda_archivo(array $file){
        $ruta_archivos = (new generales())->path_base.'/archivos/';
        $ruta_relativa = 'archivos/'.$this->tabla.'/';
        if(!is_dir($ruta_archivos) && !mkdir($ruta_archivos) && !is_dir($ruta_archivos)) {
            return $this->errores->error(mensaje: 'Error crear directorio', data: $ruta_archivos);
        }

        $ruta_absoluta_directorio = (new generales())->path_base.$ruta_relativa;
        if(!is_dir($ruta_absoluta_directorio) && !mkdir($ruta_absoluta_directorio) &&
            !is_dir($ruta_absoluta_directorio)) {
            return $this->errores->error(mensaje: 'Error crear directorio', data: $ruta_absoluta_directorio);
        }

        $nombre_doc = $file['archivo']['name'];

        $ruta_absoluta = strtolower($ruta_absoluta_directorio.$nombre_doc);
        if(!file_exists($ruta_absoluta)){
            $guarda = (new files())->guarda_archivo_fisico(contenido_file:  file_get_contents($file['archivo']['tmp_name']),
                ruta_file: $ruta_absoluta);
            if(errores::$error){
                return $this->errores->error('Error al guardar archivo', $guarda);
            }
        }

        return $ruta_absoluta;
    }

    public function nominas(bool $header, bool $ws = false): array|stdClass
    {
        $filtro['nom_periodo.id'] = $this->registro_id;
        $nominas = (new nom_nomina($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al obtener nominas del periodo',data:  $nominas,
                header: $header,ws:$ws);
        }

        foreach ($nominas->registros as $indice => $nomina) {
            $nomina = $this->data_nomina_btn(nomina: $nomina);
            if (errores::$error) {
                return $this->retorno_error(mensaje: 'Error al asignar botones', data: $nomina, header: $header, ws: $ws);
            }
            $nominas->registros[$indice] = $nomina;
        }
        $this->nominas = $nominas;

        return $this->nominas;
    }

    public function procesa_nomina(bool $header, bool $ws = false, string $breadcrumbs = '', bool $aplica_form = true,
                                   bool $muestra_btn = true): array|string
    {

        $resultado = (new nom_periodo($this->link))->genera_registro_nomina(nom_periodo_id: $this->registro_id);
        if(errores::$error){
            return $this->retorno_error(mensaje: 'Error al generar mensaje', data: $resultado,header:  $header,ws:  $ws);
        }
        $link = "./index.php?seccion=nom_periodo&accion=nominas&registro_id=".$this->registro_id;
        $link.="&session_id=$this->session_id";
        header('Location:' . $link);
        exit;


    }
}
