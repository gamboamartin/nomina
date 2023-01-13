<?php
namespace gamboamartin\nomina\models;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;


class nom_par_percepcion extends nominas{

    public function __construct(PDO $link){
        $tabla = 'nom_par_percepcion';
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

    public function get_by_percepcion(int $nom_nomina_id, int $nom_percepcion_id){
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_percepcion.id'] = $nom_percepcion_id;

        $percepciones = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }
        return $percepciones;
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

        $existe = $this->existe(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al determinar si aplica septimo dia', data: $existe);
        }

        return $existe;
    }

    public function get_percepciones_aplica_septimo_dia(int $nom_nomina_id): array|stdClass
    {
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_septimo_dia'] = "activo";
        $percepciones = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }

        return $percepciones;
    }

    public function total_par_percepciones_isn(int $nom_nomina_id): float|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }

        $total_par_percepciones = $this->total_par_percepciones_exento_isn(nom_nomina_id: $nom_nomina_id) +
            $this->total_par_percepciones_gravado_isn(nom_nomina_id: $nom_nomina_id);

        return round($total_par_percepciones);

    }

    public function total_par_percepciones_exento_isn(int $nom_nomina_id): float|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }

        $campos = array();
        $campos['total_importe_exento'] = 'nom_par_percepcion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_isn'] = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }

        return round($r_nom_par_percepcion['total_importe_exento'],2);

    }

    public function total_par_percepciones_gravado_isn(int $nom_nomina_id): float|array
    {
        if($nom_nomina_id <=0 ){
            return $this->error->error(mensaje: 'Error nom_nomina_id debe ser mayor a 0', data: $nom_nomina_id);
        }

        $campos = array();
        $campos['total_importe_gravado'] = 'nom_par_percepcion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_percepcion.aplica_isn'] = 'activo';
        $r_nom_par_percepcion = (new nom_par_percepcion($this->link))->suma(campos: $campos,filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $r_nom_par_percepcion);
        }

        return round($r_nom_par_percepcion['total_importe_gravado'],2);

    }
}