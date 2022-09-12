<?php
namespace html;
use gamboamartin\system\html_controler;
use gamboamartin\system\system;
use stdClass;

class base_nominas extends html_controler{

    protected function inputs_percepcion_partida(system $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->nom_nomina_id = $inputs->selects->nom_nomina_id;
        $controler->inputs->select->nom_percepcion_id = $inputs->selects->nom_percepcion_id;
        $controler->inputs->importe_gravado = $inputs->texts->importe_gravado;
        $controler->inputs->importe_exento = $inputs->texts->importe_exento;
        return $controler->inputs;
    }

}
