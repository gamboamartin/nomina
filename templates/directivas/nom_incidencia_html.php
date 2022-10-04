<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use models\nom_incidencia;
use PDO;
use stdClass;

class nom_incidencia_html extends html_controler {
    public function select_nom_incidencia_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                         bool $disabled = false, array $filtro = array()): array|string
    {
        $modelo = new nom_incidencia(link: $link);

        $select = $this->select_catalogo(cols: $cols, con_registros: $con_registros, id_selected: $id_selected,
            modelo: $modelo, disabled: $disabled, filtro: $filtro, label: 'Incidencia', required: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


}
