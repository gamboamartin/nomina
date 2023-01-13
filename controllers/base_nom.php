<?php
namespace gamboamartin\nomina\controllers;
use gamboamartin\errores\errores;
use gamboamartin\system\system;
use gamboamartin\nomina\models\nom_par_deduccion;
use gamboamartin\nomina\models\nom_par_otro_pago;
use gamboamartin\nomina\models\nom_par_percepcion;
use stdClass;

class base_nom extends system {
    public int $nom_nomina_id = -1;
    public stdClass $deducciones;
    public stdClass $otros_pagos;
    public stdClass $percepciones;

    private function data_partida_btn(array $partida, string $tipo): array
    {
        $key_id = 'nom_par_'.$tipo.'_id';
        $params[$key_id] = $partida[$key_id];

        $btn_elimina = $this->html_base->button_href(accion: 'elimina_'.$tipo.'_bd', etiqueta: 'Elimina',
            registro_id: $this->registro_id, seccion: 'nom_nomina', style: 'danger', params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_elimina);
        }
        $partida['link_elimina'] = $btn_elimina;

        $btn_modifica = $this->html_base->button_href(accion: 'modifica_'.$tipo, etiqueta: 'Modifica',
            registro_id: $this->registro_id, seccion: 'nom_nomina', style: 'warning', params: $params);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al generar btn', data: $btn_modifica);
        }
        $partida['link_modifica'] = $btn_modifica;

        return $partida;
    }

    private function deducciones_by_nomina(): array|stdClass
    {
        $r_deducciones = (new nom_par_deduccion($this->link))->deducciones_by_nomina(nom_nomina_id: $this->nom_nomina_id);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener deducciones', data: $r_deducciones);
        }

        foreach ($r_deducciones->registros as $indice => $deduccion) {

            $deduccion = $this->data_partida_btn(partida: $deduccion, tipo: 'deduccion');
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al asignar botones', data: $deduccion);
            }

            $r_deducciones->registros[$indice] = $deduccion;
        }
        return $r_deducciones;
    }

    private function otros_pagos_by_nomina(): array|stdClass
    {
        $r_otros_pagos = (new nom_par_otro_pago($this->link))->otros_pagos_by_nomina(nom_nomina_id: $this->nom_nomina_id);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener otros pagos', data: $r_otros_pagos);
        }
        foreach ($r_otros_pagos->registros as $indice => $otro_pago) {

            $otro_pago = $this->data_partida_btn(partida: $otro_pago, tipo: 'otro_pago');
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al asignar botones', data: $otro_pago);
            }
            $r_otros_pagos->registros[$indice] = $otro_pago;
        }
        return $r_otros_pagos;
    }

    protected function partidas(): array|stdClass
    {
        $r_deducciones = $this->deducciones_by_nomina();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener deducciones', data: $r_deducciones);
        }

        $this->deducciones = $r_deducciones;

        $r_percepciones = $this->percepciones_by_nomina();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener percepciones', data: $r_percepciones);
        }

        $this->percepciones = $r_percepciones;

        $r_otros_pagos = $this->otros_pagos_by_nomina();
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener otros pagos', data: $r_otros_pagos);
        }
        $this->otros_pagos = $r_otros_pagos;

        $data = new stdClass();
        $data->percepciones = $r_percepciones;
        $data->deducciones = $r_deducciones;
        $data->otros_pagos = $r_otros_pagos;

        return $data;

    }

    private function percepciones_by_nomina(): array|stdClass
    {
        $percepciones = (new nom_par_percepcion($this->link))->percepciones_by_nomina(nom_nomina_id: $this->nom_nomina_id);
        if (errores::$error) {
            return $this->errores->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }

        foreach ($percepciones->registros as $indice => $percepcion) {

            $percepcion = $this->data_partida_btn(partida: $percepcion,tipo: 'percepcion');
            if (errores::$error) {
                return $this->errores->error(mensaje: 'Error al asignar botones', data: $percepcion);
            }
            $percepciones->registros[$indice] = $percepcion;
        }
        return $percepciones;
    }
}
