<?php
namespace gamboamartin\nomina\models;
use base\orm\_modelo_parent;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_conf_nomina extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_nomina';

        $columnas = array($tabla=>false, 'nom_conf_factura' => $tabla, 'cat_sat_periodicidad_pago_nom' => $tabla,
            'cat_sat_tipo_nomina' => $tabla);

        $campos_obligatorios = array('cat_sat_periodicidad_pago_nom_id','cat_sat_tipo_nomina_id',
            'nom_conf_factura_id','descripcion_select');

        $columnas_extra['nom_conf_nomina_n_conf_percepciones'] = "(SELECT COUNT(*) FROM nom_conf_percepcion 
        WHERE nom_conf_percepcion.nom_conf_nomina_id = nom_conf_nomina.id)";

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas, columnas_extra: $columnas_extra);

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
            return $this->error->error(mensaje: 'Error registrar conf. nomina', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

    public function get_codigo_aleatorio(int $longitud = 6): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_string = '';

        for($i = 0; $i < $longitud; $i++) {
            $random_character = $chars[mt_rand(0, strlen($chars) - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }
}