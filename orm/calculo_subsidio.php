<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class calculo_subsidio{
    private errores $error;
    private validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }


    /**
     * @param modelo $modelo
     * @param int $partida_percepcion_id otro pago o percepcion id
     * @return float|array
     */
    public function calcula_subsidio_nomina(modelo $modelo, int $partida_percepcion_id): float|array
    {
        $nom_partida = $modelo->registro(registro_id:$partida_percepcion_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_partida);
        }

        $isr = 0.0;
        $total_gravado = (new nom_nomina($modelo->link))->total_gravado(nom_nomina_id: $nom_partida->nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular total gravado', data: $total_gravado);
        }

        if($total_gravado >0.0) {
            $isr = $this->subsidio_total_nomina_por_percepcion(modelo:$modelo,
                partida_percepcion_id: $partida_percepcion_id, total_gravado: $total_gravado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
            }
        }
        return $isr;
    }

    /**
     * @param PDO $link
     * @param int $nom_nomina_id
     * @return float|array
     */
    public function calcula_subsidio_por_nomina(PDO $link, int $nom_nomina_id): float|array
    {

        $subsidio = 0.0;
        $total_gravado = (new nom_nomina($link))->total_gravado(nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular total gravado', data: $total_gravado);
        }

        if($total_gravado >0.0) {
            $subsidio = $this->subsidio_nomina(link:$link, nom_nomina_id: $nom_nomina_id,
                total_gravado: $total_gravado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener $subsidio', data: $subsidio);
            }
        }
        return $subsidio;
    }


    /**
     * Genera el filtro para la obtencion de tablas de subsidio
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha de nomina
     * @return array
     * @version 0.177.6
     */
    private function filtro_especial_subsidio(float|int $monto, string $fecha = ''): array
    {
        if($fecha === ''){
            $fecha = date('Y-m-d');
        }
        if($monto<=0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $monto);
        }

        $filtro_especial[0][$fecha]['operador'] = '>=';
        $filtro_especial[0][$fecha]['valor'] = 'cat_sat_subsidio.fecha_inicio';
        $filtro_especial[0][$fecha]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha]['operador'] = '<=';
        $filtro_especial[1][$fecha]['valor'] = 'cat_sat_subsidio.fecha_fin';
        $filtro_especial[1][$fecha]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha]['valor_es_campo'] = true;

        $filtro_especial[2][(string)$monto]['operador'] = '>=';
        $filtro_especial[2][(string)$monto]['valor'] = 'cat_sat_subsidio.limite_inferior';
        $filtro_especial[2][(string)$monto]['comparacion'] = 'AND';
        $filtro_especial[2][(string)$monto]['valor_es_campo'] = true;

        $filtro_especial[3][(string)$monto]['operador'] = '<=';
        $filtro_especial[3][(string)$monto]['valor'] = 'cat_sat_subsidio.limite_superior';
        $filtro_especial[3][(string)$monto]['comparacion'] = 'AND';
        $filtro_especial[3][(string)$monto]['valor_es_campo'] = true;

        return $filtro_especial;
    }

    /**
     * Genera el monto de subsidio en base al registro aplicado
     * @param stdClass $row_subsidio Registro de subsidio
     * @return float|array
     * @version 0.179.6
     */
    private function genera_subsidio( stdClass $row_subsidio): float|array
    {

        $keys = array('cat_sat_subsidio_cuota_fija');
        $valida = $this->validacion->valida_double_mayores_igual_0(keys: $keys, registro: $row_subsidio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row_subsidio', data: $valida);
        }

        $subsidio = $row_subsidio->cat_sat_subsidio_cuota_fija;
        return round($subsidio,2);
    }

    /**
     * Obtiene un registro de subsidio
     * @param int $cat_sat_periodicidad_pago_nom_id Periodicidad de pago identificador
     * @param PDO $link Conexion a la base de datos
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha din del periodo de pago
     * @return array|stdClass
     * @version 0.178.6
     */
    private function get_subsidio(int $cat_sat_periodicidad_pago_nom_id, PDO $link, float|int $monto,
                             string $fecha = ''):array|stdClass{

        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $cat_sat_periodicidad_pago_nom_id debe ser mayor  a 0',
                data: $cat_sat_periodicidad_pago_nom_id);
        }

        $filtro['cat_sat_periodicidad_pago_nom.id'] = $cat_sat_periodicidad_pago_nom_id;

        if($fecha === ''){
            $fecha = date('Y-m-d');
        }

        if($monto<=0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $monto);
        }

        $filtro_especial = $this->filtro_especial_subsidio(monto: $monto, fecha : $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro', data: $filtro_especial);
        }

        $r_isr = (new cat_sat_subsidio($link))->filtro_and(filtro: $filtro, filtro_especial: $filtro_especial);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener isr', data: $r_isr);
        }

        if($r_isr->n_registros===0){
            return $this->error->error(mensaje: 'Error no existe registro isr', data: $r_isr);
        }
        if($r_isr->n_registros>1){
            return $this->error->error(mensaje: 'Error existe mas de un registro de isr', data: $r_isr);
        }


        return $r_isr->registros_obj[0];
    }

    /**
     * Calcula el subsidio
     * @param int $cat_sat_periodicidad_pago_nom_id Periodicidad aplicada
     * @param PDO $link conexion a la bd
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha din del periodo de pago
     * @return float|array
     * @version 0.180.6
     */
    private function subsidio(int $cat_sat_periodicidad_pago_nom_id, PDO $link, float|int $monto,
                              string $fecha = ''): float|array
    {
        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $cat_sat_periodicidad_pago_nom_id debe ser mayor a 0',
                data: $cat_sat_periodicidad_pago_nom_id);
        }
        if($monto<=0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $monto);
        }

        if($fecha === ''){
            $fecha = date('Y-m-d');
        }

        $row_subsidio = $this->get_subsidio(cat_sat_periodicidad_pago_nom_id: $cat_sat_periodicidad_pago_nom_id,
            link: $link, monto:$monto, fecha: $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener subsidio', data: $row_subsidio);
        }

        $subsidio = $this->genera_subsidio(row_subsidio: $row_subsidio);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular subsidio', data: $subsidio);
        }

        return $subsidio;

    }

    /**
     * @param PDO $link
     * @param int $nom_nomina_id Registro de nomina en ejecucion
     * @param string|float|int $total_gravado Monto gravable de nomina
     * @return float|array
     */
    private function subsidio_nomina(PDO $link, int $nom_nomina_id, string|float|int $total_gravado): float|array
    {
        $nom_nomina = (new nom_nomina($link))->registro(registro_id: $nom_nomina_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nomina', data: $nom_nomina);
        }

        $subsidio = $this->subsidio(
            cat_sat_periodicidad_pago_nom_id: $nom_nomina->cat_sat_periodicidad_pago_nom_id, link: $link,
            monto: $total_gravado, fecha: $nom_nomina->nom_nomina_fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $subsidio', data: $subsidio);
        }

        return $subsidio;
    }

    /**
     * @param nominas $modelo $modelo Modelo en ejecucion
     * @param int $partida_percepcion_id otro pago o percepcion id
     * @param string|float|int $total_gravado Monto gravable de nomina
     * @return float|array
     */
    private function subsidio_total_nomina_por_percepcion(nominas $modelo, int $partida_percepcion_id,
                                                     string|float|int $total_gravado): float|array
    {

        if($partida_percepcion_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $partida_percepcion_id debe ser mayor a 0',
                data: $partida_percepcion_id);
        }

        $nom_par_percepcion = $modelo->base_calculo_impuesto(partida_percepcion_id: $partida_percepcion_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $nom_par_percepcion', data: $nom_par_percepcion);
        }

        $subsidio = $this->subsidio(
            cat_sat_periodicidad_pago_nom_id: $nom_par_percepcion->cat_sat_periodicidad_pago_nom_id, link: $modelo->link,
            monto: $total_gravado, fecha: $nom_par_percepcion->nom_nomina_fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener $subsidio', data: $subsidio);
        }

        return $subsidio;
    }

}
