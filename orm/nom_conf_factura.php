<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use gamboamartin\cat_sat\models\cat_sat_forma_pago;
use gamboamartin\cat_sat\models\cat_sat_metodo_pago;
use gamboamartin\cat_sat\models\cat_sat_moneda;
use gamboamartin\cat_sat\models\cat_sat_tipo_de_comprobante;
use gamboamartin\cat_sat\models\cat_sat_uso_cfdi;
use gamboamartin\comercial\models\com_producto;
use gamboamartin\comercial\models\com_tipo_cambio;
use PDO;

class nom_conf_factura extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_factura';
        $columnas = array($tabla => false, "cat_sat_forma_pago" => $tabla, "cat_sat_metodo_pago" => $tabla,
            "cat_sat_moneda" => $tabla, "com_tipo_cambio" => $tabla, "cat_sat_uso_cfdi" => $tabla,
            "cat_sat_tipo_de_comprobante" => $tabla, "com_producto" => $tabla);
        $campos_obligatorios = array('cat_sat_forma_pago_id','cat_sat_metodo_pago_id','cat_sat_moneda_id',
            'com_tipo_cambio_id','cat_sat_uso_cfdi_id','cat_sat_tipo_de_comprobante_id','com_producto_id');

        $campos_view['cat_sat_forma_pago_id'] = array('type' => 'selects', 'model' => new cat_sat_forma_pago($link));
        $campos_view['cat_sat_metodo_pago_id'] = array('type' => 'selects', 'model' => new cat_sat_metodo_pago($link));
        $campos_view['cat_sat_moneda_id'] = array('type' => 'selects', 'model' => new cat_sat_moneda($link));
        $campos_view['com_tipo_cambio_id'] = array('type' => 'selects', 'model' => new com_tipo_cambio($link));
        $campos_view['cat_sat_uso_cfdi_id'] = array('type' => 'selects', 'model' => new cat_sat_uso_cfdi($link));
        $campos_view['cat_sat_tipo_de_comprobante_id'] = array('type' => 'selects', 'model' => new cat_sat_tipo_de_comprobante($link));
        $campos_view['com_producto_id'] = array('type' => 'selects', 'model' => new com_producto($link));
        $campos_view['codigo'] = array('type' => 'inputs');
        $campos_view['codigo_bis'] = array('type' => 'inputs');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}