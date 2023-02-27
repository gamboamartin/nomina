<?php
namespace gamboamartin\nomina\tests;
use base\orm\modelo_base;
use gamboamartin\cat_sat\models\cat_sat_isr;
use gamboamartin\cat_sat\models\cat_sat_metodo_pago;
use gamboamartin\cat_sat\models\cat_sat_moneda;
use gamboamartin\cat_sat\models\cat_sat_subsidio;
use gamboamartin\cat_sat\models\cat_sat_tipo_de_comprobante;
use gamboamartin\cat_sat\models\cat_sat_tipo_nomina;
use gamboamartin\comercial\models\com_producto;
use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\comercial\models\com_tipo_cambio;
use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\empleado\models\em_registro_patronal;
use gamboamartin\errores\errores;
use gamboamartin\im_registro_patronal\models\im_rcv;
use gamboamartin\nomina\models\nom_conf_empleado;
use gamboamartin\nomina\models\nom_conf_factura;
use gamboamartin\nomina\models\nom_conf_nomina;
use gamboamartin\nomina\models\nom_conf_percepcion;
use gamboamartin\nomina\models\nom_nomina;
use gamboamartin\nomina\models\nom_par_percepcion;
use gamboamartin\nomina\models\nom_percepcion;
use gamboamartin\nomina\models\nom_periodo;
use gamboamartin\nomina\models\nom_rel_empleado_sucursal;
use gamboamartin\im_registro_patronal\models\im_registro_patronal;
use gamboamartin\im_registro_patronal\models\im_uma;
use PDO;
use stdClass;

class base_test{

    public function alta_cat_sat_metodo_pago(PDO $link): array|\stdClass
    {


        $alta = (new \gamboamartin\cat_sat\tests\base_test())->alta_cat_sat_metodo_pago($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);
        }

