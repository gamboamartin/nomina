<?php

namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JsonException;
use PDO;
use stdClass;

class transaccion_fc{

    private errores $error;
    private validacion $validacion;


    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();

    }

    /**
     * @throws JsonException
     */
    private function actualiza_deduccion(int $fc_partida_id, PDO $link, int $nom_nomina_id): array|stdClass
    {
        $total_deducciones = (new totales_nomina())->total_deducciones(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones', data: $total_deducciones);
        }

        $fc_partida_upd = $this->upd_descuento_fc_partida(fc_partida_id: $fc_partida_id, link: $link,
            total_deducciones:  $total_deducciones);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener actualizar descuento', data: $fc_partida_upd);
        }

        return $fc_partida_upd;
    }

    /**
     * @throws JsonException
     */
    public function actualiza_fc_partida_factura(PDO $link,int $nom_nomina_id): array|stdClass
    {
        $fc_partida_id = $this->fc_partida_nom_id(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener $fc_partida_id', data: $fc_partida_id);
        }

        $fc_partida_upd = $this->fc_partida_upd(fc_partida_id: $fc_partida_id, link: $link, nom_nomina_id:  $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener actualizar partida', data: $fc_partida_upd);
        }

        return $fc_partida_upd;
    }

    /**
     * @throws JsonException
     */
    private function actualiza_valor_unitario(int $fc_partida_id, PDO $link, int $nom_nomina_id): array|stdClass
    {
        $total_ingreso_bruto = (new totales_nomina())->total_ingreso_bruto(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total ingreso', data: $total_ingreso_bruto);
        }

        $fc_partida_upd = $this->upd_valor_unitario_fc_partida(fc_partida_id: $fc_partida_id, link: $link,
            total_ingreso_bruto:  $total_ingreso_bruto);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar ingreso', data: $fc_partida_upd);
        }
        return $fc_partida_upd;
    }

    /**
     * @throws JsonException
     */
    public function aplica_deduccion(nominas $mod_nominas, float $monto, int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
    {
        $data_existe = $mod_nominas->data_deduccion(monto: $monto, nom_deduccion_id: $nom_deduccion_id,
            nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }

        $transaccion = $this->transaccion_deduccion(data_existe: $data_existe,link: $mod_nominas->link,nom_par_deduccion_ins: $data_existe->row_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
        }

        return $transaccion;
    }





    /**
     * Obtiene una factura en base a la nomina cargada
     * @param PDO $link Conexion a la bd
     * @param int $nom_nomina_id Identificador de nomina
     * @return array|stdClass
     * @version 0.194.6
     */
    private function fc_factura(PDO $link, int $nom_nomina_id): array|stdClass
    {
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }

        $nom_nomina = (new nom_nomina($link))->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener nomina' , data: $nom_nomina);
        }

        $fc_factura = (new fc_factura($link))->registro(registro_id: $nom_nomina->fc_factura_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener factura' , data: $fc_factura);
        }
        return $fc_factura;

    }

    /**
     * Obtiene el id d ela factura
     * @param PDO $link Conexion a la bd
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return int|array
     */
    private function fc_factura_id(PDO $link, int $nom_nomina_id): int|array
    {
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }
        $fc_factura = $this->fc_factura(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener factura' , data: $fc_factura);
        }
        return (int)$fc_factura->fc_factura_id;

    }



    private function fc_partida_nom(PDO $link, int $nom_nomina_id){
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }
        $fc_factura_id = $this->fc_factura_id(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener factura id' , data: $fc_factura_id);
        }

        $filtro['fc_factura.id'] = $fc_factura_id;
        $r_fc_partida = (new fc_partida($link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener partidas' , data: $r_fc_partida);
        }
        if($r_fc_partida->n_registros === 0){
            return $this->error->error(mensaje:'Error no existe partida' , data: $r_fc_partida);
        }
        if($r_fc_partida->n_registros > 1){
            return $this->error->error(mensaje:'Error  existe mas de una partida' , data: $r_fc_partida);
        }
        return $r_fc_partida->registros[0];


    }

    private function fc_partida_nom_id(PDO $link, int $nom_nomina_id): int|array
    {
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }
        $fc_partida_nom = $this->fc_partida_nom(link:$link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje:'Error al obtener partida' , data: $fc_partida_nom);
        }

        return (int)$fc_partida_nom['fc_partida_id'];

    }

    /**
     * @throws JsonException
     */
    private function fc_partida_upd(int $fc_partida_id, PDO $link, int $nom_nomina_id): array|stdClass
    {
        $upd = new stdClass();
        $fc_partida_upd = $this->actualiza_deduccion(fc_partida_id: $fc_partida_id,
            link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener actualizar descuento', data: $fc_partida_upd);
        }
        $upd->descuento = $fc_partida_upd;

        $fc_partida_upd = $this->actualiza_valor_unitario(fc_partida_id: $fc_partida_id,
            link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar ingreso', data: $fc_partida_upd);
        }
        $upd->valor_unitario = $fc_partida_upd;

        return $upd;
    }

    /**
     * @throws JsonException
     */
    private function modifica_deduccion(array $filtro, PDO $link, array $nom_par_deduccion_upd): array|\stdClass
    {

        $nom_par_deduccion_modelo = new nom_par_deduccion($link);

        $nom_par_deduccion = $nom_par_deduccion_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion', data: $nom_par_deduccion);
        }

        $r_modifica_nom_par_deduccion = $nom_par_deduccion_modelo->modifica_bd(
            registro:$nom_par_deduccion_upd, id: $nom_par_deduccion->registros[0]['nom_par_deduccion_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar deduccion', data: $r_modifica_nom_par_deduccion);
        }

        return $r_modifica_nom_par_deduccion;
    }

    /**
     * @throws JsonException
     */
    private function transaccion_deduccion(stdClass $data_existe, PDO $link, array $nom_par_deduccion_ins): array|stdClass
    {
        $result = new stdClass();
        if($data_existe->existe){
            $r_modifica_nom_par_deduccion = $this->modifica_deduccion(
                filtro: $data_existe->filtro, link: $link,nom_par_deduccion_upd:  $nom_par_deduccion_ins);
            if(errores::$error){
                return $this->error->error(
                    mensaje: 'Error al modificar deduccion', data: $r_modifica_nom_par_deduccion);
            }
            $result->data = $r_modifica_nom_par_deduccion;
            $result->transaccion = 'modifica';
        }
        else{
            $r_alta_nom_par_deduccion = (new nom_par_deduccion($link))->alta_registro(
                registro: $nom_par_deduccion_ins);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al registrar deduccion', data: $r_alta_nom_par_deduccion);
            }
            $result->data = $r_alta_nom_par_deduccion;
            $result->transaccion = 'alta';

        }
        return $result;


    }

    /**
     * @throws JsonException
     */
    private function upd_descuento_fc_partida(int $fc_partida_id, PDO $link, float|int $total_deducciones): array|stdClass
    {
        $fc_partida_upd['descuento'] = $total_deducciones;
        $r_fc_partida_upd = (new fc_partida($link))->modifica_bd(registro: $fc_partida_upd,id:  $fc_partida_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones', data: $r_fc_partida_upd);
        }
        return $r_fc_partida_upd;
    }

    /**
     * @throws JsonException
     */
    private function upd_valor_unitario_fc_partida(int $fc_partida_id, PDO $link, float|int $total_ingreso_bruto): array|stdClass
    {
        $fc_partida_upd['valor_unitario'] = $total_ingreso_bruto;
        $r_fc_partida_upd = (new fc_partida($link))->modifica_bd(registro: $fc_partida_upd,id:  $fc_partida_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar valor unitario', data: $r_fc_partida_upd);
        }
        return $r_fc_partida_upd;

    }


}
