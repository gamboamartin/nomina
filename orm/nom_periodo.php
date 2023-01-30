<?php
namespace gamboamartin\nomina\models;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_periodo extends nominas_confs {

    public function __construct(PDO $link){
        $tabla = 'nom_periodo';
        $columnas = array($tabla=>false, 'cat_sat_periodicidad_pago_nom'=>$tabla);
        $campos_obligatorios = array('cat_sat_periodicidad_pago_nom_id','im_registro_patronal_id','nom_tipo_periodo_id',
            'descripcion','descripcion_select');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(): array|stdClass
    {

        $keys = array('codigo','descripcion');

        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        if(!isset($this->registro['codigo_bis'])){
            $this->registro['codigo_bis'] = strtoupper($this->registro['codigo']);
        }

        if(!isset($this->registro['descripcion_select'])){
            $this->registro['descripcion_select'] = strtoupper($this->registro['descripcion']);
        }

        $this->registro = $this->limpia_campos(registro: $this->registro,
            campos_limpiar: array('nom_conf_nomina_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $this->registro);
        }

        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar registro', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

    private function alta_nomina_empleado(mixed $em_empleado) : array|stdClass{
        $modelo = new nom_nomina(link: $this->link);
        $modelo->registro = $em_empleado;

        $r_alta_bd = $modelo->alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar nomina', data: $r_alta_bd);
        }
        return $r_alta_bd;
    }

    public function elimina_bd(int $id): array|stdClass
    {
        $filtro['nom_nomina.nom_periodo_id'] = $id;
        $registros_nomina_periodo = (new nom_nomina($this->link))->filtro_and( filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las nominas ligadas al periodo',
                data: $registros_nomina_periodo);
        }

        foreach ($registros_nomina_periodo->registros as $nomina) {
            $elimina = $this->elimina_nomina_periodo(nomina_id: $nomina['nom_nomina_id']);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al eliminar la nomina ligada al periodo', data: $elimina);
            }
        }

        $r_elimina_bd = parent::elimina_bd(id:$id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al eliminar periodo', data: $r_elimina_bd);
        }


        return $r_elimina_bd;
    }

    private function elimina_nomina_periodo(int $nomina_id) : array|stdClass{
        $elimina_bd = (new nom_nomina(link: $this->link))->elimina_bd($nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al eliminar nomina', data: $elimina_bd);
        }
        return $elimina_bd;
    }

    /**
     * Obtiene un conjunto de empleados de un registro patronal
     * @param int $im_registro_patronal_id Registro patronal integrado
     * @param int $cat_sat_periodicidad_pago_nom_id
     * @return array
     * @version 0.242.7
     */
    public function get_empleados(int $cat_sat_periodicidad_pago_nom_id, int $im_registro_patronal_id): array
    {
        if($cat_sat_periodicidad_pago_nom_id<=0){
            return $this->error->error(mensaje: 'Error $im_registro_patronal_id debe ser mayor a 0',
                data: $im_registro_patronal_id);
        }

        if($im_registro_patronal_id<=0){
            return $this->error->error(mensaje: 'Error $im_registro_patronal_id debe ser mayor a 0',
                data: $im_registro_patronal_id);
        }

        $filtro['im_registro_patronal.id'] = $im_registro_patronal_id;
        $r_empleados = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros', data: $r_empleados);
        }

