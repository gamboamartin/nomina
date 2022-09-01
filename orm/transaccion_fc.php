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

    public function fc_partida_nom_id(PDO $link, int $nom_nomina_id): int|array
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
    public function upd_descuento_fc_partida(int $fc_partida_id, PDO $link, float|int $total_deducciones): array|stdClass
    {
        $fc_partida_upd['descuento'] = $total_deducciones;
        $r_fc_partida_upd = (new fc_partida($link))->modifica_bd(registro: $fc_partida_upd,id:  $fc_partida_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones', data: $r_fc_partida_upd);
        }
        return $r_fc_partida_upd;
    }


}