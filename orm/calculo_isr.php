<?php
namespace models;
use gamboamartin\errores\errores;

class calculo_isr{
    private errores $error;

    public function __construct(){
        $this->error = new errores();
    }

    /**
     * Genera el filtro para la obtencion de tablas de isr
     * @param float|int $monto Monto gravable de nomina
     * @param string $fecha Fecha de nomina
     * @return array
     * @version 0.110.11
     */
    public function filtro_especial_isr(float|int $monto, string $fecha = ''): array
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

}
