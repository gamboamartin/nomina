<?php
namespace gamboamartin\nomina\controllers;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\validacion\validacion;
use models\com_sucursal;
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

    public function comprobante(int $fc_factura_id, PDO $link): array|stdClass
    {

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

    public function emisor(int $fc_factura_id, PDO $link): array|stdClass
    {
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
        $comprobante->total = (new fc_factura($link))->total(fc_factura_id:$fc_factura->fc_factura_id );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total', data: $comprobante->total);
        }
        $comprobante->sub_total = (new fc_factura($link))->sub_total(fc_factura_id:$fc_factura->fc_factura_id );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sub_total', data: $comprobante->sub_total);
        }
        $comprobante->descuento = (
            new fc_factura($link))->get_factura_descuento(fc_factura_id:$fc_factura->fc_factura_id );
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener sub_total', data: $comprobante->sub_total);
        }
        return $comprobante;
    }

    /**
     * Genera los datos de emision de nomina
     * @param stdClass $fc_factura Factura
     * @return stdClass
     *
     */
    private function data_emisor(stdClass $fc_factura): stdClass
    {
        $emisor = new stdClass();
        $emisor->rfc = $fc_factura->org_empresa_rfc;
        $emisor->nombre = $fc_factura->org_empresa_razon_social;
        $emisor->regimen_fiscal = $fc_factura->cat_sat_regimen_fiscal_codigo;

        return $emisor;
    }

    private function data_receptor(stdClass $com_sucursal, stdClass $fc_factura): stdClass
    {
        $receptor = new stdClass();
        $receptor->rfc = $fc_factura->com_cliente_rfc;
        $receptor->nombre = $fc_factura->com_cliente_razon_social;
        $receptor->domicilio_fiscal_receptor = $com_sucursal->dp_cp_descripcion;
        $receptor->regimen_fiscal_receptor = $com_sucursal->cat_sat_regimen_fiscal_codigo;

        return $receptor;
    }
    
    public function nomina_base(PDO $link, stdClass $nom_nomina): array|stdClass
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

    public function receptor(int $com_sucursal_id, int $fc_factura_id, PDO $link): array|stdClass
    {
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
}
