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
     * Verifica si aplica una deduccion automatica
     * @param nominas $mod_nominas
     * @param float $monto
     * @param int $nom_deduccion_id
     * @param int $nom_nomina_id
     * @return array|stdClass
     * @throws JsonException
     */
    private function aplica_deduccion(nominas $mod_nominas, float $monto, int $nom_deduccion_id, int $nom_nomina_id): array|stdClass
    {
        $data_existe = $mod_nominas->data_deduccion(monto_exento: 0,monto_gravado: $monto,
            nom_deduccion_id: $nom_deduccion_id, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }

        $transaccion = $this->transaccion_deduccion(data_existe: $data_existe,link: $mod_nominas->link,
            nom_par_deduccion_ins: $data_existe->row_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
        }

        return $transaccion;
    }

    /**
     */
    private function aplica_imss_valor(nominas $mod_nominas, int $nom_nomina_id, int $partida_percepcion_id): array|stdClass
    {

        $imss = $mod_nominas->imss(partida_percepcion_id: $partida_percepcion_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular imss', data: $imss);
        }

        $genera_imss = $this->result_imss(imss: $imss, mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $genera_imss);
        }



        return $genera_imss;
    }



    /**
     */
    private function aplica_imss_valor_por_nomina(nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {

        $imss = $mod_nominas->imss_por_nomina(nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular imss', data: $imss);
        }

        $genera_imss = $this->result_imss(imss: $imss, mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $genera_imss);
        }
        return $genera_imss;
    }

    private function aplica_otro_pago(nominas $mod_nominas, float $monto, int $nom_otro_pago_id, int $nom_nomina_id): array|stdClass
    {
        $data_existe = $mod_nominas->data_otro_pago(monto: $monto, nom_otro_pago_id: $nom_otro_pago_id,
            nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }

        $transaccion = $this->transaccion_otro_pago(data_existe: $data_existe,
            link: $mod_nominas->link,nom_par_otro_pago_ins: $data_existe->row_ins);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
        }

        return $transaccion;
    }

    /**
     * @throws JsonException
     */
    private function del_nodo(nominas $mod_nominas, int $nom_nomina_id): array
    {
        $elimina_deducciones = array();
        $data_existe = $mod_nominas->existe_data_deduccion(nom_deduccion_id:1, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }
        if($data_existe->existe){
            $elimina_deducciones = $this->elimina_deduccion(filtro: $data_existe->filtro, link: $mod_nominas->link);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }

        }
        return $elimina_deducciones;
    }

    /**
     * @throws JsonException
     */
    private function del_nodo_0(nominas $mod_nominas, int $nom_nomina_id): array
    {
        $elimina_deducciones = array();
        $data_existe = $mod_nominas->existe_data_deduccion(nom_deduccion_id:1, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }
        if($data_existe->existe){
            $elimina_deducciones = $this->elimina_deduccion_0(filtro: $data_existe->filtro, link: $mod_nominas->link);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }

        }
        return $elimina_deducciones;
    }


    private function ejecuta_isr(float $isr, nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {
        $transaccion = array();
        if($isr>0.0){
            $transaccion = $this->aplica_deduccion(mod_nominas: $mod_nominas, monto: $isr, nom_deduccion_id: 1,
                nom_nomina_id:  $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }
        }
        elseif($isr<=0.0){
            $transaccion = $this->del_nodo_0(mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $transaccion);
            }
        }

        return $transaccion;
    }



    /**
     * @throws JsonException
     */
    private function ejecuta_transaccion_imss(bool $aplica_imss, nominas $mod_nominas, int $nom_nomina_id,
                                              int $partida_percepcion_id): array|stdClass
    {
        $data = new stdClass();
        $data->transaccion_aplicada = false;
        $data->transaccion = new stdClass();
        $data->dels = array();
        $data->imss = array();
        if($aplica_imss) {
            $aplicacion_imss = $this->aplica_imss_valor(mod_nominas:$mod_nominas, nom_nomina_id: $nom_nomina_id,
                partida_percepcion_id: $partida_percepcion_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
            }
            $data->transaccion_aplicada = $aplicacion_imss->transaccion_aplicada;
            $data->transaccion = $aplicacion_imss->transaccion;
            $data->imss = $aplicacion_imss->imss;

        }
        else{
            $elimina_deducciones = $this->elimina_imss(mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }
            $data->dels = $elimina_deducciones;
        }
        return $data;
    }

    private function ejecuta_transaccion_imss_por_nomina(bool $aplica_imss, nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {
        $data = new stdClass();
        $data->transaccion_aplicada = false;
        $data->transaccion = new stdClass();
        $data->dels = array();
        $data->imss = array();
        if($aplica_imss) {
            $aplicacion_imss = $this->aplica_imss_valor_por_nomina(mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
            }
            $data->transaccion_aplicada = $aplicacion_imss->transaccion_aplicada;
            $data->transaccion = $aplicacion_imss->transaccion;
            $data->imss = $aplicacion_imss->imss;

        }
        else{
            $elimina_deducciones = $this->elimina_imss(mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }
            $data->dels = $elimina_deducciones;
        }
        return $data;
    }

    /**
     * @throws JsonException
     */
    private function elimina_deduccion(array $filtro, PDO $link): array
    {
        $r_nom_par_deduccion = (new nom_par_deduccion(link: $link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion', data: $r_nom_par_deduccion);
        }
        $dels = array();
        foreach ($r_nom_par_deduccion->registros as $par_deduccion) {
            $elimina_deduccion = (new nom_par_deduccion(link: $link))->elimina_bd(
                id: $par_deduccion['nom_par_deduccion_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar deduccion', data: $elimina_deduccion);
            }
            $dels[] = $elimina_deduccion;
        }
        return $dels;
    }

    /**
     * @throws JsonException
     */
    private function elimina_deduccion_0(array $filtro, PDO $link): array
    {
        $r_nom_par_deduccion = (new nom_par_deduccion(link: $link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion', data: $r_nom_par_deduccion);
        }
        $dels = array();
        foreach ($r_nom_par_deduccion->registros as $par_deduccion) {
            $nom_par_deduccion['importe_exento'] = 0;
            $nom_par_deduccion['importe_gravado'] = 0;
            $elimina_deduccion = (new nom_par_deduccion(link: $link))->modifica_bd(
                registro: $nom_par_deduccion, id: $par_deduccion['nom_par_deduccion_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar deduccion', data: $elimina_deduccion);
            }
            $dels[] = $elimina_deduccion;
        }
        return $dels;
    }

    /**
     * @throws JsonException
     */
    private function elimina_imss(nominas $mod_nominas, int $nom_nomina_id): array
    {
        $elimina_deducciones = array();
        $data_existe = $mod_nominas->existe_data_deduccion(nom_deduccion_id:2, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
        }
        if($data_existe->existe){
            $elimina_deducciones = $this->elimina_deduccion( filtro: $data_existe->filtro,link:$mod_nominas->link);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
            }
        }
        return $elimina_deducciones;
    }

    /**
     * @throws JsonException
     */
    private function elimina_otro_pago(array $filtro, PDO $link): array
    {
        $r_nom_par_otro_pago = (new nom_par_otro_pago(link: $link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion', data: $r_nom_par_otro_pago);
        }
        $dels = array();
        foreach ($r_nom_par_otro_pago->registros as $par_otro_pago) {

            $filtro_npop = array();
            $filtro_npop['nom_par_otro_pago.id'] = $par_otro_pago['nom_par_otro_pago_id'];
            $existe_data_subsidio = (new nom_data_subsidio($link))->existe(filtro: $filtro_npop);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar si existe data subsidio', data: $existe_data_subsidio);
            }

            if($existe_data_subsidio){

                $nom_data_subsidio_id = (new nom_data_subsidio($link))->nom_data_subsidio_id(filtro: $filtro_npop);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener id de data subsidio', data: $nom_data_subsidio_id);
                }

                $elimina_data_subsidio = (new nom_data_subsidio(link: $link))->elimina_bd(id: $nom_data_subsidio_id);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al eliminar data subsidio', data: $elimina_data_subsidio);
                }
            }

            $elimina_otro_pago = (new nom_par_otro_pago(link: $link))->elimina_bd(id: $par_otro_pago['nom_par_otro_pago_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar deduccion', data: $elimina_otro_pago);
            }
            $dels[] = $elimina_otro_pago;
        }
        return $dels;
    }

    private function elimina_otro_pago_0(array $filtro, PDO $link): array
    {
        $r_nom_par_otro_pago = (new nom_par_otro_pago(link: $link))->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener deduccion', data: $r_nom_par_otro_pago);
        }
        $dels = array();
        foreach ($r_nom_par_otro_pago->registros as $par_otro_pago) {

            $filtro_npop = array();
            $filtro_npop['nom_par_otro_pago.id'] = $par_otro_pago['nom_par_otro_pago_id'];
            $existe_data_subsidio = (new nom_data_subsidio($link))->existe(filtro: $filtro_npop);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al validar si existe data subsidio', data: $existe_data_subsidio);
            }

            if($existe_data_subsidio){

                $nom_data_subsidio_id = (new nom_data_subsidio($link))->nom_data_subsidio_id(filtro: $filtro_npop);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al obtener id de data subsidio', data: $nom_data_subsidio_id);
                }

                $elimina_data_subsidio = (new nom_data_subsidio(link: $link))->elimina_bd(id: $nom_data_subsidio_id);
                if (errores::$error) {
                    return $this->error->error(mensaje: 'Error al eliminar data subsidio', data: $elimina_data_subsidio);
                }
            }
            $nom_otro_pago_upd = array();
            $nom_otro_pago_upd['importe_gravado'] = 0;
            $nom_otro_pago_upd['importe_exento'] = 0;
            $upd_otro_pago = (new nom_par_otro_pago(link: $link))->modifica_bd(registro:$nom_otro_pago_upd, id: $par_otro_pago['nom_par_otro_pago_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar deduccion', data: $upd_otro_pago);
            }
            $dels[] = $upd_otro_pago;
        }
        return $dels;
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
    private function genera_imss(array $imss, nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {
        $data = new stdClass();
        $transaccion = new stdClass();
        $transaccion_aplicada = false;
        if ((float)$imss['total'] > 0.0) {

            $nom_deduccion_id = (new nom_deduccion($mod_nominas->link))->nom_deduccion_imss_id();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener deduccion imss id', data: $nom_deduccion_id);
            }

            $transaccion = $this->aplica_deduccion(mod_nominas: $mod_nominas,
                monto: (float)$imss['total'], nom_deduccion_id: $nom_deduccion_id, nom_nomina_id: $nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }
            $transaccion_aplicada = true;
        }

        $data->transaccion = $transaccion;
        $data->transaccion_aplicada = $transaccion_aplicada;
        return $data;

    }

    /**
     * @throws JsonException
     */
    private function integra_deducciones(nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {

        $transaccion_isr = $this->transacciona_isr_por_nomina(mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar isr', data: $transaccion_isr);
        }

        $transaccion_imss = $this->transacciona_imss_por_nomina(mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transaccion imss', data: $transaccion_imss);
        }
        $data = new stdClass();
        $data->imss = $transaccion_imss;
        $data->isr = $transaccion_isr;
        return $data;
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

    private function modifica_otro_pago(array $filtro, PDO $link, array $nom_par_otro_pago_upd): array|\stdClass
    {

        $nom_par_otro_pago_modelo = new nom_par_otro_pago($link);

        $nom_par_otro_pago = $nom_par_otro_pago_modelo->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener otro pago', data: $nom_par_otro_pago);
        }

        $r_modifica_nom_par_otro_pago = $nom_par_otro_pago_modelo->modifica_bd(
            registro:$nom_par_otro_pago_upd, id: $nom_par_otro_pago->registros[0]['nom_par_otro_pago_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar otro pago', data: $r_modifica_nom_par_otro_pago);
        }

        return $r_modifica_nom_par_otro_pago;
    }

    private function result_imss(array $imss, nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {
        $genera_imss = $this->genera_imss(imss: $imss,mod_nominas:  $mod_nominas,nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $genera_imss);
        }


        $data = new stdClass();
        $data->imss = $imss;
        $data->transaccion_aplicada = $genera_imss->transaccion_aplicada;
        $data->transaccion = $genera_imss->transaccion;

        return $data;
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

    private function transaccion_otro_pago(stdClass $data_existe, PDO $link, array $nom_par_otro_pago_ins): array|stdClass
    {
        $result = new stdClass();
        if($data_existe->existe){
            $r_modifica_nom_par_otro_pago = $this->modifica_otro_pago(
                filtro: $data_existe->filtro, link: $link,nom_par_otro_pago_upd:  $nom_par_otro_pago_ins);
            if(errores::$error){
                return $this->error->error(
                    mensaje: 'Error al modificar otro pago', data: $r_modifica_nom_par_otro_pago);
            }
            $result->data = $r_modifica_nom_par_otro_pago;
            $result->transaccion = 'modifica';
        }
        else{
            $r_alta_nom_par_otro_pago = (new nom_par_otro_pago($link))->alta_registro(
                registro: $nom_par_otro_pago_ins);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al registrar otro_pago', data: $r_alta_nom_par_otro_pago);
            }
            $result->data = $r_alta_nom_par_otro_pago;
            $result->transaccion = 'alta';

        }
        return $result;


    }

    /**
     * Transacciona la deduccion de imss
     * @param nominas $mod_nominas
     * @param int $nom_nomina_id Nomina para transaccionar imss
     * @param int $partida_percepcion_id Registro de deduccion, percepcion u otro pago
     * @return array|stdClass
     * @throws JsonException
     */
    private function transacciona_imss(nominas $mod_nominas, int $nom_nomina_id, int $partida_percepcion_id): array|stdClass
    {

        $aplica_imss = (new nom_nomina($mod_nominas->link))->aplica_imss(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si aplica imss', data: $aplica_imss);
        }

        $aplicacion_imss = $this->ejecuta_transaccion_imss(aplica_imss: $aplica_imss, mod_nominas: $mod_nominas,
            nom_nomina_id:  $nom_nomina_id, partida_percepcion_id: $partida_percepcion_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
        }
        return $aplicacion_imss;

    }

    private function transacciona_imss_por_nomina(nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {

        $aplica_imss = (new nom_nomina($mod_nominas->link))->aplica_imss(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar si aplica imss', data: $aplica_imss);
        }

        $aplicacion_imss = $this->ejecuta_transaccion_imss_por_nomina(aplica_imss: $aplica_imss, mod_nominas: $mod_nominas,
            nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $aplicacion_imss);
        }
        return $aplicacion_imss;

    }

    /**
     * @param int $partida_percepcion_id Identificador ya sea otto_pago o percepcion
     * @throws JsonException
     */
    private function transacciona_isr(nominas $mod_nominas, int $nom_nomina_id, int $partida_percepcion_id): float|array
    {
        $isr = (new calculo_isr())->calcula_isr_nomina(modelo: $mod_nominas, partida_percepcion_id: $partida_percepcion_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        if($isr>0.0){

            $transaccion = $this->aplica_deduccion(mod_nominas: $mod_nominas, monto: (float)$isr, nom_deduccion_id: 1,
                nom_nomina_id:  $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }


        }
        return $isr;
    }

    /**
     * @param nominas $mod_nominas
     * @param int $nom_nomina_id
     * @return float|array

     */
    private function transacciona_isr_por_nomina(nominas $mod_nominas, int $nom_nomina_id): float|array
    {

        $data_isr = (new calcula_nomina())->calcula_impuestos_netos_por_nomina(
            link: $mod_nominas->link,nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $data_isr);
        }

        $transaccion = $this->ejecuta_isr(
            isr: $data_isr->isr_neto,mod_nominas:  $mod_nominas, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
        }

        return $data_isr->isr_neto;
    }

    /**
     * @throws JsonException
     */
    private function transacciona_isr_subsidio(nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {
        $transacciones_deduccion_isr = $this->transacciona_isr_por_nomina(
            mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones isr',
                data: $transacciones_deduccion_isr);
        }
        $transacciones_otro_pago_subsidio = $this->transacciona_subsidio_por_nomina(
            mod_nominas: $mod_nominas, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar otros pagos subsidio',
                data: $transacciones_otro_pago_subsidio);
        }

        $data = new stdClass();
        $data->isr = $transacciones_deduccion_isr;
        $data->otro_pago = $transacciones_otro_pago_subsidio;
        return $data;

    }

    private function transacciona_subsidio_por_nomina(nominas $mod_nominas, int $nom_nomina_id): float|array
    {
        $subsidio = (new calculo_subsidio())->calcula_subsidio_por_nomina(link:$mod_nominas->link, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $subsidio);
        }
        $data_isr = (new calcula_nomina())->calcula_impuestos_netos_por_nomina(link: $mod_nominas->link,
            nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $data_isr);
        }


        if($subsidio>0.0){
            $transaccion = $this->aplica_otro_pago(mod_nominas: $mod_nominas,
                monto: (float)$data_isr->subsidio_neto, nom_otro_pago_id: 2, nom_nomina_id:  $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar transaccion', data: $transaccion);
            }
        }
        elseif($subsidio<=0.0){
            $data_existe = $mod_nominas->existe_data_otro_pago(nom_otro_pago_id:2, nom_nomina_id: $nom_nomina_id);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al validar si existe deduccion', data: $data_existe);
            }
            if($data_existe->existe){

                $elimina_deducciones = $this->elimina_otro_pago_0(filtro: $data_existe->filtro, link: $mod_nominas->link);
                if(errores::$error){
                    return $this->error->error(mensaje: 'Error al eliminar deducciones', data: $elimina_deducciones);
                }
            }

        }
        return $subsidio;
    }

    /**
     * @throws JsonException
     */
    public function transacciones_por_nomina(nominas $mod_nominas, int $nom_nomina_id): array|stdClass
    {
        $transacciones_deduccion_isr_subsidio = $this->transacciona_isr_subsidio(
            mod_nominas: $mod_nominas,nom_nomina_id:  $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones isr',
                data: $transacciones_deduccion_isr_subsidio);
        }
        $transacciones_deduccion_imss = $this->transacciona_imss_por_nomina(mod_nominas: $mod_nominas,nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integrar deducciones imss',
                data: $transacciones_deduccion_imss);
        }

        $fc_partida_upd = $this->actualiza_fc_partida_factura(link: $mod_nominas->link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar $fc_partida', data: $fc_partida_upd);
        }

        $data = new stdClass();
        $data->isr = $transacciones_deduccion_isr_subsidio->isr;
        $data->imss = $transacciones_deduccion_imss;
        $data->otro_pago = $transacciones_deduccion_isr_subsidio->otro_pago;
        $data->fc_partida = $fc_partida_upd;
        return $data;
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
