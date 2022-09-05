<?php

namespace models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_nomina extends modelo
{
    public function __construct(PDO $link)
    {
        $tabla = __CLASS__;
        $columnas = array($tabla => false, 'dp_calle_pertenece' => $tabla, 'dp_calle' => 'dp_calle_pertenece',
            'dp_colonia_postal' => 'dp_calle_pertenece', 'dp_colonia' => 'dp_colonia_postal',
            'dp_cp' => 'dp_colonia_postal', 'dp_municipio' => 'dp_cp', 'dp_estado' => 'dp_municipio',
            'dp_pais' => 'dp_estado', 'em_empleado' => $tabla, 'fc_factura' => $tabla,'
            cat_sat_periodicidad_pago_nom'=>$tabla,'im_registro_patronal'=>$tabla);

        $campos_obligatorios = array('cat_sat_periodicidad_pago_nom_id','em_cuenta_bancaria_id','fecha_inicial_pago',
            'fecha_final_pago','num_dias_pagados','im_registro_patronal_id','em_empleado_id');

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function alta_bd(): array|stdClass
    {
        $registros = $this->genera_registros();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros', data: $registros);
        }

        $registros_factura = $this->genera_registro_factura(registros: $registros['fc_csd'],
            empleado_sucursal: $registros['nom_rel_empleado_sucursal'],cat_sat: $registros['nom_conf_empleado']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de factura', data: $registros_factura);
        }

        $r_alta_factura = $this->inserta_factura(registro: $registros_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la factura', data: $r_alta_factura);
        }

        $registros_cfd_partida = $this->genera_registro_partida(fc_factura: $r_alta_factura,em_empleado: $registros['em_empleado'],
            conf_empleado: $registros['nom_conf_empleado']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de cfd partida', data: $registros_cfd_partida);
        }

        $r_alta_cfd_partida  = $this->inserta_partida(registro: $registros_cfd_partida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfd partida', data: $r_alta_cfd_partida);
        }

        $this->registro = $this->limpia_campos(registro: $this->registro,
            campos_limpiar: array('folio', 'fecha', 'descuento','nom_conf_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $this->registro);
        }

        $this->registro = $this->genera_registro_nomina(registros: $registros, fc_factura: $r_alta_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de nomina', data: $this->registro);
        }

        $r_alta_bd = parent::alta_bd(); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar nomina', data: $r_alta_bd);
        }

        $nom_par_percepcion_ins = array();
        $nom_par_percepcion_ins['nom_nomina_id'] = $r_alta_bd->registro_id;
        $nom_par_percepcion_ins['nom_percepcion_id'] = 1;
        $nom_par_percepcion_ins['importe_gravado'] = $registros_cfd_partida['valor_unitario'] * $registros_cfd_partida['cantidad'];

        $r_alta_nom_par_percepcion = (new nom_par_percepcion($this->link))->alta_registro(registro: $nom_par_percepcion_ins);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar percepcion default', data: $r_alta_nom_par_percepcion);
        }





        return $r_alta_bd;
    }

