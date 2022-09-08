<?php

namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use PDO;
use stdClass;

class calcula_nomina{

    private errores $error;
    private validacion $validacion;

    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();

    }

    public function calcula_impuestos_netos_por_nomina(PDO $link, int $nom_nomina_id): array|stdClass
    {
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }
        $impuestos = $this->calculos(link:$link, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener impuestos', data: $impuestos);
        }

        $monto_isr_bruto = round($impuestos->isr,2);
        $monto_subsidio_bruto = round($impuestos->subsidio,2);

        $monto_isr_neto = $monto_isr_bruto;
        $monto_subsidio_neto = $monto_subsidio_bruto;

        if($monto_isr_bruto >= $monto_subsidio_bruto){
            $monto_isr_neto = round($monto_isr_bruto-$monto_subsidio_bruto,2);
            $monto_subsidio_neto = 0;

        }
        if($monto_isr_bruto < $monto_subsidio_bruto){
            $monto_isr_neto = 0;
            $monto_subsidio_neto = round($monto_subsidio_bruto-$monto_isr_bruto,2);
        }

        $data = new stdClass();
        $data->isr_neto = $monto_isr_neto;
        $data->subsidio_neto = $monto_subsidio_neto;

        return $data;


    }

    /**
     * Obtiene los calculos de isr y subsidio por nomina
     * @param PDO $link Conexion a la base de datos
     * @param int $nom_nomina_id Nomina en ejecucion
     * @return array|stdClass
     * @version 0.287.9
     */
    public function calculos(PDO $link, int $nom_nomina_id): array|stdClass
    {
        if($nom_nomina_id <=0){
            return  $this->error->error(mensaje: 'Error al obtener registro $nom_nomina_id debe ser mayor a 0',
                data: $nom_nomina_id);
        }
        $isr = (new calculo_isr())->calcula_isr_por_nomina(link: $link,nom_nomina_id:  $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener isr',data:  $isr);
        }

        $subsidio = (new calculo_subsidio())->calcula_subsidio_por_nomina(link: $link,nom_nomina_id:  $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener subsidio',data:  $subsidio);
        }

        $data = new stdClass();
        $data->isr = $isr;
        $data->subsidio = $subsidio;

        return $data;
    }


}
