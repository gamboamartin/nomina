<?php
namespace models;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;


class nom_par_percepcion extends nominas{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_nomina'=>$tabla, 'nom_percepcion'=>$tabla,
            'cat_sat_tipo_percepcion_nom'=>'nom_percepcion','cat_sat_periodicidad_pago_nom'=>'nom_nomina',
            'em_empleado'=>'nom_nomina');
        $campos_obligatorios = array('nom_nomina_id','descripcion_select','alias','codigo_bis','nom_percepcion_id',
            'importe_gravado','importe_exento');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->tabla_nom_conf = 'nom_percepcion';
    }


    public function alta_bd(): array|stdClass
    {

        $keys = array('nom_nomina_id','nom_percepcion_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $modelo = new nom_percepcion($this->link);

        $r_alta_bd = $this->alta_bd_percepcion(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta registro', data: $r_alta_bd);
        }


        return $r_alta_bd;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $r_modifica_bd = $this->modifica_bd_percepcion(registro: $registro,id:  $id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar registro',data:  $r_modifica_bd);
        }
        return $r_modifica_bd;

    }

    public function percepciones_by_nomina(int $nom_nomina_id): array|stdClass
    {
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $percepciones = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }
        return $percepciones;
    }

    public function aplica_septimo_dia(int $nom_nomina_id): array|bool
    {
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_septimo_dia'] = "activo";

        $percepciones = $this->filtro_and(columnas: ['nom_percepcion_aplica_septimo_dia'], filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }

        if ($percepciones->n_registros == 0){
            return false;
        }

        return true;
    }
}