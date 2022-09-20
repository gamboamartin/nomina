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

    public function total_deducciones(PDO $link, int $nom_nomina_id): float|array
    {
        $exento = $this->total_deducciones_exento(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones exento',data:  $exento);
        }

        $gravado = $this->total_deducciones_gravado(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones gravado',data:  $gravado);
        }

        $total = $exento + $gravado;
        return round($total,2);

    }

    private function total_deducciones_exento(PDO $link, int $nom_nomina_id): float|array
    {
        $campos['total_deducciones_exento'] = 'nom_par_deduccion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_deducciones = (new nom_par_deduccion($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones',data:  $total_deducciones);
        }

        return round($total_deducciones['total_deducciones_exento'],2);
    }

    private function total_deducciones_gravado(PDO $link, int $nom_nomina_id): float|array
    {
        $campos['total_deducciones_gravado'] = 'nom_par_deduccion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_deducciones = (new nom_par_deduccion($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones',data:  $total_deducciones);
        }

        return round($total_deducciones['total_deducciones_gravado'],2);
    }

    public function total_ingreso_bruto(PDO $link, int $nom_nomina_id): float|array
    {
        $total_percepciones = $this->total_percepciones(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones', data: $total_percepciones);
        }

        $total_otros_pagos = $this->total_otros_pagos(link: $link,nom_nomina_id:$nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total otros pagos', data: $total_otros_pagos);
        }

        return $total_percepciones + $total_otros_pagos;
    }

    private function total_otros_pagos(PDO $link, int $nom_nomina_id): float|array
    {
        $exento = $this->total_otros_pagos_exento(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total otros_pagos exento',data:  $exento);
        }

        $gravado = $this->total_otros_pagos_gravado(link: $link,nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total otros_pagos gravado',data:  $gravado);
        }

        $total = $exento + $gravado;
        return round($total,2);

    }

    private function total_otros_pagos_exento(PDO $link,int $nom_nomina_id): float|array
    {
        $campos['total_otros_pagos_gravado'] = 'nom_par_otro_pago.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_percepciones = (new nom_par_otro_pago($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total otros pagos',data:  $total_percepciones);
        }

        return round($total_percepciones['total_otros_pagos_gravado'],2);
    }

    private function total_otros_pagos_gravado(PDO $link,int $nom_nomina_id): float|array
    {
        $campos['total_otros_pagos_gravado'] = 'nom_par_otro_pago.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_otros_pagos = (new nom_par_otro_pago($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total otros pagos',data:  $total_otros_pagos);
        }

        return round($total_otros_pagos['total_otros_pagos_gravado'],2);
    }


    private function total_percepciones(PDO $link, int $nom_nomina_id): float|array
    {
        $exento = $this->total_percepciones_exento(link: $link, nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones exento',data:  $exento);
        }

        $gravado = $this->total_percepciones_gravado(link: $link,nom_nomina_id: $nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total deducciones gravado',data:  $gravado);
        }

        $total = $exento + $gravado;
        return round($total,2);

    }

    private function total_percepciones_exento(PDO $link, int $nom_nomina_id): float|array
    {
        $campos['total_percepciones_gravado'] = 'nom_par_percepcion.importe_exento';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_percepciones = (new nom_par_percepcion($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total percepciones',data:  $total_percepciones);
        }

        return round($total_percepciones['total_percepciones_gravado'],2);
    }

    private function total_percepciones_gravado(PDO $link, int $nom_nomina_id): float|array
    {
        $campos['total_percepciones_gravado'] = 'nom_par_percepcion.importe_gravado';
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $total_percepciones = (new nom_par_percepcion($link))->suma(campos: $campos,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total percepciones',data:  $total_percepciones);
        }

        return round($total_percepciones['total_percepciones_gravado'],2);
    }





}
