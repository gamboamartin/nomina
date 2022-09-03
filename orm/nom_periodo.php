<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_periodo extends nominas_confs {

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, );
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function alta_bd(): array|stdClass
    {

        $r_alta_bd = parent::alta_bd();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar registro', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

    public function get_empleados(int $im_registro_patronal_id){
        $filtro['im_registro_patronal.id'] = $im_registro_patronal_id;

        $r_empleados = (new em_empleado($this->link))->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener registros', data: $r_empleados);
        }

        return $r_empleados->registros;
    }

    public function genera_registro_nomina(int $nom_periodo_id) : array|stdClass{

        $nom_periodo = $this->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al al obtener periodo', data: $nom_periodo);
        }


        $registros_empleados = $this->get_empleados(im_registro_patronal_id: $nom_periodo['nom_periodo_im_registro_patronal_id']);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al generar registros de factura', data: $registros_empleados);
        }


        foreach ($registros_empleados as $empleado) {
            $nomina_empleado = $this->genera_registro_nomina_empleado($empleado, $nom_periodo);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al maquetar nomina del empleado', data: $nomina_empleado);
            }

            $alta_empleado = $this->alta_nomina_empleado($nomina_empleado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al dar de alta la nomina del empleado', data: $alta_empleado);
            }

        }

        return array();
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

    private function genera_registro_nomina_empleado(mixed $em_empleado, mixed $nom_periodo) : array{



        $registros['im_registro_patronal_id'] = $em_empleado['im_registro_patronal_id'];
        $registros['em_empleado_id'] = $em_empleado['em_empleado_id'];
        $registros['nom_conf_empleado_id'] = 1;
        $registros['em_cuenta_bancaria_id'] = 1;
        $registros['folio'] = rand();
        $registros['fecha'] = $nom_periodo['nom_periodo_fecha_pago'];
        $registros['cat_sat_tipo_nomina_id'] = 1;
        $registros['cat_sat_periodicidad_pago_nom_id'] = $nom_periodo['nom_periodo_cat_sat_periodicidad_pago_nom_id'];
        $registros['fecha_pago'] =$nom_periodo['nom_periodo_fecha_pago'];
        $registros['fecha_inicial_pago'] = $nom_periodo['nom_periodo_fecha_inicial_pago'];
        $registros['fecha_final_pago'] = $nom_periodo['nom_periodo_fecha_final_pago'];
        $registros['num_dias_pagados'] = 15;
        $registros['descuento'] = 0;

        return $registros;
    }



}