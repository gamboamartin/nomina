<?php
namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class calculo_isr{
    private errores $error;
    private validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * Genera la suma del la cuota exedente y lacuota fija
     * @param float $cuota_excedente Monto calculado en base a la tabla de isr
     * @param stdClass $row_isr Registro de isr
     * @return float|array
     * @version 0.129.16
     */
    public function calcula_isr(float $cuota_excedente, stdClass $row_isr): float|array
    {
        $keys = array('cat_sat_isr_cuota_fija');
        $valida = $this->validacion->valida_double_mayores_igual_0(keys: $keys, registro: $row_isr);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al validar row isr', data: $valida);
        }
        if($cuota_excedente<0.0){
            return $this->error->error(mensaje: 'Error $cuota_excedente debe ser mayor o igual a 0',
                data: $cuota_excedente);
        }

        $isr = $cuota_excedente + $row_isr->cat_sat_isr_cuota_fija;
        return round($isr,2);

    }

    /**
     * Calcula la cuota exedente para isr
     * @param float|int $diferencia_li Diferencia en te monto y limite inferior
     * @param stdClass $row_isr Registro en proceso
     * @return float|array
     * @version 0.126.16
     */
    public function cuota_excedente_isr(float|int $diferencia_li, stdClass $row_isr): float|array
    {
        $keys = array('cat_sat_isr_porcentaje_excedente');
        $valida = $this->validacion->valida_double_mayores_igual_0(keys: $keys, registro: $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row_isr', data: $valida);
        }

        if($diferencia_li<0.0){
            return $this->error->error(mensaje: 'Error $diferencia_li debe ser mayor a 0', data: $diferencia_li);
        }

        $cuota_excedente = $diferencia_li * $row_isr->cat_sat_isr_porcentaje_excedente;
        $cuota_excedente = round($cuota_excedente,2);
        $cuota_excedente /= 100;
        return round($cuota_excedente,2);
    }

    /**
     * Obtiene la diferencia entre limite inferior menos monto
     * @param float|int $monto Monto total gravable
     * @param stdClass $row_isr Registro para isr
     * @return float|array
     * @version 0.119.14
     */
    public function diferencia_li(float|int $monto, stdClass $row_isr): float|array
    {
        $keys = array('cat_sat_isr_limite_inferior');
        $valida = $this->validacion->valida_double_mayores_0(keys: $keys, registro: $row_isr);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar row_isr', data: $valida);
        }

        $diferencia_li = $monto - $row_isr->cat_sat_isr_limite_inferior;
        $diferencia_li = round($diferencia_li, 2);
        if($diferencia_li<0.0){
            return $this->error->error(mensaje: 'Error el limite debe ser menor o igual al monto', data: $diferencia_li);
        }
        return $diferencia_li;
    }

    /**
     * Genera el filtro para la obtencion de tablas de isr
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha de nomina
     * @return array
     * @version 0.110.11
     */
    private function filtro_especial_isr(float|int $monto, string $fecha = ''): array
    {
        if($fecha === ''){
            $fecha = date('Y-m-d');
        }
        if($monto<=0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $monto);
        }

        $filtro_especial[0][$fecha]['operador'] = '>=';
        $filtro_especial[0][$fecha]['valor'] = 'cat_sat_isr.fecha_inicio';
        $filtro_especial[0][$fecha]['comparacion'] = 'AND';
        $filtro_especial[0][$fecha]['valor_es_campo'] = true;

        $filtro_especial[1][$fecha]['operador'] = '<=';
        $filtro_especial[1][$fecha]['valor'] = 'cat_sat_isr.fecha_fin';
        $filtro_especial[1][$fecha]['comparacion'] = 'AND';
        $filtro_especial[1][$fecha]['valor_es_campo'] = true;

        $filtro_especial[2][(string)$monto]['operador'] = '>=';
        $filtro_especial[2][(string)$monto]['valor'] = 'cat_sat_isr.limite_inferior';
        $filtro_especial[2][(string)$monto]['comparacion'] = 'AND';
        $filtro_especial[2][(string)$monto]['valor_es_campo'] = true;

        $filtro_especial[3][(string)$monto]['operador'] = '<=';
        $filtro_especial[3][(string)$monto]['valor'] = 'cat_sat_isr.limite_superior';
        $filtro_especial[3][(string)$monto]['comparacion'] = 'AND';
        $filtro_especial[3][(string)$monto]['valor_es_campo'] = true;

        return $filtro_especial;
    }

    /**
     * @param int $cat_sat_periodicidad_pago_nom_id Periodicidad de pago identificador
     * @param PDO $link Conexion a la base de datos
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha din del periodo de pago
     * @return array|stdClass
     * @version 0.113.11
     */
    public function get_isr(int $cat_sat_periodicidad_pago_nom_id, PDO $link, float|int $monto,
                             string $fecha = ''):array|stdClass{

        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor  a 0',
                data: $cat_sat_periodicidad_pago_nom_id);
        }

        $filtro['cat_sat_periodicidad_pago_nom.id'] = $cat_sat_periodicidad_pago_nom_id;

        if($fecha === ''){
            $fecha = date('Y-m-d');
        }

        if($monto<=0.0){
            return $this->error->error(mensaje: 'Error monto debe ser mayor o igual a 0', data: $monto);
        }

        $filtro_especial = $this->filtro_especial_isr(monto: $monto, fecha : $fecha);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener filtro', data: $filtro_especial);
        }

        $r_isr = (new cat_sat_isr($link))->filtro_and(filtro: $filtro, filtro_especial: $filtro_especial);
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

}
