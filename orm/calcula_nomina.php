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

    public function calculos(PDO $link, int $nom_nomina_id): array|stdClass
    {
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
