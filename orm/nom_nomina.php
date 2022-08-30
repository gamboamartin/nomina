<?php

namespace models;

use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use html\im_registro_patronal_html;
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

        $registros_factura = $this->genera_registro_factura(registros: $registros['fc_fcd'],
            empleado_sucursal: $registros['nom_rel_empleado_sucursal'],cat_sat: $registros['nom_conf_empleado']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de factura', data: $registros_factura);
        }

        $r_alta_factura = $this->inserta_factura(registro: $registros_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la factura', data: $r_alta_factura);
        }

        $registros_cfd_partida = $this->genera_registro_cfd_partida(fc_factura: $r_alta_factura,em_empleado: $registros['em_empleado']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de cfd partida', data: $registros_cfd_partida);
        }

        $r_alta_cfd_partida  = $this->inserta_cfd_partida(registro: $registros_cfd_partida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfd partida', data: $r_alta_cfd_partida);
        }

        $this->registro = $this->limpia_campos(registro: $this->registro,
            campos_limpiar: array('folio', 'fecha', 'descuento'));
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

    /**
     * Genera la suma del la cuota exedente y lacuota fija
     * @param float $cuota_excedente Monto calculado en base a la tabla de isr
     * @param stdClass $row_isr Registro de isr
     * @return float|array
     * @version 0.129.16
     */
    private function calcula_isr(float $cuota_excedente, stdClass $row_isr): float|array
    {
        $keys = array('cat_sat_isr_cuota_fija');
        $valida = $this->validacion->valida_double_mayores_igual_0(keys: $keys, registro: $row_isr);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar row isr', data: $valida);
        }
        if($cuota_excedente<0.0){
            return $this->error->error(mensaje: 'Error $cuota_excedente debe ser mayor o igual a 0',
                data: $cuota_excedente);
        }

        $isr = $cuota_excedente + $row_isr->cat_sat_isr_cuota_fija;
        return round($isr,2);

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

    /**
     * Calcula la cuota exedente para isr
     * @param float|int $diferencia_li Diferencia en te monto y limite inferior
     * @param stdClass $row_isr Registro en proceso
     * @return float|array
     * @version 0.126.16
     */
    private function cuota_excedente_isr(float|int $diferencia_li, stdClass $row_isr): float|array
    {
        $keys = array('cat_sat_isr_porcentaje_excedente');
        $valida = $this->validacion->valida_double_mayores_igual_0(keys: $keys, registro: $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row_isr', data: $valida);
        }

        if($diferencia_li<0.0){
            return $this->error->error(mensaje: 'Error $diferencia_li debe ser mayor a 0', data: $diferencia_li);
        }

        $cuota_excedente = $diferencia_li * $row_isr->cat_sat_isr_porcentaje_excedente;
        $cuota_excedente = round($cuota_excedente,2);
        $cuota_excedente /= 100;
        return round($cuota_excedente,2);
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

    /**
     * Obtiene la diferencia entre limite inferior menos monto
     * @param float|int $monto Monto total gravable
     * @param stdClass $row_isr Registro para isr
     * @return float|array
     * @version 0.119.14
     */
    private function diferencia_li(float|int $monto, stdClass $row_isr): float|array
    {
        $keys = array('cat_sat_isr_limite_inferior');
        $valida = $this->validacion->valida_double_mayores_0(keys: $keys, registro: $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row_isr', data: $valida);
        }

        $diferencia_li = $monto - $row_isr->cat_sat_isr_limite_inferior;
        $diferencia_li = round($diferencia_li, 2);
        if($diferencia_li<0.0){
            return $this->error->error(mensaje: 'Error el limite debe ser menor o igual al monto', data: $diferencia_li);
        }
        return $diferencia_li;
    }

    /**
     * Genera el filtro para la obtencion de tablas de isr
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha de nomina
     * @return array
     * @version 0.110.11
     */
    private function filtro_especial_isr(float|int $monto, string $fecha = ''): array
    {
        if($fecha === ''){
            $fecha = date('Y-m-d');
        }
        if($monto<=0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $monto);
        }

        $filtro_especial[0][$fecha]['operador'] = '>=';
        $filtro_especial[0][$fecha]['valor'] = 'cat_sat_isr.fecha_inicio';
        $filtro_especial[0][$fecha]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha]['operador'] = '<=';
        $filtro_especial[1][$fecha]['valor'] = 'cat_sat_isr.fecha_fin';
        $filtro_especial[1][$fecha]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha]['valor_es_campo'] = true;

        $filtro_especial[2][(string)$monto]['operador'] = '>=';
        $filtro_especial[2][(string)$monto]['valor'] = 'cat_sat_isr.limite_inferior';
        $filtro_especial[2][(string)$monto]['comparacion'] = 'AND';
        $filtro_especial[2][(string)$monto]['valor_es_campo'] = true;

        $filtro_especial[3][(string)$monto]['operador'] = '<=';
        $filtro_especial[3][(string)$monto]['valor'] = 'cat_sat_isr.limite_superior';
        $filtro_especial[3][(string)$monto]['comparacion'] = 'AND';
        $filtro_especial[3][(string)$monto]['valor_es_campo'] = true;

        return $filtro_especial;
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

    /**
     * Genera el monto de isr en base al registro aplicado
     * @param float|int $monto Monto total gravable
     * @param stdClass $row_isr Registro en proceso
     * @return float|array
     */
    private function genera_isr(float|int $monto, stdClass $row_isr): float|array
    {
        $diferencia_li = $this->diferencia_li(monto:$monto,row_isr:  $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener diferencia limite inferior', data: $diferencia_li);
        }

        $cuota_excedente = $this->cuota_excedente_isr(diferencia_li: $diferencia_li,row_isr:  $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener cuota excedente', data: $cuota_excedente);
        }

        $isr = $this->calcula_isr(cuota_excedente: $cuota_excedente,row_isr:  $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular isr', data: $isr);
        }

        return $isr;
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

        $fc_fcd_id = $this->registro_por_id(new fc_cfd($this->link),
            $im_registro_patronal->im_registro_patronal_fc_cfd_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de fcd', data: $fc_fcd_id);
        }

        $em_empleado = $this->registro_por_id(new em_empleado($this->link), $this->registro['em_empleado_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado ', data: $em_empleado);
        }

        $nom_conf_empleado = $this->registro_por_id(new nom_conf_empleado($this->link), $this->registro['em_empleado_id']);
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
            'fc_fcd' => $fc_fcd_id, 'nom_rel_empleado_sucursal' => $nom_rel_empleado_sucursal,
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

        $folio = $this->registro['folio'];
        $serie = $registros->fc_cfd_serie;
        $fecha = $this->registro['fecha'];
        $fc_cfd_id = $registros->fc_cfd_id;
        $com_sucursal_id = $empleado_sucursal['com_sucursal_id'];
        $cat_sat_forma_pago_id = $cat_sat->nom_conf_factura_cat_sat_forma_pago_id;
        $cat_sat_metodo_pago_id = $cat_sat->nom_conf_factura_cat_sat_metodo_pago_id;
        $cat_sat_moneda_id = $cat_sat->nom_conf_factura_cat_sat_moneda_id;
        $com_tipo_cambio_id = $cat_sat->nom_conf_factura_com_tipo_cambio_id;
        $cat_sat_uso_cfdi_id = $cat_sat->nom_conf_factura_cat_sat_uso_cfdi_id;
        $cat_sat_tipo_de_comprobante_id = $cat_sat->nom_conf_factura_cat_sat_tipo_de_comprobante_id;

        $regisro_factura = array('folio' => $folio, 'serie' => $serie, 'fecha' => $fecha,
            'fc_cfd_id' => $fc_cfd_id, 'com_sucursal_id' => $com_sucursal_id,
            'cat_sat_forma_pago_id' => $cat_sat_forma_pago_id, 'cat_sat_metodo_pago_id' => $cat_sat_metodo_pago_id,
            'cat_sat_moneda_id' => $cat_sat_moneda_id, 'com_tipo_cambio_id' => $com_tipo_cambio_id,
            'cat_sat_uso_cfdi_id' => $cat_sat_uso_cfdi_id, 'cat_sat_tipo_de_comprobante_id' => $cat_sat_tipo_de_comprobante_id);

        return $regisro_factura;
    }

    private function genera_registro_cfd_partida(mixed $fc_factura, mixed $em_empleado) : array{

        $keys = array('num_dias_pagados','descuento');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }
        $codigo = mt_rand();
        $descripcion = mt_rand();
        $descripcion_select = mt_rand();
        $alias = mt_rand();
        $codigo_bis = mt_rand();
        $com_producto_id = 1;
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

        $asignar = array($registros['fc_fcd']->org_sucursal_id,
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

    /**
     * @param int $cat_sat_periodicidad_pago_nom_id Periodicidad de pago identificador
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha din del periodo de pago
     * @return array|stdClass
     * @version 0.113.11
     */
    private function get_isr(int $cat_sat_periodicidad_pago_nom_id, float|int $monto, string $fecha = ''):array|stdClass{

        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor  a 0',
                data: $cat_sat_periodicidad_pago_nom_id);
        }

        $filtro['cat_sat_periodicidad_pago_nom.id'] = $cat_sat_periodicidad_pago_nom_id;

        if($fecha === ''){
            $fecha = date('Y-m-d');
        }

        if($monto<=0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $monto);
        }

        $filtro_especial = $this->filtro_especial_isr(monto: $monto, fecha : $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro', data: $filtro_especial);
        }

        $r_isr = (new cat_sat_isr($this->link))->filtro_and(filtro: $filtro, filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener isr', data: $r_isr);
        }

        if($r_isr->n_registros===0){
            return $this->error->error(mensaje: 'Error no existe registro isr', data: $r_isr);
        }
        if($r_isr->n_registros>1){
            return $this->error->error(mensaje: 'Error existe mas de un registro de isr', data: $r_isr);
        }


        return $r_isr->registros_obj[0];
    }

    private function inserta_cfd_partida(array $registro): array|stdClass
    {
        $r_alta_cfd_partida = (new fc_cfd_partida($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfd partida', data: $r_alta_cfd_partida);
        }
        return $r_alta_cfd_partida;
    }

    private function inserta_factura(array $registro): array|stdClass
    {
        $r_alta_factura = (new fc_factura($this->link))->alta_registro(registro: $registro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la factura', data: $r_alta_factura);
        }
        return $r_alta_factura;
    }

    /**
     * Calcula el isr
     * @param int $cat_sat_periodicidad_pago_nom_id
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha din del periodo de pago
     * @return float|array
     */
    public function isr(int $cat_sat_periodicidad_pago_nom_id, float|int $monto, string $fecha = ''): float|array
    {
        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $cat_sat_periodicidad_pago_nom_id debe ser mayor a 0',
                data: $cat_sat_periodicidad_pago_nom_id);
        }

        if($fecha === ''){
            $fecha = date('Y-m-d');
        }

        $row_isr = $this->get_isr(cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id, monto:$monto,
            fecha: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener isr', data: $row_isr);
        }

        $isr = $this->genera_isr(monto: $monto, row_isr: $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular isr', data: $isr);
        }

        return $isr;

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