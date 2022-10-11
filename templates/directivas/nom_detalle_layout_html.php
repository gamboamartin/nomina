<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use models\nom_detalle_layout;
use PDO;
use stdClass;

class nom_detalle_layout_html extends html_controler {

    public function select_nom_detalle_layout_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                         bool $disabled = false, array $filtro = array()): array|string
    {
        $modelo = new nom_detalle_layout(link: $link);

        $select = $this->select_catalogo(cols: $cols, con_registros: $con_registros, id_selected: $id_selected,
            modelo: $modelo, disabled: $disabled, filtro: $filtro, label: 'Detalle Layout', required: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


}
