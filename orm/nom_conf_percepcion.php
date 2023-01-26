<?php
namespace gamboamartin\nomina\models;
use base\orm\_modelo_parent;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_conf_percepcion extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_percepcion';

        $columnas = array($tabla=>false , "nom_percepcion" => $tabla,"nom_conf_nomina" => $tabla);

        $campos_obligatorios = array('codigo_bis','descripcion_select','alias','nom_conf_nomina_id','nom_percepcion_id');

        $campos_view['nom_conf_nomina_id'] = array('type' => 'selects', 'model' => new nom_conf_nomina($link));
        $campos_view['nom_percepcion_id'] = array('type' => 'selects', 'model' => new nom_percepcion($link));
        $campos_view['importe_gravado'] = array('type' => 'inputs');
        $campos_view['importe_exento'] = array('type' => 'inputs');
        $campos_view['fecha_inicio'] = array('type' => 'dates');
        $campos_view['fecha_fin'] = array('type' => 'dates');
        $campos_view['codigo'] = array('type' => 'inputs');
        $campos_view['codigo_bis'] = array('type' => 'inputs');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view: $campos_view);

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
            return $this->error->error(mensaje: 'Error registrar conf. percepción', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

    /**
     * Verifica si aplica septimo dia
     * @param int $nom_conf_nomina_id Configuracion de nomina
     * @return array|bool
     * @version 0.483.26
     */
    public function aplica_septimo_dia(int $nom_conf_nomina_id): array|bool
    {
        if($nom_conf_nomina_id<=0){
            return $this->error->error(
                mensaje: 'Error nom_conf_nomina_id debe ser mayor a 0', data: $nom_conf_nomina_id);
        }
        $filtro['nom_conf_nomina.id'] = $nom_conf_nomina_id;
        $filtro['nom_percepcion.aplica_septimo_dia'] = "activo";

        $existe = $this->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al determinar si aplica septimo dia', data: $existe);
        }

        return $existe;
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