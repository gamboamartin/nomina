<?php
namespace html;



use gamboamartin\errores\errores;
use gamboamartin\nomina\controllers\controlador_nom_conf_empleado;
use gamboamartin\system\html_controler;
use gamboamartin\template\directivas;
use models\nom_conf_empleado;
use PDO;
use stdClass;

class nom_conf_empleado_html extends html_controler {

    private function asigna_inputs(controlador_nom_conf_empleado $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->em_empleado_id = $inputs->selects->em_empleado_id;
        $controler->inputs->select->em_cuenta_bancaria_id = $inputs->selects->em_cuenta_bancaria_id;
        $controler->inputs->select->nom_conf_nomina_id = $inputs->selects->nom_conf_nomina_id;

        return $controler->inputs;
    }

    private function asigna_modifica(controlador_nom_conf_empleado $controler, stdClass $inputs): array|stdClass
    {
        $controler->inputs->select = new stdClass();
        $controler->inputs->select->em_empleado_id = $inputs->selects->em_empleado_id;
        $controler->inputs->select->em_cuenta_bancaria_id = $inputs->selects->em_cuenta_bancaria_id;
        $controler->inputs->select->nom_conf_nomina_id = $inputs->selects->nom_conf_nomina_id;
        $controler->inputs->descripcion = $inputs->texts->descripcion;

        return $controler->inputs;
    }

    public function genera_inputs_alta(controlador_nom_conf_empleado $controler, array $keys_selects, PDO $link): array|stdClass
    {
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

    private function genera_inputs_modifica(controlador_nom_conf_empleado $controler,PDO $link,
                                            stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->init_modifica(link: $link, row_upd: $controler->row_upd, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);

        }
        $inputs_asignados = $this->asigna_modifica(controler:$controler, inputs: $inputs);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar inputs',data:  $inputs_asignados);
        }

        return $inputs_asignados;
    }

    private function init_alta(array $keys_selects, PDO $link): array|stdClass
    {
        $selects = $this->selects_alta(keys_selects: $keys_selects,link: $link);
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

        $texts = $this->texts_modifica(row_upd: $row_upd, value_vacio: false, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar texts',data:  $texts);
        }

        $alta_inputs = new stdClass();
        $alta_inputs->texts = $texts;
        $alta_inputs->selects = $selects;
        return $alta_inputs;
    }

    public function inputs_nom_conf_empleado(controlador_nom_conf_empleado $controlador,
                                       stdClass $params = new stdClass()): array|stdClass
    {
        $inputs = $this->genera_inputs_modifica(controler: $controlador,
            link: $controlador->link, params: $params);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar inputs',data:  $inputs);
        }
        return $inputs;
    }

    private function selects_modifica(PDO $link, stdClass $row_upd): array|stdClass
    {
        $nom_conf_empleado = (new nom_conf_empleado(link: $link))->registro( registro_id: $row_upd->id,
            columnas: array('em_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener los registros de configuracion',
                data: $nom_conf_empleado);
        }

        $selects = new stdClass();

        $filtro['em_empleado.id'] = $nom_conf_empleado['em_empleado_id'];
        $select = (new em_empleado_html(html:$this->html_base))->select_em_empleado_id(cols: 8, con_registros:true,
            id_selected:$nom_conf_empleado['em_empleado_id'],link: $link,filtro: $filtro,disabled: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->em_empleado_id = $select;

        $select = (new em_cuenta_bancaria_html(html: $this->html_base))->select_em_cuenta_bancaria_id(
            cols: 6, con_registros: true, id_selected: $row_upd->em_cuenta_bancaria_id,
            link: $link,filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->em_cuenta_bancaria_id = $select;

        $select = (new nom_conf_nomina_html(html:$this->html_base))->select_nom_conf_nomina_id(
            cols: 6, con_registros:true, id_selected:$row_upd->nom_conf_nomina_id,link: $link);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select',data:  $select);
        }
        $selects->nom_conf_nomina_id = $select;

        return $selects;
    }


    public function select_nom_conf_empleado_id(int $cols, bool $con_registros, int|null $id_selected, PDO $link): array|string
    {
        $valida = (new directivas(html:$this->html_base))->valida_cols(cols:$cols);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar cols', data: $valida);
        }
        if(is_null($id_selected)){
            $id_selected = -1;
        }

        $modelo = new nom_conf_empleado(link: $link);

        $select = $this->select_catalogo(cols:$cols,con_registros:$con_registros,id_selected:$id_selected,
            modelo: $modelo,label: 'Configuracion Empleado',required: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al generar select', data: $select);
        }
        return $select;
    }


    private function texts_modifica(stdClass $row_upd, bool $value_vacio, stdClass $params = new stdClass()):
    array|stdClass
    {
        $texts = new stdClass();

        $in_descripcion = $this->input_descripcion(cols: 8, row_upd: $row_upd, value_vacio: $value_vacio);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar input', data: $in_descripcion);
        }
        $texts->descripcion = $in_descripcion;

        return $texts;
    }

}
