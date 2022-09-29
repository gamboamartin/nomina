<?php
namespace gamboamartin\nomina\controllers;
use gamboamartin\errores\errores;
use gamboamartin\facturacion\models\fc_factura;
use gamboamartin\validacion\validacion;
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
     * Maqueta el objeto de un comprobante para cfdi
     * @param stdClass $fc_factura Factura
     * @param PDO $link Conexion a la base de datos
     * @return array|stdClass
     * @version 0.394.21
     */
    public function data_comprobante(stdClass $fc_factura, PDO $link): array|stdClass
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
}
