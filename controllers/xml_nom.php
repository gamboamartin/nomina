<?php
namespace gamboamartin\nomina\controllers;
use gamboamartin\comercial\models\com_sucursal;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\validacion\validacion;
use gamboamartin\xml_cfdi_4\cfdis;
use models\calcula_nomina;
use models\nom_data_subsidio;
use models\nom_nomina;
use PDO;
use stdClass;

class xml_nom{
    private errores $error;
    private validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * Genera un comprobante para cfdi
     * @param int $fc_factura_id Factura a verificar
     * @param PDO $link Conexion a la base de datos
     * @return array|stdClass
     * @version 0.454.25
     */
    private function comprobante(int $fc_factura_id, PDO $link): array|stdClass
    {

        if($fc_factura_id <= 0){
            return $this->error->error(mensaje: 'Error fc_factura_id debe ser mayor a 0', data: $fc_factura_id);
        }
        $fc_factura = (new fc_factura($link))->registro(registro_id:$fc_factura_id, retorno_obj: true );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener factura', data: $fc_factura);
        }

        $comprobante = $this->data_comprobante(fc_factura: $fc_factura, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear comprobante', data: $comprobante);
        }
        return $comprobante;
    }

    private function data_cfdi_base(int $fc_factura_id, PDO $link): array|stdClass
    {
        $fc_factura = (new fc_factura($link))->registro(
            registro_id:$fc_factura_id, retorno_obj: true );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener factura', data: $fc_factura);
        }


        $comprobante = $this->comprobante(fc_factura_id: $fc_factura_id, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear comprobante', data: $comprobante);
        }


        $emisor = $this->emisor(fc_factura_id: $fc_factura_id, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear emisor', data: $emisor);
        }

        $receptor = $this->receptor(com_sucursal_id:$fc_factura->com_sucursal_id ,
            fc_factura_id: $fc_factura->fc_factura_id, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear receptor', data: $receptor);
        }

        $data = new stdClass();
        $data->fc_factura = $fc_factura;
        $data->comprobante = $comprobante;
        $data->emisor = $emisor;
        $data->receptor = $receptor;

        return $data;
    }

    private function data_cfdi_base_nomina(stdClass $data_cfdi, PDO $link, stdClass $nom_nomina): array|stdClass
    {
        $deducciones = (new nom_nomina($link))->deducciones(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $deducciones);
        }

        $nomina = $this->nomina_header(emisor: $data_cfdi->emisor, link: $link,nom_nomina:  $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina base', data: $nomina);
        }

        $nomina = $this->percepciones(link: $link,nomina:  $nomina,nom_nomina_id:  $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar percepciones', data: $nomina);
        }

        $nomina = $this->deducciones(link: $link,nomina:  $nomina,nom_nomina_id:  $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar percepciones', data: $nomina);
        }

        $nomina = $this->otros_pagos(link: $link,nomina:  $nomina,nom_nomina_id:  $nom_nomina->nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al asignar otros_pagos', data: $nomina);
        }

        return $nomina;
    }



    /**
     * Maqueta el objeto de un comprobante para cfdi
     * @param stdClass $fc_factura Factura
     * @param PDO $link Conexion a la base de datos
     * @return array|stdClass
     * @version 0.394.21
     */
    private function data_comprobante(stdClass $fc_factura, PDO $link): array|stdClass
    {

        $keys = array('dp_cp_descripcion','fc_factura_folio','fc_factura_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys ,registro:  $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $keys = array('fc_factura_id');
        $valida = $this->validacion->valida_ids(keys:$keys ,registro:  $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $comprobante = new stdClass();
        $comprobante->lugar_expedicion = $fc_factura->dp_cp_descripcion;
        $comprobante->folio = $fc_factura->fc_factura_folio;

        $total = (new fc_factura($link))->total(fc_factura_id:$fc_factura->fc_factura_id );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total', data: $total);
        }

        $comprobante->total = $total;

        $sub_total = (new fc_factura($link))->sub_total(fc_factura_id:$fc_factura->fc_factura_id );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sub_total', data: $sub_total);
        }

        $comprobante->sub_total = $sub_total;

        $descuento = (
            new fc_factura($link))->get_factura_descuento(fc_factura_id:$fc_factura->fc_factura_id );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener descuento', data: $descuento);
        }

        $comprobante->descuento = $descuento;
        return $comprobante;
    }

    private function data_deduccion(stdClass $nomina, array $deduccion): stdClass
    {

        $data_deduccion = new stdClass();

        $data_deduccion->tipo_deduccion = $deduccion['cat_sat_tipo_deduccion_nom_codigo'];
        $data_deduccion->clave = $deduccion['nom_deduccion_codigo'];
        $data_deduccion->concepto = $deduccion['nom_par_deduccion_descripcion'];
        $data_deduccion->importe = round(round($deduccion['nom_par_deduccion_importe_gravado'],2) + round($deduccion['nom_par_deduccion_importe_exento'],2),2);

        $nomina->deducciones->deduccion[] = $data_deduccion;


        return $nomina;

    }

    private function data_deducciones(stdClass $nomina, array $deducciones): array|stdClass
    {
        foreach ($deducciones as $deduccion){
            $nomina = $this->data_deduccion(nomina: $nomina, deduccion: $deduccion);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar deduccion', data: $nomina);
            }
        }
        return $nomina;
    }

    /**
     * Genera los datos de emision de nomina
     * @param stdClass $fc_factura Factura
     * @return stdClass|array
     * @version 0.456.25
     */
    private function data_emisor(stdClass $fc_factura): stdClass|array
    {
        $keys = array('org_empresa_rfc','org_empresa_razon_social','cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $keys = array('org_empresa_rfc');
        $valida = $this->validacion->valida_rfcs(keys:$keys, registro: $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $keys = array('cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_codigos_int_0_3_numbers(keys:$keys, registro: $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar fc_factura', data: $valida);
        }

        $emisor = new stdClass();
        $emisor->rfc = $fc_factura->org_empresa_rfc;
        $emisor->nombre = $fc_factura->org_empresa_razon_social;
        $emisor->regimen_fiscal = $fc_factura->cat_sat_regimen_fiscal_codigo;

        return $emisor;
    }

    private function data_otro_pago(PDO $link, stdClass $nomina, array $otro_pago): stdClass|array
    {
        $keys = array('cat_sat_tipo_otro_pago_nom_codigo','nom_otro_pago_codigo','nom_par_otro_pago_descripcion',
            'nom_par_otro_pago_importe_gravado','nom_par_otro_pago_importe_exento','nom_otro_pago_es_subsidio',
            'nom_par_otro_pago_id');

        $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $otro_pago);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar otro pago',data:  $valida);
        }

        $keys = array('nom_par_otro_pago_importe_gravado','nom_par_otro_pago_importe_exento');

        $valida = $this->validacion->valida_double_mayores_igual_0(keys:$keys, registro: $otro_pago);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar otro pago',data:  $valida);
        }

        $data_otro_pago = new stdClass();
        $es_subsidio = false;
        $subsidio_causado = 0;
        if($otro_pago['nom_otro_pago_es_subsidio'] ==='activo'){
            if((int)$otro_pago['nom_par_otro_pago_id'] > 0) {
                $nom_data_subsidios = (new nom_data_subsidio($link))->get_data_by_otro_pago(nom_par_otro_pago_id: $otro_pago['nom_par_otro_pago_id']);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener nom_data_subsidios', data: $nom_data_subsidios);
                }

                foreach ($nom_data_subsidios as $nom_data_subsidio) {
                    $subsidio_causado += round($nom_data_subsidio['nom_data_subsidio_monto_subsidio_bruto'], 2);
                }
            }
            $subsidio_causado = round($subsidio_causado,2);
            $es_subsidio = true;
        }



        $data_otro_pago->tipo_otro_pago = $otro_pago['cat_sat_tipo_otro_pago_nom_codigo'];
        $data_otro_pago->clave = $otro_pago['nom_otro_pago_codigo'];
        $data_otro_pago->concepto = $otro_pago['nom_par_otro_pago_descripcion'];
        $data_otro_pago->importe = round(round($otro_pago['nom_par_otro_pago_importe_gravado'],2) + round($otro_pago['nom_par_otro_pago_importe_exento'],2),2);
        $data_otro_pago->es_subsidio = $es_subsidio;
        $data_otro_pago->subsidio_causado = $subsidio_causado;
        $nomina->otros_pagos->otro_pago[] = $data_otro_pago;

        return $nomina;

    }

    private function data_otros_pagos(PDO $link, stdClass $nomina, array $otros_pagos): array|stdClass
    {
        foreach ($otros_pagos as $otro_pago){
            $keys = array('cat_sat_tipo_otro_pago_nom_codigo','nom_otro_pago_codigo','nom_par_otro_pago_descripcion',
                'nom_par_otro_pago_importe_gravado','nom_par_otro_pago_importe_exento','nom_otro_pago_es_subsidio',
                'nom_par_otro_pago_id');

            $valida = $this->validacion->valida_existencia_keys(keys:$keys, registro: $otro_pago);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar otro pago',data:  $valida);
            }

            $nomina = $this->data_otro_pago(link: $link,nomina: $nomina, otro_pago: $otro_pago);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar percepcion', data: $nomina);
            }
        }
        return $nomina;
    }

    private function data_percepcion(stdClass $nomina, array $percepcion): stdClass
    {
        $data_percepcion = new stdClass();
        $data_percepcion->tipo_percepcion = $percepcion['cat_sat_tipo_percepcion_nom_codigo'];
        $data_percepcion->clave = $percepcion['nom_percepcion_codigo'];
        $data_percepcion->concepto = $percepcion['nom_par_percepcion_descripcion'];
        $data_percepcion->importe_gravado = $percepcion['nom_par_percepcion_importe_gravado'];
        $data_percepcion->importe_exento = $percepcion['nom_par_percepcion_importe_exento'];
        $nomina->percepciones->percepcion[] = $data_percepcion;

        return $nomina;

    }

    private function data_percepciones(stdClass $nomina, array $percepciones): array|stdClass
    {
        foreach ($percepciones as $percepcion){
            $nomina = $this->data_percepcion(nomina: $nomina, percepcion: $percepcion);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al asignar percepcion', data: $nomina);
            }
        }
        return $nomina;
    }

    /**
     * Asigna los datos para receptor
     * @param stdClass $com_sucursal Datos de receptor
     * @param stdClass $fc_factura Datos de factura
     * @return stdClass|array
     * @version 0.460.25
     */
    PUBLIC function data_receptor(stdClass $com_sucursal, stdClass $fc_factura): stdClass|array
    {

        $keys = array('com_cliente_rfc','com_cliente_razon_social');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar factura', data: $valida);
        }

        $keys = array('dp_cp_descripcion','cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys,registro:  $com_sucursal);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar com_sucursal', data: $valida);
        }

        $keys = array('com_cliente_rfc');
        $valida = $this->validacion->valida_rfcs(keys:$keys,registro:  $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar factura', data: $valida);
        }

        $keys = array('dp_cp_descripcion');
        $valida = $this->validacion->valida_codigos_int_0_5_numbers(keys:$keys,registro:  $com_sucursal);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar com_sucursal', data: $valida);
        }

        $keys = array('cat_sat_regimen_fiscal_codigo');
        $valida = $this->validacion->valida_codigos_int_0_3_numbers(keys:$keys,registro:  $com_sucursal);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar com_sucursal', data: $valida);
        }

        $receptor = new stdClass();
        $receptor->rfc = $fc_factura->com_cliente_rfc;
        $receptor->nombre = $fc_factura->com_cliente_razon_social;
        $receptor->domicilio_fiscal_receptor = $com_sucursal->dp_cp_descripcion;
        $receptor->regimen_fiscal_receptor = $com_sucursal->cat_sat_regimen_fiscal_codigo;

        return $receptor;
    }

    private function deducciones(PDO $link, stdClass $nomina, int $nom_nomina_id): array|stdClass
    {

        $deducciones = (new nom_nomina($link))->deducciones(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $deducciones);
        }


        $nomina = $this->deducciones_header(link:$link, nomina: $nomina,nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $nomina);
        }


        $nomina = $this->data_deducciones(nomina: $nomina, deducciones: $deducciones);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar deducciones', data: $nomina);
        }
        return $nomina;
    }

    private function deducciones_header(PDO $link, stdClass $nomina, int $nom_nomina_id): array|stdClass
    {

        $nomina->deducciones = new stdClass();

        $nomina->deducciones->total_otras_deducciones = (new nom_nomina($link))->total_otras_deducciones_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sueldos', data: $nomina->deducciones->total_otras_deducciones);
        }


        $nomina->deducciones->total_impuestos_retenidos = (new nom_nomina($link))->total_impuestos_retenidos_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sueldos', data: $nomina->deducciones->total_impuestos_retenidos);
        }

        return $nomina;
    }

    /**
     * @param int $fc_factura_id Identificador de factura
     * @param PDO $link Conexion a BD
     * @return array|stdClass
     * @version 0.458.25
     */
    private function emisor(int $fc_factura_id, PDO $link): array|stdClass
    {
        if($fc_factura_id<=0){
            return $this->error->error(mensaje: 'Error fc_factura_id debe ser mayor a 0', data: $fc_factura_id);
        }
        $fc_factura = (new fc_factura($link))->registro(registro_id:$fc_factura_id, retorno_obj: true );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener factura', data: $fc_factura);
        }

        $emisor = $this->data_emisor(fc_factura: $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear emisor', data: $emisor);
        }

        return $emisor;
    }


    private function nomina_base(PDO $link, stdClass $nom_nomina): array|stdClass
    {
        $nomina = new stdClass();

        $nomina->tipo_nomina = $nom_nomina->cat_sat_tipo_nomina_codigo;
        $nomina->fecha_pago = $nom_nomina->nom_nomina_fecha_pago;
        $nomina->fecha_inicial_pago = $nom_nomina->nom_nomina_fecha_inicial_pago;
        $nomina->fecha_final_pago = $nom_nomina->nom_nomina_fecha_final_pago;
        $nomina->num_dias_pagados = $nom_nomina->nom_nomina_num_dias_pagados;
        $nomina->total_percepciones = (new nom_nomina(link: $link))->total_percepciones_monto(
            nom_nomina_id: $nom_nomina->nom_nomina_id);

        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener total percepciones xml', data: $nomina->total_percepciones);
        }

        $nomina->total_deducciones = (new nom_nomina(link: $link))->total_deducciones_monto(
            nom_nomina_id: $nom_nomina->nom_nomina_id);

        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener total deducciones xml',
                data: $nomina->total_deducciones);
        }

        return $nomina;
    }

    private function nomina_emisor(stdClass $emisor, stdClass $nom_nomina, stdClass $nomina): stdClass
    {
        $nomina->emisor = new stdClass();
        $nomina->emisor->registro_patronal = $nom_nomina->im_registro_patronal_descripcion;
        $nomina->emisor->rfc_patron_origen =  $emisor->rfc;
        return $nomina;
    }

    private function nomina_header(stdClass $emisor, PDO $link, stdClass $nom_nomina): array|stdClass
    {
        $nomina = $this->nomina_base(link: $link, nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina base', data: $nomina);
        }

        $nomina = $this->nomina_emisor(emisor: $emisor,nom_nomina: $nom_nomina, nomina: $nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina emisor', data: $nomina);
        }

        $nomina = $this->nomina_receptor(nom_nomina: $nom_nomina, nomina: $nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina receptor', data: $nomina);
        }
        return $nomina;
    }
    
    private function nomina_receptor(stdClass $nom_nomina, stdClass $nomina): array|stdClass
    {
        $nomina->receptor = new stdClass();
        $nomina->receptor->curp = $nom_nomina->em_empleado_curp;
        $nomina->receptor->num_seguridad_social = $nom_nomina->em_empleado_nss;
        $nomina->receptor->fecha_inicio_rel_laboral = $nom_nomina->em_empleado_fecha_inicio_rel_laboral;

        $nomina->receptor->antiguedad = (new calcula_nomina())->antiguedad_empleado(
            fecha_final_pago: $nom_nomina->nom_nomina_fecha_final_pago,
            fecha_inicio_rel_laboral:  $nom_nomina->em_empleado_fecha_inicio_rel_laboral);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener antiguedad', data: $nomina->receptor->antiguedad);
        }


        $nomina->receptor->tipo_contrato = $nom_nomina->cat_sat_tipo_contrato_nom_codigo;
        $nomina->receptor->tipo_jornada = $nom_nomina->cat_sat_tipo_jornada_nom_codigo;
        $nomina->receptor->tipo_regimen = $nom_nomina->cat_sat_tipo_regimen_nom_codigo;
        $nomina->receptor->num_empleado = $nom_nomina->em_empleado_codigo;
        $nomina->receptor->departamento = $nom_nomina->org_departamento_descripcion;
        $nomina->receptor->puesto = $nom_nomina->org_puesto_descripcion;
        $nomina->receptor->riesgo_puesto = $nom_nomina->im_clase_riesgo_codigo;
        $nomina->receptor->periodicidad_pago = $nom_nomina->cat_sat_periodicidad_pago_nom_codigo;
        $nomina->receptor->cuenta_bancaria = $nom_nomina->em_cuenta_bancaria_clabe;
        $nomina->receptor->banco = $nom_nomina->bn_banco_codigo;
        $nomina->receptor->salario_base_cot_apor = $nom_nomina->em_empleado_salario_diario_integrado;
        $nomina->receptor->salario_diario_integrado = $nom_nomina->em_empleado_salario_diario_integrado;
        $nomina->receptor->clave_ent_fed = $nom_nomina->dp_estado_codigo;

        return $nomina;
    }

    private function otros_pagos(PDO $link, stdClass $nomina, int $nom_nomina_id): array|stdClass
    {

        $otros_pagos = (new nom_nomina($link))->otros_pagos(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otros_pagos', data: $otros_pagos);
        }

        if(count($otros_pagos) === 0){
            $otros_pagos[0] = array();
            $otros_pagos[0]['cat_sat_tipo_otro_pago_nom_codigo'] ='002';
            $otros_pagos[0]['nom_otro_pago_codigo'] ='002';
            $otros_pagos[0]['nom_par_otro_pago_descripcion'] ='SUB EFP';
            $otros_pagos[0]['nom_par_otro_pago_importe_gravado'] ='0';
            $otros_pagos[0]['nom_par_otro_pago_importe_exento'] ='0';
            $otros_pagos[0]['nom_otro_pago_es_subsidio'] ='activo';
            $otros_pagos[0]['subsidio_causado'] =0;
            $otros_pagos[0]['nom_par_otro_pago_id'] =-1;
        }

        $nomina = $this->otros_pagos_header(link:$link, nomina: $nomina,nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener otros_pagos', data: $nomina);
        }

        $nomina = $this->data_otros_pagos(link: $link, nomina: $nomina, otros_pagos: $otros_pagos);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar percepciones', data: $nomina);
        }

        return $nomina;
    }

    private function otros_pagos_header(PDO $link, stdClass $nomina, int $nom_nomina_id): array|stdClass
    {
        $nomina->otros_pagos = new stdClass();

        return $nomina;
    }

    private function percepciones(PDO $link, stdClass $nomina, int $nom_nomina_id): array|stdClass
    {

        $percepciones = (new nom_nomina($link))->percepciones(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }


        $nomina = $this->percepciones_header(link:$link, nomina: $nomina,nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $nomina);
        }


        $nomina = $this->data_percepciones(nomina: $nomina, percepciones: $percepciones);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar percepciones', data: $nomina);
        }
        return $nomina;
    }

    private function percepciones_header(PDO $link, stdClass $nomina, int $nom_nomina_id): array|stdClass
    {
        $nomina->percepciones = new stdClass();
        $nomina->percepciones->total_sueldos = (new nom_nomina($link))->total_sueldos_monto(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sueldos', data: $nomina->percepciones->total_sueldos);
        }

        $nomina->percepciones->total_gravado = (new nom_nomina($link))->total_percepciones_gravado(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener sueldos', data: $nomina->percepciones->total_gravado);
        }

        $nomina->percepciones->total_exento = (new nom_nomina($link))->total_percepciones_exento(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(
                mensaje: 'Error al obtener sueldos', data: $nomina->percepciones->total_exento);
        }
        return $nomina;
    }

    /**
     * Obtiene el receptor para un xml
     * @param int $com_sucursal_id cliente tipo empleado
     * @param int $fc_factura_id Factura de nomina
     * @param PDO $link conexion de la bd
     * @return array|stdClass
     * @version 0.461.25
     */
    private function receptor(int $com_sucursal_id, int $fc_factura_id, PDO $link): array|stdClass
    {
        if($com_sucursal_id<=0){
            return $this->error->error(mensaje: 'Error com_sucursal_id debe ser mayor a 0', data: $com_sucursal_id);
        }
        if($fc_factura_id<=0){
            return $this->error->error(mensaje: 'Error fc_factura_id debe ser mayor a 0', data: $fc_factura_id);
        }
        $com_sucursal = (new com_sucursal($link))->registro(registro_id:$com_sucursal_id, retorno_obj: true );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sucursal', data: $com_sucursal);
        }
        $fc_factura = (new fc_factura($link))->registro(registro_id:$fc_factura_id, retorno_obj: true );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener factura', data: $fc_factura);
        }
        
        $receptor = $this->data_receptor(com_sucursal: $com_sucursal, fc_factura: $fc_factura);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al crear receptor', data: $receptor);
        }
        return $receptor;
    }

    public function xml(PDO $link, stdClass $nom_nomina): bool|array|string
    {
        $data_cfdi = $this->data_cfdi_base(fc_factura_id:  $nom_nomina->fc_factura_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data cfdi', data: $data_cfdi);
        }


        $nomina = $this->data_cfdi_base_nomina(data_cfdi: $data_cfdi,link:  $link, nom_nomina: $nom_nomina);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al asignar xml nomina', data: $nomina);
        }

        $xml = (new cfdis())->complemento_nomina(comprobante: $data_cfdi->comprobante,emisor:  $data_cfdi->emisor,
            nomina: $nomina,receptor:  $data_cfdi->receptor);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar xml', data: $xml);
        }
        return $xml;
    }
}
