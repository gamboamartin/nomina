<?php
namespace models;
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
        $tabla = __CLASS__;
        $columnas = array($tabla=>false);
        $campos_obligatorios = array();

        $campos_view['cat_sat_forma_pago_id']['type'] = "selects";
        $campos_view['cat_sat_forma_pago_id']['model'] = (new cat_sat_forma_pago($link));
        $campos_view['cat_sat_metodo_pago_id']['type'] = "selects";
        $campos_view['cat_sat_metodo_pago_id']['model'] = (new cat_sat_metodo_pago($link));
        $campos_view['cat_sat_moneda_id']['type'] = "selects";
        $campos_view['cat_sat_moneda_id']['model'] = (new cat_sat_moneda($link));
        $campos_view['com_tipo_cambio_id']['type'] = "selects";
        $campos_view['com_tipo_cambio_id']['model'] = (new com_tipo_cambio($link));
        $campos_view['cat_sat_uso_cfdi_id']['type'] = "selects";
        $campos_view['cat_sat_uso_cfdi_id']['model'] = (new cat_sat_uso_cfdi($link));
        $campos_view['cat_sat_tipo_de_comprobante_id']['type'] = "selects";
        $campos_view['cat_sat_tipo_de_comprobante_id']['model'] = (new cat_sat_tipo_de_comprobante($link));
        $campos_view['com_producto_id']['type'] = "selects";
        $campos_view['com_producto_id']['model'] = (new com_producto($link));

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }
}