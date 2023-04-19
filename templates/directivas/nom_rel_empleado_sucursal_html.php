<?php
namespace html;

use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_rel_empleado_sucursal;
use gamboamartin\system\html_controler;
use gamboamartin\nomina\models\nom_rel_empleado_sucursal;
use PDO;
use stdClass;


class nom_rel_empleado_sucursal_html extends html_controler {

    private function asigna_inputs(controlador_nom_rel_empleado_sucursal $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->em_empleado_id = $inputs->selects->em_empleado_id;
        $controler->inputs->select->com_sucursal_id = $inputs->selects->com_sucursal_id;
        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_rel_empleado_sucursal $controler, PDO $link): array|stdClass
    {
        $keys_selects = array();
        $inputs = $this->init_alta(keys_selects: $keys_selects, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function genera_inputs_modifica(controlador_nom_rel_empleado_sucursal $controler, PDO $link,
                                            stdClass                      $params = new stdClass()): array|stdClass
    {
        $inputs = $this->init_modifica(link: $link, row_upd: $controler->row_upd, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_inputs(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    protected function init_alta(array $keys_selects, PDO $link): array|stdClass
    {
        $keys_selects = array();
        $selects = $this->selects_alta(keys_selects: $keys_selects, link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar selects',data:  $selects);
        }

        $texts = $this->texts_alta(row_upd: new stdClass(), value_vacio: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar texts',data:  $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->selects = $selects;
        $alta_inputs->texts = $texts;

        return $alta_inputs;
    }

    private function init_modifica(PDO $link, stdClass $row_upd, stdClass $params = new stdClass()): array|stdClass
    {
        $selects = $this->selects_modifica(link: $link, row_upd: $row_upd);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar selects',data:  $selects);
        }

        $texts = $this->texts_alta(row_upd: $row_upd, value_vacio: false, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar texts',data:  $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->texts = $texts;
        $alta_inputs->selects = $selects;
        return $alta_inputs;
    }

    public function inputs_nom_rel_empleado_sucursal(controlador_nom_rel_empleado_sucursal $controlador,
                                            stdClass                      $params = new stdClass()): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    protected function selects_alta(array $keys_selects, PDO $link): array|stdClass
    {
        $selects = new stdClass();

        $select = (new em_empleado_html(html:$this->html_base))->select_em_empleado_id(
            cols: 6, con_registros:true, id_selected:-1,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new com_sucursal_html(html:$this->html_base))->select_com_sucursal_id(
            cols: 6, con_registros: true, id_selected: -1, link: $link, disabled: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->com_sucursal_id = $select;

        return $selects;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $selects = new stdClass();

        $select = (new em_empleado_html(html:$this->html_base))->select_em_empleado_id(
            cols: 6, con_registros:true, id_selected:$row_upd->em_empleado_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new com_sucursal_html(html:$this->html_base))->select_com_sucursal_id(
            cols: 6, con_registros: true, id_selected: $row_upd->com_sucursal_id, link: $link, disabled: false);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->com_sucursal_id = $select;

        return $selects;
    }

    public function select_nom_rel_empleado_sucursal_id(int $cols, bool $con_registros, int $id_selected, PDO $link,
                                           bool $disabled = false, bool $required = false): array|string
    {
        $modelo = new nom_rel_empleado_sucursal(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo, disabled: $disabled,label: 'Rel Empleado Sucursal',required: $required);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }
}
