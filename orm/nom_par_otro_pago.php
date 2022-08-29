<?php

namespace models;

use gamboamartin\errores\errores;
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
    }

    public function alta_bd(): array|stdClass
    {
        $keys = array('nom_nomina_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $modelo = new nom_otro_pago($this->link);
        $registro = $this->asigna_registro_alta(modelo: $modelo, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar datos', data: $registro);
        }
        $this->registro = $registro;

        $total = $this->total_otro_pago(registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total', data: $total);
        }

        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar otro pago', data: $r_alta_bd);
        }

        $isr = $this->calcula_isr_nomina(nom_par_otro_pago_id: $r_alta_bd->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        if($isr>0.0){

            $transaccion = $this->aplica_deduccion(monto: (float)(float)$isr, nom_deduccion_id: 1,
                nom_nomina_id:  $this->registro['nom_nomina_id']);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }


            $imss = $this->imss(registro_id: $r_alta_bd->registro_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al calcular imss', data: $imss);
            }
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al calcular imss', data: $imss);
            }

            if((float)$imss['total']>0.0) {

                $transaccion = $this->aplica_deduccion(monto: (float)$imss['total'], nom_deduccion_id: 2,
                    nom_nomina_id:  $this->registro['nom_nomina_id']);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
                }
            }
        }
        return $r_alta_bd;
    }





    private function calcula_isr_nomina(int $nom_par_otro_pago_id): float|array
    {
        $nom_nomina = $this->registro(registro_id:$nom_par_otro_pago_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }

        $isr = 0.0;
        $total_gravado = (new nom_nomina($this->link))->total_gravado(nom_nomina_id: $nom_nomina->nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular total gravado', data: $total_gravado);
        }

        if($total_gravado >0.0) {
            $isr = $this->isr_total_nomina_par_otro_pago(
                nom_par_otro_pago_id: $nom_par_otro_pago_id, total_gravado: $total_gravado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
            }
        }
        return $isr;
    }

    private function isr_total_nomina_par_otro_pago(int $nom_par_otro_pago_id, string $total_gravado): float|array
    {
        $nom_par_otro_pago = $this->registro(registro_id: $nom_par_otro_pago_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nom_par_otro_pago', data: $nom_par_otro_pago);
        }

        $isr = (new nom_nomina($this->link))->isr(
            cat_sat_periodicidad_pago_nom_id: $nom_par_otro_pago->cat_sat_periodicidad_pago_nom_id,
            monto: $total_gravado, fecha: $nom_par_otro_pago->nom_nomina_fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        return $isr;
    }



    private function total_otro_pago(array $registro): float|array
    {
        $total = $registro['importe_exento']+ $registro['importe_gravado'];
        $total = round($total,2);

        if($total<=0.0){
            return $this->error->error(mensaje: 'Error total es 0', data: $total);
        }
        return $total;
    }

}