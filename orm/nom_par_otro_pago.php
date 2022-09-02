<?php

namespace models;

use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;

class nom_par_otro_pago extends nominas
{

    public function __construct(PDO $link)
    {
        $tabla = __CLASS__;
        $columnas = array($tabla => false,'nom_nomina'=>$tabla, 'nom_otro_pago'=>$tabla,
            'cat_sat_tipo_otro_pago_nom'=>'nom_otro_pago','cat_sat_periodicidad_pago_nom'=>'nom_nomina',
            'em_empleado'=>'nom_nomina');
        $campos_obligatorios = array('nom_nomina_id','descripcion_select','alias','codigo_bis','nom_otro_pago_id',
            'importe_gravado','importe_exento');

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->tabla_nom_conf = 'nom_otro_pago';
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




}