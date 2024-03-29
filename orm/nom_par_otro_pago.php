<?php

namespace gamboamartin\nomina\models;

use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;

class nom_par_otro_pago extends nominas
{

    public function __construct(PDO $link)
    {
        $tabla = 'nom_par_otro_pago';
        $columnas = array($tabla => false,'nom_nomina'=>$tabla, 'nom_otro_pago'=>$tabla,
            'cat_sat_tipo_otro_pago_nom'=>'nom_otro_pago','cat_sat_periodicidad_pago_nom'=>'nom_nomina',
            'em_empleado'=>'nom_nomina');
        $campos_obligatorios = array('nom_nomina_id','descripcion_select','alias','codigo_bis','nom_otro_pago_id',
            'importe_gravado','importe_exento');

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->tabla_nom_conf = 'nom_otro_pago';

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(): array|stdClass
    {
        $keys = array('nom_nomina_id','nom_otro_pago_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $modelo = new nom_otro_pago($this->link);

        $r_alta_bd = $this->alta_bd_percepcion(modelo: $modelo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta registro', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

    public function elimina_bd(int $id): array|stdClass
    {

        $nom_datas_subsidios = (new nom_data_subsidio($this->link))->get_data_by_otro_pago(nom_par_otro_pago_id: $id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data subsidio', data: $nom_datas_subsidios);
        }

        $dels = $this->del_data_subsidio(nom_datas_subsidios: $nom_datas_subsidios);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar data subsidio', data: $dels);
        }

        $r_elimina_bd = parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar', data: $r_elimina_bd);
        }
        return $r_elimina_bd;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $nom_par_otro_pago = $this->registro(registro_id :$id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data:  $nom_par_otro_pago);
        }

        $es_subsidio = false;
        if((int)$nom_par_otro_pago['nom_otro_pago_id'] === 2){
            $es_subsidio = true;
        }

        $r_modifica_bd = $this->modifica_bd_percepcion(registro: $registro,id:  $id, es_subsidio: $es_subsidio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar registro',data:  $r_modifica_bd);
        }

        return $r_modifica_bd;

    }

    /**
     * @throws JsonException

     */
    public function modifica_subsidio(int $nom_par_otro_pago_id, float $importe_gravado, float $importe_exento): array|stdClass
    {
        $nom_par_otro_pago_upd['importe_gravado'] = $importe_gravado;
        $nom_par_otro_pago_upd['importe_exento'] = $importe_exento;
        $r_nom_par_otro_pago = parent::modifica_bd(registro: $nom_par_otro_pago_upd, id:$nom_par_otro_pago_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar isr', data: $r_nom_par_otro_pago);
        }

        return $r_nom_par_otro_pago;


    }

    public function otros_pagos_by_nomina(int $nom_nomina_id): array|stdClass
    {
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $otros_pagos = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener otros pagos', data: $otros_pagos);
        }
        return $otros_pagos;
    }

    public function get_by_otro_pago(int $nom_nomina_id, int $nom_otro_pago_id){
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_otro_pago.id'] = $nom_otro_pago_id;

        $percepciones = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }
        return $percepciones;
    }




}