<?php

namespace models;

use base\orm\modelo;
use gamboamartin\errores\errores;
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
        $campos_obligatorios = array('cat_sat_periodicidad_pago_nom_id','em_cuenta_bancaria_id');

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

        $registros_cfd_partida = $this->genera_registro_cfd_partida(fc_factura: $r_alta_factura);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de cfd partida', data: $registros_cfd_partida);
        }

        $r_alta_cfd_partida  = $this->inserta_cfd_partida(registro: $registros_cfd_partida);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta cfd partida', data: $r_alta_cfd_partida);
        }

        $this->registro = $this->limpia_campos(registro: $this->registro,
            campos_limpiar: array('folio', 'fecha'));
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

    private function calcula_isr(float $cuota_excedente, stdClass $row_isr): float
    {
        $isr = $cuota_excedente + $row_isr->cat_sat_isr_cuota_fija;
        return round($isr,2);

    }

    private function cuota_excedente_isr(float|int $diferencia_li, stdClass $row_isr): float
    {
        $cuota_excedente = $diferencia_li * $row_isr->cat_sat_isr_porcentaje_excedente;
        $cuota_excedente = round($cuota_excedente,2);
        $cuota_excedente /= 100;
        return round($cuota_excedente,2);
    }

    private function diferencia_li(float|int $monto, stdClass $row_isr): float
    {
        $diferencia_li = $monto - $row_isr->cat_sat_isr_limite_inferior;
        return round($diferencia_li, 2);
    }

    private function genera_isr(float $monto, stdClass $row_isr): float|array
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
        $im_registro_patronal = $this->registros_por_id(new im_registro_patronal($this->link),
            $this->registro['im_registro_patronal_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de registro patronal',
                data: $im_registro_patronal);
        }

        $fc_fcd_id = $this->registros_por_id(new fc_cfd($this->link),
            $im_registro_patronal->im_registro_patronal_fc_fcd_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de fcd', data: $fc_fcd_id);
        }

        $em_empleado = $this->registros_por_id(new em_empleado($this->link), $this->registro['em_empleado_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de empleado ', data: $em_empleado);
        }

        $nom_conf_empleado = $this->registros_por_id(new nom_conf_empleado($this->link), $this->registro['em_empleado_id']);
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

    private function genera_registro_cfd_partida(mixed $fc_factura) : array{

        $codigo = rand();
        $descripcion = rand();
        $descripcion_select = rand();
        $alias = rand();
        $codigo_bis = rand();
        $com_producto_id = 1;
        $cantidad = 1;
        $valor_unitario= 1;
        $descuento = 1;
        $fc_factura_id= $fc_factura->registro['fc_factura_id'];

        $regisro_cfd_partida = array('codigo' => $codigo, 'descripcion' => $descripcion, 'descripcion_select' => $descripcion_select,
            'alias' => $alias, 'codigo_bis' => $codigo_bis,
            'com_producto_id' => $com_producto_id, 'cantidad' => $cantidad,
            'valor_unitario' => $valor_unitario, 'descuento' => $descuento,
            'fc_factura_id' => $fc_factura_id);

        return $regisro_cfd_partida;
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

    private function get_isr(int $cat_sat_periodicidad_pago_nom_id, float|int $monto, string $fecha = ''){
        $filtro['cat_sat_periodicidad_pago_nom.id'] = $cat_sat_periodicidad_pago_nom_id;

        if($fecha === ''){
            $fecha = date('Y-m-d');
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

    private function filtro_especial_isr(float $monto, string $fecha = ''): array
    {
        if($fecha === ''){
            $fecha = date('Y-m-d');
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

    private function limpia_campos(array $registro, array $campos_limpiar): array
    {
        foreach ($campos_limpiar as $valor) {
            if (isset($registro[$valor])) {
                unset($registro[$valor]);
            }
        }
        return $registro;
    }

    public function registros_por_id(modelo $entidad, int $id): array|stdClass
    {
        $data = $entidad->registro(registro_id: $id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros', data: $data);
        }
        return $data;
    }

    private function genera_valor_campo(array $campos_asignar): string
    {
        return implode($campos_asignar);
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


}