<?php

namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
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
     * @param PDO $link
     * @param int $nom_nomina_id Identificador de nomina
     * @return array|stdClass
     * @version 0.194.6
     */
    public function fc_factura(PDO $link, int $nom_nomina_id): array|stdClass
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


}
