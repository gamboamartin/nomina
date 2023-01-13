<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\system\html_controler;
use gamboamartin\nomina\models\nom_tipo_concepto_imss;
use PDO;
use stdClass;


class nom_tipo_concepto_imss_html extends html_controler {

    public function select_nom_tipo_concepto_imss_id(int $cols,bool $con_registros,int $id_selected, PDO $link): array|string
    {
        $modelo = new nom_tipo_concepto_imss($link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,label: 'Tipo Concepto',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }
}