        $empleados = array();
        foreach ($r_empleados->registros as $empleado){
            $filtro_em['em_empleado.id'] = $empleado['em_empleado_id'];
            $filtro_em['cat_sat_periodicidad_pago_nom.id'] = $cat_sat_periodicidad_pago_nom_id;
            $conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro_em);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener configuracion de empleado', data: $conf_empleado);
            }

            if(isset($conf_empleado->registros[0])){
                $empleados[] = $conf_empleado->registros[0];
            }

        }

        return $empleados;
    }

    public function genera_registro_nomina_excel(int $nom_periodo_id, array $empleados_excel){
        $nom_periodo = $this->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al al obtener periodo', data: $nom_periodo);
        }

        $empleados = array();
        foreach ($empleados_excel as $empleado_excel){
            $filtro['im_registro_patronal.id'] = $nom_periodo['nom_periodo_im_registro_patronal_id'];
            $filtro['em_empleado.codigo'] = $empleado_excel->codigo;
            $registro = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al al obtener registro de empleado', data: $registro);
            }
            if($registro->n_registros > 0){
                $empleados[] = $registro->registros[0];
            }
        }

        $empleados_res = array();
        foreach ($empleados as $empleado){
            $filtro_em['em_empleado.id'] = $empleado['em_empleado_id'];
            $filtro_em['cat_sat_periodicidad_pago_nom.id'] = $nom_periodo['cat_sat_periodicidad_pago_nom_id'];
            $conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro_em);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener configuracion de empleado', data: $conf_empleado);
            }

            if(isset($conf_empleado->registros[0])){
                $empleados_res[] = $conf_empleado->registros[0];
            }
        }

        foreach ($empleados_res as $empleado) {
            foreach ($empleados_excel as $empleado_excel){
                if($empleado_excel->codigo === $empleado['em_empleado_codigo']){
                    $registro_inc['nom_tipo_incidencia_id'] = 1;
                    $registro_inc['em_empleado_id'] = $empleado['em_empleado_id'];
                    $registro_inc['n_dias'] = $empleado_excel->faltas;
                    
                    $nom_incidencia = (new nom_incidencia($this->link))->alta_registro(registro: $registro_inc);
                    if (errores::$error) {
                        return $this->error->error(mensaje: 'Error al dar de alta incidencias', data: $nom_incidencia);
                    }
                }
            }
            $alta_empleado = $this->alta_empleado_periodo(empleado: $empleado, nom_periodo: $nom_periodo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta la nomina del empleado', data: $alta_empleado);
            }
        }

        return array();
    }

    public function genera_registro_nomina(int $nom_periodo_id) : array|stdClass{

        $nom_periodo = $this->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al al obtener periodo', data: $nom_periodo);
        }



        $registros_empleados = $this->get_empleados(
            cat_sat_periodicidad_pago_nom_id: $nom_periodo['cat_sat_periodicidad_pago_nom_id'],
            im_registro_patronal_id: $nom_periodo['nom_periodo_im_registro_patronal_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de factura', data: $registros_empleados);
        }

        foreach ($registros_empleados as $empleado) {
            $alta_empleado = $this->alta_empleado_periodo(empleado: $empleado, nom_periodo: $nom_periodo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta la nomina del empleado', data: $alta_empleado);
            }
        }

        return array();
    }

    public function alta_empleado_periodo(array $empleado, array $nom_periodo): array|stdClass
    {
        $filtro['em_empleado.id'] = $empleado['em_empleado_id'];
        $nom_conf_empleado = (new nom_conf_empleado($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nom_conf_empleado', data: $nom_conf_empleado);
        }

        $nom_conf_empleado_reg['nom_conf_empleado_id'] = 1;
        $nom_conf_empleado_reg['em_cuenta_bancaria_id'] = 1;
        if($nom_conf_empleado->n_registros > 0){
            $nom_conf_empleado_reg = $nom_conf_empleado->registros[0];
        }

        $nomina_empleado = $this->genera_registro_nomina_empleado(em_empleado:$empleado, nom_periodo: $nom_periodo,
            nom_conf_empleado: $nom_conf_empleado_reg);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar nomina del empleado', data: $nomina_empleado);
        }

        $alta_empleado = $this->alta_nomina_empleado($nomina_empleado);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta la nomina del empleado', data: $alta_empleado);
        }

        return $alta_empleado;
    }

    private function genera_registro_nomina_empleado(mixed $em_empleado, mixed $nom_periodo, mixed $nom_conf_empleado) : array{
        $keys = array('im_registro_patronal_id','em_empleado_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys ,registro:  $em_empleado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar em_empleado', data: $valida);
        }

        $keys = array('nom_periodo_fecha_pago','nom_periodo_cat_sat_periodicidad_pago_nom_id',
            'nom_periodo_fecha_inicial_pago','nom_periodo_fecha_final_pago','cat_sat_periodicidad_pago_nom_n_dias',
            'nom_periodo_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys ,registro:  $nom_periodo);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nom_periodo', data: $valida);
        }

        $keys = array('nom_conf_empleado_id','em_cuenta_bancaria_id');
        $valida = $this->validacion->valida_existencia_keys(keys:$keys ,registro:  $nom_conf_empleado);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar nom_conf_empleado', data: $valida);
        }


        $registros['im_registro_patronal_id'] = $em_empleado['im_registro_patronal_id'];
        $registros['em_empleado_id'] = $em_empleado['em_empleado_id'];
        $registros['nom_conf_empleado_id'] = $nom_conf_empleado['nom_conf_empleado_id'];
        $registros['em_cuenta_bancaria_id'] = $nom_conf_empleado['em_cuenta_bancaria_id'];
        $registros['folio'] = rand();
        $registros['fecha'] = $nom_periodo['nom_periodo_fecha_pago'];
        $registros['cat_sat_tipo_nomina_id'] = 1;
        $registros['cat_sat_periodicidad_pago_nom_id'] = $nom_periodo['nom_periodo_cat_sat_periodicidad_pago_nom_id'];
        $registros['fecha_pago'] =$nom_periodo['nom_periodo_fecha_pago'];
        $registros['fecha_inicial_pago'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];
        $registros['fecha_final_pago'] = $nom_periodo['nom_periodo_fecha_final_pago'];
        $registros['num_dias_pagados'] = $nom_periodo['cat_sat_periodicidad_pago_nom_n_dias'];
        $registros['nom_periodo_id'] = $nom_periodo['nom_periodo_id'];
        $registros['descuento'] = 0;

        return $registros;
    }

    private function limpia_campos(array $registro, array $campos_limpiar): array
    {
        foreach ($campos_limpiar as $valor) {
            if (isset($registro[$valor])) {
                unset($registro[$valor]);
            }
        }
        return $registro;
    }

    public function modifica_bd(array $registro, int $id, bool $reactiva = false): array|stdClass
    {

        $r_modifica_bd = parent::modifica_bd(registro: $registro, id: $id, reactiva: $reactiva); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar',data:  $r_modifica_bd);
        }
        return $r_modifica_bd;
    }


}