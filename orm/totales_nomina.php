<?php

namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use JsonException;
use PDO;
use stdClass;

class totales_nomina{

    private errores $error;
    private validacion $validacion;


    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();

    }

    public function total_deducciones_exento(PDO $link, int $nom_nomina_id): float|array
    {
        $campos['total_deducciones_exento'] = 'nom_par_deduccion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_deducciones = (new nom_par_deduccion($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones',data:  $total_deducciones);
        }

        return round($total_deducciones['total_deducciones_exento'],2);
    }

    public function total_deducciones_gravado(PDO $link, int $nom_nomina_id): float|array
    {
        $campos['total_deducciones_gravado'] = 'nom_par_deduccion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_deducciones = (new nom_par_deduccion($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones',data:  $total_deducciones);
        }

        return round($total_deducciones['total_deducciones_gravado'],2);
    }





}