        return $alta;
    }

    public function alta_cat_sat_moneda(PDO $link): array|\stdClass
    {


        $alta = (new \gamboamartin\cat_sat\tests\base_test())->alta_cat_sat_moneda($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);
        }

        return $alta;
    }

    public function alta_cat_sat_tipo_nomina(PDO $link, int $id = 1): array|\stdClass
    {

        $alta = (new \gamboamartin\cat_sat\tests\base_test())->alta_cat_sat_tipo_nomina(link: $link, id: $id);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_com_producto(PDO $link, int $id = 1): array|\stdClass
    {

        $alta = (new \gamboamartin\comercial\test\base_test())->alta_com_producto(link: $link, id: $id);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_com_sucursal(PDO $link, int $id = 1): array|\stdClass
    {
        $del = $this->del_em_rel_empleado_sucursal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $alta = (new \gamboamartin\comercial\test\base_test())->alta_com_sucursal(link: $link, id: $id);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_com_tipo_cambio(PDO $link): array|\stdClass
    {

        $alta = (new \gamboamartin\comercial\test\base_test())->alta_com_tipo_cambio(link: $link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_em_cuenta_bancaria(PDO $link, int $id = 1): array|\stdClass
    {

        $alta = (new \gamboamartin\empleado\test\base_test())->alta_em_cuenta_bancaria(link: $link, id: $id);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_em_empleado(PDO $link, string $codigo = '1', string $fecha_inicio_rel_laboral = '2020-01-01', int $id = 1,
                                     float $salario_diario = 180, float  $salario_diario_integrado = 180): array|\stdClass
    {

        $alta = (new \gamboamartin\empleado\test\base_test())->alta_em_empleado(link: $link,
            codigo: $codigo, fecha_inicio_rel_laboral: $fecha_inicio_rel_laboral, id: $id,
            salario_diario: $salario_diario, salario_diario_integrado: $salario_diario_integrado);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_fc_partida(PDO $link): array|\stdClass
    {

        $alta = (new \gamboamartin\facturacion\tests\base_test())->alta_fc_partida($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_fc_csd(PDO $link): array|\stdClass
    {

        $alta = (new \gamboamartin\facturacion\tests\base_test())->alta_fc_csd($link);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_im_registro_patronal(PDO $link, int $fc_csd_id = 1, int $id = 1): array|\stdClass
    {

        $alta = (new \gamboamartin\im_registro_patronal\test\base_test())->alta_im_registro_patronal(
            link: $link, id: $id,fc_csd_id: $fc_csd_id);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_im_uma(PDO $link, string $fecha_fin = '2020-12-31', string $fecha_inicio = '2020-01-01',
                                float $monto = 0.0): array|\stdClass
    {

        $alta = (new \gamboamartin\im_registro_patronal\test\base_test())->alta_im_uma(link: $link,
            fecha_fin: $fecha_fin, fecha_inicio: $fecha_inicio, monto: $monto);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }    
    public function alta_im_rcv(PDO $link, float $monto_inicial = 0, float $monto_final = 999999999,
                                float $factor = 0.0): array|\stdClass
    {

        $alta = (new \gamboamartin\im_registro_patronal\test\base_test())->alta_im_rcv(link: $link,
            monto_inicial: $monto_inicial, monto_final: $monto_final, factor: $factor);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);

        }

        return $alta;
    }

    public function alta_cat_sat_isr(PDO $link, int $cat_sat_periodicidad_pago_nom_id = 1, float $cuota_fija = 0,
                                     string $fecha_fin = '2020-12-31', string $fecha_inicio = '2020-01-01',
                                     float $limite_inferior= 0.01, float $limite_superior = 99999,
                                     float $porcentaje_excedente = 1.92): array|\stdClass
    {

        $alta = (new \gamboamartin\cat_sat\tests\base_test())->alta_cat_sat_isr(link: $link,
            cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id, cuota_fija: $cuota_fija,
            fecha_fin: $fecha_fin, fecha_inicio: $fecha_inicio, limite_inferior: $limite_inferior,
            limite_superior: $limite_superior, porcentaje_excedente: $porcentaje_excedente);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);
        }

        return $alta;
    }


    public function alta_cat_sat_subsidio(PDO $link, string $alias = '1', int $cat_sat_periodicidad_pago_nom_id = 1,
                                          string $codigo = '1', string $codigo_bis = '1', float $cuota_fija = 0,
                                          string $descripcion = '1', string $descripcion_select = '1',
                                          string $fecha_fin = '2020-12-31', string $fecha_inicio = '2020-01-01',
                                          int $id = 1, float $limite_inferior = 0.01, float $limite_superior = 99999,
                                          float $porcentaje_excedente = 1.92): array|\stdClass
    {

        $alta = (new \gamboamartin\cat_sat\tests\base_test())->alta_cat_sat_subsidio(link: $link,
            alias: $alias, cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id, codigo: $codigo,
            codigo_bis: $codigo_bis, cuota_fija: $cuota_fija, descripcion: $descripcion,
            descripcion_select: $descripcion_select, fecha_fin: $fecha_fin, fecha_inicio: $fecha_inicio, id: $id,
            limite_inferior: $limite_inferior, limite_superior: $limite_superior,
            porcentaje_excedente: $porcentaje_excedente);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta', $alta);
        }

        return $alta;
    }


    public function alta_nom_conf_empleado(PDO $link, int $cat_sat_tipo_nomina_id = 1, int $em_cuenta_bancaria_id = 1,
                                           int $nom_conf_factura_id = 1, int $nom_conf_nomina_id = 1): array|\stdClass
    {

        $existe = (new nom_conf_nomina($link))->existe_by_id(registro_id: $nom_conf_nomina_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = $this->alta_nom_conf_nomina(link: $link, cat_sat_tipo_nomina_id: $cat_sat_tipo_nomina_id,
                nom_conf_factura_id: $nom_conf_factura_id, id: $nom_conf_nomina_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new em_cuenta_bancaria($link))->existe_by_id(registro_id: $em_cuenta_bancaria_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = $this->alta_em_cuenta_bancaria(link: $link, id: $em_cuenta_bancaria_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }



        $nom_conf_empleado = array();
        $nom_conf_empleado['id'] = 1;
        $nom_conf_empleado['codigo'] = 1;
        $nom_conf_empleado['descripcion'] = 1;
        $nom_conf_empleado['em_cuenta_bancaria_id'] = $em_cuenta_bancaria_id;
        $nom_conf_empleado['nom_conf_nomina_id'] = $nom_conf_nomina_id;

        $alta = (new nom_conf_empleado($link))->alta_registro($nom_conf_empleado);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }

    public function alta_nom_conf_factura(PDO $link, int $cat_sat_metodo_pago_id = 1, int $cat_sat_moneda_id = 1,
                                          int $com_producto_id = 1, $com_tipo_cambio_id = 1, int $id = 1): array|\stdClass
    {

        $existe = (new com_producto($link))->existe_by_id(registro_id: $com_producto_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = (new base_test())->alta_com_producto(link: $link, id: $com_producto_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }



        $existe = (new cat_sat_metodo_pago($link))->existe_by_id(registro_id: $cat_sat_metodo_pago_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = (new base_test())->alta_cat_sat_metodo_pago($link);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new cat_sat_moneda($link))->existe_by_id(registro_id: $cat_sat_moneda_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = (new base_test())->alta_cat_sat_moneda($link);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new com_tipo_cambio($link))->existe_by_id(registro_id: $com_tipo_cambio_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = (new base_test())->alta_com_tipo_cambio($link);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $registro = array();
        $registro['id'] = $id;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['cat_sat_forma_pago_id'] = 1;
        $registro['cat_sat_metodo_pago_id'] = $cat_sat_metodo_pago_id;
        $registro['com_tipo_cambio_id'] = 1;
        $registro['cat_sat_moneda_id'] = $cat_sat_moneda_id;
        $registro['cat_sat_uso_cfdi_id'] = 1;
        $registro['cat_sat_tipo_de_comprobante_id'] = 1;
        $registro['com_producto_id'] = 1;


        $alta = (new nom_conf_factura($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }

    public function alta_nom_conf_nomina(PDO $link, int $cat_sat_tipo_nomina_id = 1, int $nom_conf_factura_id = 1,
                                         int $id = 1): array|\stdClass
    {

        $existe = (new cat_sat_tipo_nomina($link))->existe_by_id(registro_id: $cat_sat_tipo_nomina_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = (new base_test())->alta_cat_sat_tipo_nomina(link: $link, id: $cat_sat_tipo_nomina_id);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new nom_conf_factura($link))->existe_by_id(registro_id: $nom_conf_factura_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = (new base_test())->alta_nom_conf_factura(link: $link, id: $nom_conf_factura_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);
            }
        }




        $nom_conf_empleado = array();
        $nom_conf_empleado['id'] = $id;
        $nom_conf_empleado['codigo'] = 1;
        $nom_conf_empleado['descripcion'] = 1;
        $nom_conf_empleado['nom_conf_factura_id'] = $nom_conf_factura_id;
        $nom_conf_empleado['descripcion_select'] = 1;
        $nom_conf_empleado['cat_sat_periodicidad_pago_nom_id'] = 1;
        $nom_conf_empleado['cat_sat_tipo_nomina_id'] = 1;
        $nom_conf_empleado['aplica_septimo_dia'] = 'inactivo';
        $nom_conf_empleado['aplica_prima_dominical'] = 'inactivo';


        $alta = (new nom_conf_nomina($link))->alta_registro($nom_conf_empleado);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }

    public function alta_nom_conf_percepcion(PDO $link, int $id = 1, int $nom_conf_nomina_id = 1,
                                             string $nom_percepcion_aplica_septimo_dia = 'inactivo',
                                             int $nom_percepcion_id = 1): array|\stdClass
    {

        $existe = (new nom_conf_nomina($link))->existe_by_id(registro_id: $nom_conf_nomina_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = (new base_test())->alta_nom_conf_nomina(link: $link, id: $nom_conf_nomina_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);
            }
        }

        $filtro = array();
        $filtro['nom_percepcion.aplica_septimo_dia'] = $nom_percepcion_aplica_septimo_dia;
        $filtro['nom_percepcion.id'] = $nom_percepcion_id;
        $existe = (new nom_percepcion($link))->existe(filtro: $filtro);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }

        if(!$existe) {
            $alta = (new base_test())->alta_nom_percepcion(link: $link,
                aplica_septimo_dia: $nom_percepcion_aplica_septimo_dia, id: $nom_percepcion_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);
            }
        }

        $registro = array();
        $registro['id'] = $id;
        $registro['codigo'] = 1;
        $registro['descripcion'] = 1;
        $registro['nom_conf_nomina_id'] = $nom_conf_nomina_id;
        $registro['nom_percepcion_id'] = $nom_percepcion_id;

        $alta = (new nom_conf_percepcion($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }

    public function alta_nom_nomina(PDO $link, float $cat_sat_isr_cuota_fija =0,
                                    float $cat_sat_isr_limite_inferior = 0.01, $cat_sat_isr_porcentaje_excedente = 1.92,
                                    float $cat_sat_subsidio_porcentaje_excedente = 1.92,
                                    string $em_empleado_codigo = '1', int $em_empleado_id = 1, int $im_uma_id = 1,
                                    int $nom_conf_empleado_id = 1, string $nom_percepcion_aplica_subsidio = 'activo',
                                    string $nom_percepcion_codigo = '1', string $nom_percepcion_codigo_bis = '1',
                                    string $nom_percepcion_descripcion = '1', int $nom_percepcion_id = 1,
                                    int $nom_periodo_id = 1, int $nom_rel_empleado_sucursal_id = 1,
                                    float $salario_diario = 250, float $salario_diario_integrado = 250,
                                    int $cat_sat_tipo_de_comprobante_id = 1): array|stdClass
    {
        $existe = (new cat_sat_tipo_de_comprobante($link))->existe_by_id(registro_id: 1);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_cat_sat_tipo_de_comprobante(link: $link,
                id: $cat_sat_tipo_de_comprobante_id);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }


        $existe = (new em_empleado($link))->existe_by_id(registro_id: $em_empleado_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_em_empleado(link: $link, codigo: $em_empleado_codigo, id: $em_empleado_id,
                salario_diario: $salario_diario, salario_diario_integrado: $salario_diario_integrado);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new nom_conf_empleado($link))->existe_by_id(registro_id: $nom_conf_empleado_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_nom_conf_empleado($link);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new nom_rel_empleado_sucursal($link))->existe_by_id(registro_id: $nom_rel_empleado_sucursal_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_nom_rel_empleado_sucursal(link:$link, id: $nom_rel_empleado_sucursal_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new nom_periodo($link))->existe_by_id(registro_id: $nom_periodo_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_nom_periodo(link: $link, id: $nom_periodo_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }



        $existe = (new im_uma($link))->existe_by_id(registro_id: $im_uma_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_im_uma(link: $link, monto: 100);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new im_rcv($link))->existe_by_id(registro_id: $im_uma_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_im_rcv(link: $link);
            if (errores::$error) {
                return (new errores())->error('Error al dar de rcv', $alta);

            }
        }

        $filtro = array();
        $filtro['nom_percepcion.aplica_subsidio'] = $nom_percepcion_aplica_subsidio;
        $filtro['nom_percepcion.id'] = $nom_percepcion_id;
        $filtro['nom_percepcion.descripcion'] = $nom_percepcion_descripcion;
        $existe = (new nom_percepcion($link))->existe(filtro:$filtro);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_nom_percepcion(link: $link, aplica_subsidio: $nom_percepcion_aplica_subsidio,
                codigo: $nom_percepcion_codigo, codigo_bis: $nom_percepcion_codigo_bis,
                descripcion: $nom_percepcion_descripcion, id: $nom_percepcion_id);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }


        $existe = (new cat_sat_isr($link))->existe_by_id(registro_id: 1);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_cat_sat_isr(link: $link, cuota_fija: $cat_sat_isr_cuota_fija,
                limite_inferior: $cat_sat_isr_limite_inferior, porcentaje_excedente: $cat_sat_isr_porcentaje_excedente);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new cat_sat_subsidio($link))->existe_by_id(registro_id: 1);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_cat_sat_subsidio(link: $link, porcentaje_excedente: $cat_sat_subsidio_porcentaje_excedente);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $registro = array();
        $registro['id'] = $nom_periodo_id;
        $registro['im_registro_patronal_id'] = 1;
        $registro['em_empleado_id'] = $em_empleado_id;
        $registro['folio'] = 1;
        $registro['fecha'] = 1;
        $registro['cat_sat_periodicidad_pago_nom_id'] = 1;
        $registro['em_cuenta_bancaria_id'] = 1;
        $registro['fecha_inicial_pago'] = '2020-01-01';
        $registro['fecha_final_pago'] = '2020-01-01';
        $registro['fecha_pago'] = '2020-01-01';
        $registro['num_dias_pagados'] = '1';
        $registro['descuento'] = '0';
        $registro['nom_periodo_id'] = 1;
        $registro['nom_conf_empleado_id'] = $nom_conf_empleado_id;
        $registro['cat_sat_tipo_jornada_nom_id'] = 1;
        $registro['dp_calle_pertenece_id'] = 1;
        $registro['cat_sat_tipo_nomina_id'] = 1;
        $registro['em_registro_patronal_id'] = 1;


        $alta = (new nom_nomina($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);
        }
        return $alta;
    }

    public function alta_nom_percepcion(PDO $link, string $aplica_septimo_dia = 'inactivo',
                                        string $aplica_subsidio = 'inactivo', int $cat_sat_tipo_percepcion_nom_id = 1,
                                        string $codigo = '1', string $codigo_bis = '1', string $descripcion = '1',
                                        int $id = 1): array|\stdClass
    {


        $registro = array();
        $registro['id'] = $id;
        $registro['codigo'] = $codigo;
        $registro['codigo_bis'] = $codigo_bis;
        $registro['descripcion'] = $descripcion;
        $registro['aplica_septimo_dia'] = $aplica_septimo_dia;
        $registro['aplica_subsidio'] = $aplica_subsidio;
        $registro['cat_sat_tipo_percepcion_nom_id'] = $cat_sat_tipo_percepcion_nom_id;


        $alta = (new nom_percepcion($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }
    public function alta_nom_par_percepcion(PDO $link, string $codigo = '1', string $codigo_bis = '1', string $descripcion = '1',
                                        int $id = 1,int $nom_nomina_id = 1,int $nom_percepcion_id = 1): array|\stdClass
    {

        $existe = (new nom_nomina($link))->existe_by_id(registro_id: $nom_nomina_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_nom_nomina(link: $link);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new nom_percepcion($link))->existe_by_id(registro_id: $nom_percepcion_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_nom_percepcion(link: $link);
            if (errores::$error) {
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $registro = array();
        $registro['id'] = $id;
        $registro['codigo'] = $codigo;
        $registro['codigo_bis'] = $codigo_bis;
        $registro['descripcion'] = $descripcion;
        $registro['nom_nomina_id'] = $nom_nomina_id;
        $registro['nom_percepcion_id'] = $nom_percepcion_id;

        $alta = (new nom_par_percepcion($link))->alta_registro($registro);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar', data: $alta);

        }
        return $alta;
    }

    public function alta_nom_periodo(PDO $link, int $em_registro_patronal_id = 1, int $id = 1, int $im_registro_patronal_id = 1): array|\stdClass
    {
        $existe = (new im_registro_patronal($link))->existe_by_id(registro_id: $im_registro_patronal_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_im_registro_patronal(link: $link, id: $im_registro_patronal_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }

        $existe = (new em_registro_patronal($link))->existe_by_id(registro_id: $em_registro_patronal_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = $this->alta_im_registro_patronal(link: $link, id: $im_registro_patronal_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }



        $nom_periodo = array();
        $nom_periodo['id'] = $id;
        $nom_periodo['codigo'] = 1;
        $nom_periodo['descripcion'] = 1;
        $nom_periodo['cat_sat_periodicidad_pago_nom_id'] = 1;
        $nom_periodo['im_registro_patronal_id'] = $im_registro_patronal_id;
        $nom_periodo['nom_tipo_periodo_id'] = 1;
        $nom_periodo['em_registro_patronal_id'] = 1;


        $alta = (new nom_periodo($link))->alta_registro($nom_periodo);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar periodo', data: $alta);

        }
        return $alta;
    }

    public function alta_cat_sat_tipo_de_comprobante(PDO $link, int $id = 1): array|\stdClass
    {
        $cat_sat_tipo_de_comprobante = array();
        $cat_sat_tipo_de_comprobante['id'] = $id;
        $cat_sat_tipo_de_comprobante['codigo'] = 1;
        $cat_sat_tipo_de_comprobante['descripcion'] = 1;
        $cat_sat_tipo_de_comprobante['descripcion_select'] = 1;
        $cat_sat_tipo_de_comprobante['alias'] = 1;
        $cat_sat_tipo_de_comprobante['codigo_bis'] = 1;


        $alta = (new cat_sat_tipo_de_comprobante($link))->alta_registro($cat_sat_tipo_de_comprobante);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al insertar periodo', data: $alta);

        }
        return $alta;
    }


    public function alta_nom_rel_empleado_sucursal(PDO $link, int $com_sucursal_id = 1, int $id = 1): array|\stdClass
    {

        $existe = (new com_sucursal($link))->existe_by_id(registro_id: $com_sucursal_id);
        if(errores::$error){
            return (new errores())->error('Error al verificar si existe', $existe);

        }
        if(!$existe) {
            $alta = (new base_test())->alta_com_sucursal(link: $link, id: $com_sucursal_id);
            if(errores::$error){
                return (new errores())->error('Error al dar de alta', $alta);

            }
        }



        $nom_rel_empleado_sucursal = array();
        $nom_rel_empleado_sucursal['id'] = $id;
        $nom_rel_empleado_sucursal['codigo'] = 1;
        $nom_rel_empleado_sucursal['descripcion'] = 1;
        $nom_rel_empleado_sucursal['em_empleado_id'] = 1;
        $nom_rel_empleado_sucursal['com_sucursal_id'] = $com_sucursal_id;


        $alta = (new nom_rel_empleado_sucursal($link))->alta_registro($nom_rel_empleado_sucursal);
        if(errores::$error){
            return (new errores())->error('Error al dar de alta ', $alta);

        }
        return $alta;
    }


    public function del(PDO $link, string $name_model): array
    {

        $model = (new modelo_base($link))->genera_modelo(modelo: $name_model);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al genera modelo '.$name_model, data: $model);
        }
        $del = $model->elimina_todo();
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al eliminar '.$name_model, data: $del);
        }

        return $del;
    }

    public function del_cat_sat_metodo_pago(PDO $link): array
    {

        $del = $this->del_com_cliente($link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al eliminar ', data: $del);
        }

        $del = (new \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_metodo_pago($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_cat_sat_moneda(PDO $link): array
    {
        $del = $this->del_com_tipo_cambio($link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al eliminar ', data: $del);
        }
        $del = $this->del_com_cliente($link);
        if(errores::$error){
            return (new errores())->error(mensaje: 'Error al eliminar ', data: $del);
        }


        $del = (new \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_moneda($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_cat_sat_tipo_de_comprobante(PDO $link): array
    {
        $del = (new \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_tipo_de_comprobante($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        return $del;
    }

    public function del_cat_sat_tipo_nomina(PDO $link): array
    {

        $del = (new base_test())->del_nom_conf_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        $del = (new base_test())->del_nom_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_tipo_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_com_cliente(PDO $link): array
    {

        $del = (new base_test())->del_nom_rel_empleado_sucursal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        $del = (new base_test())->del_fc_factura($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\comercial\test\base_test())->del_com_cliente($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_com_producto(PDO $link): array
    {

        $del = (new base_test())->del_nom_conf_factura($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\comercial\test\base_test())->del_com_producto($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_com_sucursal(PDO $link): array
    {

        $del = $this->del_fc_factura($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        $del = (new \gamboamartin\comercial\test\base_test())->del_com_sucursal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_com_tipo_cambio(PDO $link): array
    {

        $del = (new base_test())->del_nom_conf_factura($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new base_test())->del_fc_factura($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\comercial\test\base_test())->del_com_tipo_cambio($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_em_cuenta_bancaria(PDO $link): array
    {

        $del = (new base_test())->del_nom_conf_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new base_test())->del_nom_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\empleado\test\base_test())->del_em_cuenta_bancaria($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_em_empleado(PDO $link): array
    {

        $del = (new base_test())->del_em_cuenta_bancaria($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new base_test())->del_nom_rel_empleado_sucursal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new base_test())->del_nom_incidencia($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\empleado\test\base_test())->del_em_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_em_registro_patronal(PDO $link): array
    {

        $del = (new \gamboamartin\empleado\test\base_test())->del_em_registro_patronal(link: $link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_em_rel_empleado_sucursal(PDO $link): array
    {

        $del = (new \gamboamartin\empleado\test\base_test())->del_em_rel_empleado_sucursal(link: $link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        return $del;
    }

    public function del_fc_csd(PDO $link): array
    {

        $del = (new base_test())->del_im_registro_patronal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        $del = (new base_test())->del_em_registro_patronal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\facturacion\tests\base_test())->del_fc_csd($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }

    public function del_fc_factura(PDO $link): array
    {

        $del = (new base_test())->del_nom_nomina($link);
        if(errores::$error){
            $error = (new errores())->error('Error al eliminar', $del);
            print_r($error);
            exit;
        }

        $del = (new \gamboamartin\facturacion\tests\base_test())->del_fc_factura($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }

    public function del_im_registro_patronal(PDO $link): array
    {

        $del = (new base_test())->del_nom_periodo($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new base_test())->del_em_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }


        $del = (new \gamboamartin\im_registro_patronal\test\base_test())->del_im_registro_patronal($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }
    public function del_im_uma(PDO $link): array
    {

        $del = (new \gamboamartin\im_registro_patronal\test\base_test())->del_im_uma($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }

    public function del_cat_sat_isr(PDO $link): array
    {

        $del = (new  \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_isr($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }

    public function del_cat_sat_subsidio(PDO $link): array
    {

        $del = (new  \gamboamartin\cat_sat\tests\base_test())->del_cat_sat_subsidio($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }


    public function del_nom_concepto_imss(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_concepto_imss');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_conf_empleado(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_conf_empleado');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_conf_factura(PDO $link): array
    {

        $del = (new base_test())->del_nom_conf_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_conf_factura');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_conf_nomina(PDO $link): array
    {


        $del = (new base_test())->del_nom_conf_percepcion($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = (new base_test())->del_nom_conf_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_conf_nomina');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_conf_percepcion(PDO $link): array
    {

        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_conf_percepcion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_data_subsidio(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_data_subsidio');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_nomina(PDO $link): array
    {


        $del = $this->del_nom_par_deduccion($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_par_otro_pago($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_par_percepcion($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_concepto_imss($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }


        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_nomina');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_par_deduccion(PDO $link): array
    {
        $del = $this->del_nom_data_subsidio($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del_nom_rel_deduccion_abono($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_par_deduccion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_par_otro_pago(PDO $link): array
    {
        $del = $this->del_nom_data_subsidio($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }


        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_par_otro_pago');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_par_percepcion(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_par_percepcion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_percepcion(PDO $link): array
    {
        $del = $this->del_nom_par_percepcion($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_percepcion');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_periodo(PDO $link): array
    {
        $del = $this->del_nom_nomina($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }

        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_periodo');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_rel_deduccion_abono(PDO $link): array
    {

        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_rel_deduccion_abono');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_nom_rel_empleado_sucursal(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_rel_empleado_sucursal');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }

    public function del_org_clasificacion_dep(PDO $link): array
    {
        $del = (new base_test())->del_em_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\organigrama\tests\base_test())->del_org_clasificacion_dep($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }

    public function del_nom_incidencia(PDO $link): array
    {
        $del = $this->del($link, 'gamboamartin\\nomina\\models\\nom_incidencia');
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);
        }
        return $del;
    }


    public function del_org_empresa(PDO $link): array
    {

        $del = (new base_test())->del_fc_csd($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new base_test())->del_em_empleado($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }

        $del = (new \gamboamartin\organigrama\tests\base_test())->del_org_empresa($link);
        if(errores::$error){
            return (new errores())->error('Error al eliminar', $del);

        }
        return $del;
    }


}
