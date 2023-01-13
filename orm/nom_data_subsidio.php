<?php

namespace gamboamartin\nomina\models;

use gamboamartin\errores\errores;
use PDO;
use stdClass;


class nom_data_subsidio extends nominas_confs
{

    public function __construct(PDO $link)
    {
        $tabla = 'nom_data_subsidio';
        $columnas = array($tabla => false,'nom_par_deduccion'=>$tabla, 'nom_par_otro_pago'=>$tabla);
        $campos_obligatorios = array('descripcion','codigo','alias','codigo_bis','nom_par_deduccion_id',
            'nom_par_otro_pago_id','monto_isr_bruto','monto_subsidio_bruto');

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function alta_bd(): array|stdClass
    {

        $keys = array('nom_par_deduccion_id');
        $valida = $this->validacion->valida_ids(keys: $keys,registro:  $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro',data:  $valida);
        }

        $keys_registro = array();
        $keys_row = array();

        $modelo = new nom_par_deduccion($this->link);

        $registro = $this->asigna_codigo(keys_registro: $keys_registro, keys_row: $keys_row, modelo: $modelo,
            registro:  $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }


        $this->registro = $registro;

        $registro = $this->asigna_descripcion(modelo: $modelo, registro: $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }

        $this->registro = $registro;

        $registro = $this->asigna_alias(registro: $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }

        $this->registro = $registro;

        $registro = $this->asigna_codigo_bis(registro: $this->registro);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo',data:  $registro);
        }

        $this->registro = $registro;

        $r_alta_bd = parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al dar de alta data subsidio',data:  $r_alta_bd);
        }
        return $r_alta_bd;
    }

    /**
     * Obtiene los registros basados de una partida de percepcion
     * @param int $nom_par_deduccion_id Identificador de la deduccion
     * @return array

     */
    public function get_data_by_deduccion(int $nom_par_deduccion_id): array
    {
        if($nom_par_deduccion_id<=0){
            return $this->error->error(
                mensaje: 'Error $nom_par_deduccion_id debe ser mayor a 0', data: $nom_par_deduccion_id);
        }
        $filtro['nom_par_deduccion.id'] = $nom_par_deduccion_id;
        $r_nom_data_subsidio = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data subsidio', data: $r_nom_data_subsidio);
        }
        return $r_nom_data_subsidio->registros;
    }

    /**
     * Obtiene los registros basados de una partida de deduccion
     * @param int $nom_par_otro_pago_id Otro pago a obtener
     * @return array
     * @version 0.249.7
     */
    public function get_data_by_otro_pago(int $nom_par_otro_pago_id): array
    {
        if($nom_par_otro_pago_id<=0){
            return $this->error->error(
                mensaje: 'Error $nom_par_otro_pago_id debe ser mayor a 0', data: $nom_par_otro_pago_id);
        }
        $filtro['nom_par_otro_pago.id'] = $nom_par_otro_pago_id;
        $r_nom_data_subsidio = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data subsidio', data: $r_nom_data_subsidio);
        }
        return $r_nom_data_subsidio->registros;
    }



    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {
        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar',data:  $r_modifica_bd);
        }

        $monto_isr_bruto = round($registro['monto_isr_bruto'],2);
        $monto_subsidio_bruto = round($registro['monto_subsidio_bruto'],2);
        $nom_par_otro_pago_id = $registro['nom_par_otro_pago_id'];
        $nom_par_deduccion_id = $registro['nom_par_deduccion_id'];

        $monto_isr_neto = 0;
        $monto_subsidio_neto = 0;
        if($monto_isr_bruto >= $monto_subsidio_bruto){
            $monto_isr_neto = $monto_isr_bruto-$monto_subsidio_bruto;

        }
        if($monto_isr_bruto < $monto_subsidio_bruto){
            $monto_isr_neto = 0;
            $monto_subsidio_neto = $monto_subsidio_bruto-$monto_isr_bruto;
        }

        $upd = (new nom_par_otro_pago($this->link))->modifica_subsidio(nom_par_otro_pago_id: $nom_par_otro_pago_id,
            importe_gravado: 0, importe_exento: $monto_subsidio_neto);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar subsidio', data: $upd);
        }

        $upd = (new nom_par_deduccion($this->link))->modifica_isr(nom_par_deduccion_id: $nom_par_deduccion_id,
            importe_gravado: $monto_isr_neto, importe_exento: 0);

        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar subsidio', data: $upd);
        }



        return $r_modifica_bd;
    }

    public function nom_data_subsidio_id(array $filtro): array|int
    {
        $r_nom_data_subsidio = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data subsidio',data:  $r_nom_data_subsidio);
        }
        if($r_nom_data_subsidio->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe data subsidio',data:  $r_nom_data_subsidio);
        }
        if($r_nom_data_subsidio->n_registros > 1){
            return $this->error->error(mensaje: 'Error existe mas de un registro de data subsidio',
                data:  $r_nom_data_subsidio);
        }
        return (int)$r_nom_data_subsidio->registros[0]['nom_data_subsidio_id'];
    }


}