    /**
     * @param int $nom_nomina_id Nomina en proceso
     * @return bool|array
     */
    public function aplica_imss(int $nom_nomina_id): bool|array
    {
        $partidas = $this->partidas(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener partidas', data: $partidas);
        }

        $aplica_imss = $this->aplica_imss_base(partidas: $partidas);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss);
        }


        return $aplica_imss;

    }

    /**
     * @param stdClass $partidas Partidas de nomina
     * @return bool|array
     */
    private function aplica_imss_base(stdClass $partidas): bool|array
    {
        $aplica_imss = $this->aplica_imss_percepcion(obj: 'percepciones', partidas: $partidas, tabla: 'nom_percepcion');
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss);
        }

        if(!$aplica_imss) {

            $aplica_imss = $this->aplica_imss_percepcion(obj: 'otros_pagos', partidas: $partidas, tabla: 'nom_otro_pago');
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss);
            }

        }
        return $aplica_imss;
    }

    private function aplica_imss_bool(bool $es_imss_activo, bool $existe_key_imss): bool
    {
        return $existe_key_imss && $es_imss_activo;
    }

    /**
     * @param array $partida Partida a verificar
     * @param string $tabla
     * @return array|stdClass
     */
    private function aplica_imss_init(array $partida, string $tabla): array|stdClass
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        $existe_key_imss = $this->existe_key_imss(partida: $partida, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key imss', data: $existe_key_imss);
        }
        $es_imss_activo = $this->es_imss_activo(partida: $partida, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key imss', data: $es_imss_activo);
        }

        $data = new stdClass();
        $data->existe_key_imss = $existe_key_imss;
        $data->es_imss_activo = $es_imss_activo;

        return $data;
    }

    /**
     * @param string $obj Nombre del objeto a verificar
     * @param stdClass $partidas
     * @param string $tabla
     * @return bool|array
     */
    private function aplica_imss_percepcion(string $obj,stdClass $partidas, string $tabla): bool|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        $aplica_imss = false;
        foreach ($partidas->$obj as $partida){

            $aplica_imss_bool = $this->aplica_imss_val(partida: $partida, tabla: $tabla);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss_bool);
            }

            if($aplica_imss_bool){
                $aplica_imss = true;
                break;
            }
        }
        return $aplica_imss;
    }

    /**
     * @param array $partida Partida a verificar
     * @param string $tabla
     * @return bool|array
     */
    private function aplica_imss_val(array $partida, string $tabla): bool|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        $init = $this->aplica_imss_init(partida: $partida, tabla: $tabla);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener key imss', data: $init);
        }
        $aplica_imss_bool = $this->aplica_imss_bool(
            es_imss_activo:$init->es_imss_activo, existe_key_imss: $init->existe_key_imss);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener aplica imss', data: $aplica_imss_bool);
        }

        return $aplica_imss_bool;
    }


    /**
     * Verifica si aplica subsidio percepcion
     * @param int $nom_nomina_id Nomina a validar
     * @return bool|array
     * @version 0.220.6
     */
    public function aplica_subsidio_percepcion(int $nom_nomina_id): bool|array
    {
        if($nom_nomina_id <= 0){
            return $this->error->error(mensaje: 'Error nom_nomina_id es menor a 1', data: $nom_nomina_id);
        }
        $filtro['nom_percepcion.aplica_subsidio'] = 'activo';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $existe = (new nom_par_percepcion($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al verificar si existe registro', data: $existe);
        }
        return $existe;
    }

    private function asigna_campo(array $registro, string $campo, array $campos_asignar): array
    {
        if (!isset($registro[$campo])) {
            $valor_generado = $this->genera_valor_campo(campos_asignar: $campos_asignar);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar el campo', data: $valor_generado);
            }
            $registro[$campo] = $valor_generado;
        }
        return $registro;
    }

    private function asigna_codigo_nomina(array $registro): array
    {
        if (!isset($registro['codigo'])) {

            $codigo = $this->codigo_nomina(org_sucursal_id: $registro['org_sucursal_id'],
                registro: $registro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar codigo', data: $codigo);
            }

            $registro['codigo'] = $codigo;
        }
        return $registro;
    }

    private function asigna_descripcion_nomina(array $registro): array
    {
        if (!isset($registro['descripcion'])) {

            $descripcion = $this->descripcion_nomina(em_empleado_id: $registro['em_empleado_id'],
                registro: $registro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar descripcion', data: $descripcion);
            }
            $registro['descripcion'] = $descripcion;
        }
        return $registro;
    }

    private function codigo_nomina(int $org_sucursal_id, array $registro): array|string
    {

        $org_sucursal = (new org_sucursal($this->link))->registro(registro_id: $org_sucursal_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sucursal', data: $org_sucursal);
        }

        $codigo = $this->genera_codigo_nomina(org_sucursal: $org_sucursal, registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar codigo', data: $codigo);
        }
        return $codigo;
    }

    private function deduccion_isr(int $nom_nomina_id){

        $existe_deduccion_isr = $this->existe_deduccion_isr(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion isr',data:  $existe_deduccion_isr);
        }
        if(!$existe_deduccion_isr){
            return $this->error->error(mensaje: 'Error no existe la deduccion ISR',data:  $existe_deduccion_isr);
        }

        $filtro['nom_deduccion.es_isr']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $r_nom_par_deduccion);
        }
        if($r_nom_par_deduccion->n_registros > 1){
            return $this->error->error(mensaje: 'Error solo puede existir una deduccion de tipo ISR',
                data:  $r_nom_par_deduccion);
        }
        return $r_nom_par_deduccion->registros[0];


    }

    public function deduccion_isr_id(int $nom_nomina_id): array|int
    {

        $deduccion_isr = $this->deduccion_isr(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $deduccion_isr);
        }
        return (int)$deduccion_isr['nom_par_deduccion_id'];

    }



    /**
     * Obtiene todas las deducciones de una nomina
     * @param int $nom_nomina_id Identificador de nomina
     * @return array
     * @version 0.146.6
     */
    public function deducciones(int $nom_nomina_id): array
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_deduccion = (new nom_par_deduccion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $r_nom_par_deduccion);
        }
        return $r_nom_par_deduccion->registros;
    }

    private function descripcion_nomina(int $em_empleado_id, array $registro): array|string
    {
        $em_empleado = (new em_empleado($this->link))->registro(registro_id: $em_empleado_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener empleado', data: $em_empleado);
        }

        $descripcion = $this->genera_descripcion(em_empleado: $em_empleado, registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar descripcion', data: $descripcion);
        }
        return $descripcion;
    }

    public function elimina_bd(int $id): array
    {
        $r_elimina_bd = parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar nomina', data: $r_elimina_bd);
        }
        return $r_elimina_bd;
    }

    private function es_imss_activo(array $partida, string $tabla): bool
    {
        return $partida[$tabla.'_aplica_imss'] === 'activo';
    }

    /**
     * Valida si existe una deduccion de tipo ISR
     * @param int $nom_nomina_id Nomina a validar
     * @return bool|array
     * @version 0.224.6
     */
    private function existe_deduccion_isr(int $nom_nomina_id): bool|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0',data:  $nom_nomina_id);
        }
        $filtro['nom_deduccion.es_isr']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $existe = (new nom_par_deduccion($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $existe);
        }
        return $existe;
    }

    /**
     * Verifica si existe o no el key de aplicacion de imss
     * @param array $partida Partida a verificar
     * @param string $tabla Tabla nom_percepcion o nom_deduccion etc
     * @return bool|array
     * @version 0.157.6
     */
    private function existe_key_imss(array $partida, string $tabla): bool|array
    {
        $tabla = trim($tabla);
        if($tabla === ''){
            return $this->error->error(mensaje: 'Error tabla vacia', data: $tabla);
        }
        return isset($partida[$tabla.'_aplica_imss']);
    }

    private function existe_otro_pago_subsidio(int $nom_nomina_id): bool|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0',data:  $nom_nomina_id);
        }
        $filtro['nom_otro_pago.es_subsidio']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $existe = (new nom_par_otro_pago($this->link))->existe(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion',data:  $existe);
        }
        return $existe;
    }

    private function genera_codigo_nomina(stdClass $org_sucursal, array $registro): string
    {
        $serie = $org_sucursal->org_sucursal_serie;
        $folio = $registro['folio'];
        return $org_sucursal->org_sucursal_id . $serie . $folio;
    }

    private function genera_descripcion(stdClass $em_empleado, array $registro): string
    {
        return $em_empleado->em_empleado_id .
            $em_empleado->em_empleado_nombre .
            $em_empleado->em_empleado_ap .
            $em_empleado->em_empleado_am .
            $em_empleado->em_empleado_rfc;
    }

    public function get_descuento_nomina(int $fc_factura_id): float
    {
        $descuento = (new fc_factura($this->link))->get_descuento( fc_factura_id: $fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el descuento  de la partida',
                data: $descuento);
        }
        return $descuento;
    }

    private function get_percepciones(int $nom_nomina_id): array
    {
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro:$filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina_partida_percepcion', data: $r_nom_par_percepcion);
        }
        return $r_nom_par_percepcion->registros_obj;
    }

    public function get_sub_total_nomina(int $fc_factura_id): float|array
    {
        $subtotal = (new fc_factura($this->link))->get_factura_sub_total( fc_factura_id: $fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el subtotal de la partida',
                data: $subtotal);
        }
        return $subtotal;
    }

    private function get_sucursal_by_empleado(int $em_empleado_id){
        $filtro['em_empleado.id'] = $em_empleado_id;
        $nom_rel_empleado_sucursal = (new nom_rel_empleado_sucursal($this->link))->filtro_and( filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado sucursal ',
                data: $nom_rel_empleado_sucursal);
        }
        if((int)$nom_rel_empleado_sucursal->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe sucursal relacionada con empleado',
                data: $nom_rel_empleado_sucursal);
        }
        if((int)$nom_rel_empleado_sucursal->n_registros > 1){
            return $this->error->error(mensaje: 'Error de integridad solo puede existir un empleado por sucursal',
                data: $nom_rel_empleado_sucursal);
        }
        return $nom_rel_empleado_sucursal->registros[0];
    }

    private function genera_registros(): array
    {

        $keys = array('im_registro_patronal_id','em_empleado_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $this->registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $im_registro_patronal = $this->registro_por_id(new im_registro_patronal($this->link),
            $this->registro['im_registro_patronal_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de registro patronal',
                data: $im_registro_patronal);
        }

        $fc_csd_id = $this->registro_por_id(new fc_csd($this->link),
            $im_registro_patronal->im_registro_patronal_fc_csd_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de fcd', data: $fc_csd_id);
        }

        $em_empleado = $this->registro_por_id(new em_empleado($this->link), $this->registro['em_empleado_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado ', data: $em_empleado);
        }

        /**
         * REVBISAR HARDCODEO
         */
        $nom_conf_empleado = $this->registro_por_id(new nom_conf_empleado($this->link), 1);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de conf factura',
                data: $nom_conf_empleado);
        }

        $nom_rel_empleado_sucursal = $this->get_sucursal_by_empleado(em_empleado_id: $this->registro['em_empleado_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sucursal de empleado para cfdi',
                data: $nom_rel_empleado_sucursal);
        }


        $registros = array('im_registro_patronal' => $im_registro_patronal, 'em_empleado' => $em_empleado,
            'fc_csd' => $fc_csd_id, 'nom_rel_empleado_sucursal' => $nom_rel_empleado_sucursal,
            'nom_conf_empleado' => $nom_conf_empleado);

        return $registros;
    }

    private function genera_registro_factura(mixed $registros, mixed $empleado_sucursal, mixed $cat_sat): array
    {
        $keys = array('folio','fecha');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $keys = array('com_sucursal_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $empleado_sucursal);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $folio = $this->registro['folio'];
        $serie = $registros->fc_csd_serie;
        $fecha = $this->registro['fecha'];
        $fc_csd_id = $registros->fc_csd_id;
        $com_sucursal_id = $empleado_sucursal['com_sucursal_id'];
        $cat_sat_forma_pago_id = $cat_sat->nom_conf_factura_cat_sat_forma_pago_id;
        $cat_sat_metodo_pago_id = $cat_sat->nom_conf_factura_cat_sat_metodo_pago_id;
        $cat_sat_moneda_id = $cat_sat->nom_conf_factura_cat_sat_moneda_id;
        $com_tipo_cambio_id = $cat_sat->nom_conf_factura_com_tipo_cambio_id;
        $cat_sat_uso_cfdi_id = $cat_sat->nom_conf_factura_cat_sat_uso_cfdi_id;
        $cat_sat_tipo_de_comprobante_id = $cat_sat->nom_conf_factura_cat_sat_tipo_de_comprobante_id;

        $regisro_factura = array('folio' => $folio, 'serie' => $serie, 'fecha' => $fecha,
            'fc_csd_id' => $fc_csd_id, 'com_sucursal_id' => $com_sucursal_id,
            'cat_sat_forma_pago_id' => $cat_sat_forma_pago_id, 'cat_sat_metodo_pago_id' => $cat_sat_metodo_pago_id,
            'cat_sat_moneda_id' => $cat_sat_moneda_id, 'com_tipo_cambio_id' => $com_tipo_cambio_id,
            'cat_sat_uso_cfdi_id' => $cat_sat_uso_cfdi_id, 'cat_sat_tipo_de_comprobante_id' => $cat_sat_tipo_de_comprobante_id);

        return $regisro_factura;
    }

    private function genera_registro_partida(mixed $fc_factura, mixed $em_empleado, mixed $conf_empleado) : array{

        $keys = array('num_dias_pagados','descuento');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $keys = array('fc_factura_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $fc_factura->registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }


        $codigo = "PN".$fc_factura->registro['fc_factura_id'];
        $descripcion = "pago nomina".$fc_factura->registro['fc_factura_id'];
        $descripcion_select = "PAGO NOMINA".$fc_factura->registro['fc_factura_id'];
        $alias = "PN".$fc_factura->registro['fc_factura_id'];
        $codigo_bis = "PN".$fc_factura->registro['fc_factura_id'];
        $com_producto_id = $conf_empleado->nom_conf_factura_com_producto_id;
        $cantidad = 1;
        $valor_unitario= $this->registro['num_dias_pagados'] * $em_empleado->em_empleado_salario_diario;
        $descuento = $this->registro['descuento'];
        $fc_factura_id= $fc_factura->registro['fc_factura_id'];

        return array('codigo' => $codigo, 'descripcion' => $descripcion, 'descripcion_select' => $descripcion_select,
            'alias' => $alias, 'codigo_bis' => $codigo_bis,
            'com_producto_id' => $com_producto_id, 'cantidad' => $cantidad,
            'valor_unitario' => $valor_unitario, 'descuento' => $descuento,
            'fc_factura_id' => $fc_factura_id);
    }

    private function genera_registro_nomina(mixed $registros, mixed $fc_factura) : array{

        $asignar = array($registros['fc_csd']->org_sucursal_id,
            $fc_factura->registro['fc_factura_serie'], $fc_factura->registro['fc_factura_folio']);

        $registro = $this->asigna_campo(registro: $this->registro, campo: "codigo", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }
        $this->registro = $registro;

        $asignar = array($registros['em_empleado']->em_empleado_id, $registros['em_empleado']->em_empleado_nombre,
            $registros['em_empleado']->em_empleado_ap, $registros['em_empleado']->em_empleado_am,
            $registros['em_empleado']->em_empleado_rfc, $fc_factura->registro['fc_factura_codigo']);

        $registro = $this->asigna_campo(registro: $this->registro, campo: "descripcion", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $registro = $this->asigna_campo(registro: $this->registro, campo: "descripcion_select", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $asignar = array($fc_factura->registro['fc_factura_codigo'], $registros['em_empleado']->em_empleado_rfc);

        $registro = $this->asigna_campo(registro: $this->registro, campo: "alias", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $registro = $this->asigna_campo(registro: $this->registro, campo: "codigo_bis", campos_asignar: $asignar);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }
        $this->registro = $registro;

        $this->registro['fc_factura_id'] = $fc_factura->registro_id;

        return $this->registro;
    }

    private function genera_valor_campo(array $campos_asignar): string
    {
        return implode($campos_asignar);
    }



    private function inserta_partida(array $registro): array|stdClass
    {
        $r_alta_partida = (new fc_partida($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfd partida', data: $r_alta_partida);
        }
        return $r_alta_partida;
    }

    private function inserta_factura(array $registro): array|stdClass
    {
        $r_alta_factura = (new fc_factura($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la factura', data: $r_alta_factura);
        }
        return $r_alta_factura;
    }



    private function limpia_campos(array $registro, array $campos_limpiar): array
    {
        foreach ($campos_limpiar as $valor) {
            if (isset($registro[$valor])) {
                unset($registro[$valor]);
            }
        }
        return $registro;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $nom_nomina = $this->registro_por_id($this, $id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros de la nomina',
                data: $nom_nomina);
        }
        $fc_factura = array('folio' => $registro['folio'], 'fecha' => $registro['fecha']);


        $r_modifica_factura = (new fc_factura($this->link))->modifica_bd(registro: $fc_factura,id:
            $nom_nomina->nom_nomina_fc_factura_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de modificar la factura', data: $r_modifica_factura);
        }

        $registro = $this->limpia_campos(registro: $registro,
            campos_limpiar: array('folio', 'fecha', 'descuento','nom_conf_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $registro);
        }

        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al modificar la nomina', data: $r_modifica_bd);
        }

        return $r_modifica_bd;
    }

    private function otro_pago_subsidio(int $nom_nomina_id){

        $existe_otro_pago_subsidio = $this->existe_otro_pago_subsidio(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion isr',data:  $existe_otro_pago_subsidio);
        }
        if(!$existe_otro_pago_subsidio){
            return $this->error->error(mensaje: 'Error no existe otro pago subsidio',data:  $existe_otro_pago_subsidio);
        }

        $filtro['nom_otro_pago.es_subsidio']  = 'activo';
        $filtro['nom_nomina.id']  = $nom_nomina_id;
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otro pago',data:  $r_nom_par_otro_pago);
        }
        if($r_nom_par_otro_pago->n_registros > 1){
            return $this->error->error(mensaje: 'Error solo puede existir un otro pago de tipo SUBSIDIO',
                data:  $r_nom_par_otro_pago);
        }
        return $r_nom_par_otro_pago->registros[0];


    }
    public function otro_pago_subsidio_id(int $nom_nomina_id): array|int
    {

        $otro_pago_subsidio = $this->otro_pago_subsidio(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otro pago',data:  $otro_pago_subsidio);
        }
        return (int)$otro_pago_subsidio['nom_par_otro_pago_id'];

    }

    /**
     * Obtiene otros pagos de nomina
     * @param int $nom_nomina_id Nomina en proceso
     * @return array
     * @version 0.152.6
     */
    public function otros_pagos(int $nom_nomina_id): array
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }

        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $r_nom_par_otro_pago);
        }
        return $r_nom_par_otro_pago->registros;
    }

    /**
     * Obtiene las partidas de una nomina completa
     * @param int $nom_nomina_id Nomina en proceso
     * @return array|stdClass
     * @version 0.153.6
     */
    public function partidas(int $nom_nomina_id): array|stdClass
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }
        $percepciones = $this->percepciones(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }
        $deducciones = $this->deducciones(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $deducciones);
        }
        $otros_pagos = $this->otros_pagos(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener $otros_pagos', data: $otros_pagos);
        }

        $data = new stdClass();
        $data->percepciones = $percepciones;
        $data->deducciones = $deducciones;
        $data->otros_pagos = $otros_pagos;

        return $data;

    }

    /**
     * Obtiene todas las percepciones de una nomina
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return array
     * @version 1.144.6
     */
    public function percepciones(int $nom_nomina_id): array
    {
        if($nom_nomina_id<=0){
            return $this->error->error(mensaje: 'Error $nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }
        return $r_nom_par_percepcion->registros;
    }

    /**
     * Obtiene el total gravado de una nomina
     * @param int $nom_nomina_id Nomina a verificar
     * @return float|array
     * @version 0.67.1
     */
    public function total_gravado(int $nom_nomina_id): float|array
    {
        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_percepcion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }

        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_otro_pago.importe_gravado';
        $r_nom_par_otro_pago = (new nom_par_otro_pago($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $r_nom_par_otro_pago);
        }

        $total_percepciones = round($r_nom_par_percepcion['total_importe_gravado'],2);
        $total_otros_pagos = round($r_nom_par_otro_pago['total_importe_gravado'],2);

        $total_gravado = $total_percepciones + $total_otros_pagos;
        return round($total_gravado, 2);

    }

}