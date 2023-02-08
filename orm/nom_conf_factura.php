<?php
namespace gamboamartin\nomina\models;
use base\orm\_modelo_parent;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_conf_factura extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_factura';
        $columnas = array($tabla => false, "cat_sat_forma_pago" => $tabla, "cat_sat_metodo_pago" => $tabla,
            "cat_sat_moneda" => $tabla, "com_tipo_cambio" => $tabla, "cat_sat_uso_cfdi" => $tabla,
            "cat_sat_tipo_de_comprobante" => $tabla, "com_producto" => $tabla);
        $campos_obligatorios = array('cat_sat_forma_pago_id','cat_sat_metodo_pago_id','cat_sat_moneda_id',
            'com_tipo_cambio_id','cat_sat_uso_cfdi_id','cat_sat_tipo_de_comprobante_id','com_producto_id');


        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        if(!isset($this->registro['codigo'])){
            $this->registro['codigo'] =  $this->get_codigo_aleatorio();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo aleatorio',data:  $this->registro);
            }
        }

        $this->registro = $this->campos_base(data: $this->registro,modelo: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campos base',data: $this->registro);
        }

        $r_alta_bd =  parent::alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error registrar conf. factura', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }
}