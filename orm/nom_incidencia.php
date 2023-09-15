<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_incidencia extends modelo
{

    public function __construct(PDO $link)
    {
        $tabla = 'nom_incidencia';
        $columnas = array($tabla => false, 'nom_tipo_incidencia' => $tabla, 'em_empleado' => $tabla);
        $campos_obligatorios = array();

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;

    }

    public function alta_bd(): array|stdClass
    {
        if (!isset($this->registro['codigo'])) {
            $this->registro['codigo'] = $this->registro['nom_tipo_incidencia_id'] . ' - ' .
                $this->registro['em_empleado_id'] . ' - ' . rand();
        }
        if (!isset($this->registro['codigo_bis'])) {
            $this->registro['codigo_bis'] = $this->registro['codigo'];
        }

        if (!isset($this->registro['descripcion'])) {
            $this->registro['descripcion'] = $this->registro['n_dias'] . ' - ' .
                $this->registro['nom_tipo_incidencia_id'] . ' - ' . $this->registro['em_empleado_id'] . ' - ' . rand();
        }
        if (!isset($this->registro['descripcion_select'])) {
            $this->registro['descripcion_select'] = $this->registro['descripcion'];
        }

        $r_alta = parent::alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dar de alta incidencias', data: $r_alta);
        }

        return $r_alta;
    }

    public function incidencias_por_tipo(int $em_empleado_id, int $nom_tipo_incidencia_id)
    {
        $filtro['em_empleado.id'] = $em_empleado_id;
        $filtro['nom_tipo_incidencia.id'] = $nom_tipo_incidencia_id;
        $campos['n_dias'] = 'nom_incidencia.n_dias';

        $n_dias = (new nom_incidencia($this->link))->suma(campos: $campos, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener incidencias', data: $n_dias);
        }

        return $n_dias['n_dias'];
    }

    public function get_incidencias_faltas(int $em_empleado_id, int $nom_periodo_id)
    {
        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el periodo', data: $periodo);
        }

        $filtro['nom_incidencia.em_empleado_id'] = $em_empleado_id;
        $filtro['nom_incidencia.nom_periodo_id'] = $nom_periodo_id;
        $filtro['nom_tipo_incidencia.es_falta'] = 'activo';
        /*$filtro_rango['nom_incidencia.fecha_incidencia']['valor1'] = $periodo['nom_periodo_fecha_inicial_pago'];
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor2'] = $periodo['nom_periodo_fecha_final_pago'];*/

        $incidencias = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el las incidencias', data: $incidencias);
        }

        return $incidencias;
    }

    public function get_incidencias_prima_dominical(int $em_empleado_id, int $nom_periodo_id)
    {
        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el periodo', data: $periodo);
        }

        $filtro['nom_incidencia.em_empleado_id'] = $em_empleado_id;
        $filtro['nom_incidencia.nom_periodo_id'] = $nom_periodo_id;
        $filtro['nom_tipo_incidencia.es_prima_dominical'] = 'activo';
        /*$filtro_rango['nom_incidencia.fecha_incidencia']['valor1'] = $periodo['nom_periodo_fecha_inicial_pago'];
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor2'] = $periodo['nom_periodo_fecha_final_pago'];*/

        $incidencias = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el las incidencias', data: $incidencias);
        }

        return $incidencias;
    }

    public function get_incidencias_dia_festivo_laborado(int $em_empleado_id, int $nom_periodo_id)
    {
        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el periodo', data: $periodo);
        }

        $filtro['nom_incidencia.em_empleado_id'] = $em_empleado_id;
        $filtro['nom_incidencia.nom_periodo_id'] = $nom_periodo_id;
        $filtro['nom_tipo_incidencia.es_dia_festivo_laborado'] = 'activo';
        /*$filtro_rango['nom_incidencia.fecha_incidencia']['valor1'] = $periodo['nom_periodo_fecha_inicial_pago'];
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor2'] = $periodo['nom_periodo_fecha_final_pago'];*/

        $incidencias = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el las incidencias', data: $incidencias);
        }

        return $incidencias;
    }

    public function get_incidencias_dia_descanso(int $em_empleado_id, int $nom_periodo_id)
    {
        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el periodo', data: $periodo);
        }

        $filtro['nom_incidencia.em_empleado_id'] = $em_empleado_id;
        $filtro['nom_incidencia.nom_periodo_id'] = $nom_periodo_id;
        $filtro['nom_tipo_incidencia.es_dia_descanso'] = 'activo';
        /*$filtro_rango['nom_incidencia.fecha_incidencia']['valor1'] = $periodo['nom_periodo_fecha_inicial_pago'];
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor2'] = $periodo['nom_periodo_fecha_final_pago'];*/

        $incidencias = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el las incidencias', data: $incidencias);
        }

        return $incidencias;
    }

    public function get_incidencias_incapacidad(int $em_empleado_id, int $nom_periodo_id)
    {
        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el periodo', data: $periodo);
        }

        $filtro['nom_incidencia.em_empleado_id'] = $em_empleado_id;
        $filtro['nom_incidencia.nom_periodo_id'] = $nom_periodo_id;
        $filtro['nom_tipo_incidencia.es_incapacidad'] = 'activo';
        /*$filtro_rango['nom_incidencia.fecha_incidencia']['valor1'] = $periodo['nom_periodo_fecha_inicial_pago'];
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor2'] = $periodo['nom_periodo_fecha_final_pago'];*/

        $incidencias = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el las incidencias', data: $incidencias);
        }

        return $incidencias;
    }

    public function get_incidencias_vacaciones(int $em_empleado_id, int $nom_periodo_id)
    {
        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el periodo', data: $periodo);
        }

        $filtro['nom_incidencia.em_empleado_id'] = $em_empleado_id;
        $filtro['nom_incidencia.nom_periodo_id'] = $nom_periodo_id;
        $filtro['nom_tipo_incidencia.es_vacaciones'] = 'activo';
        /*$filtro_rango['nom_incidencia.fecha_incidencia']['valor1'] = $periodo['nom_periodo_fecha_inicial_pago'];
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor2'] = $periodo['nom_periodo_fecha_final_pago'];*/

        $incidencias = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el las incidencias', data: $incidencias);
        }

        return $incidencias;
    }

    public function get_incidencias(int $em_empleado_id, int $nom_periodo_id): array|stdClass
    {

        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $periodo = (new nom_periodo($this->link))->registro(registro_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el periodo', data: $periodo);
        }

        $filtro['nom_incidencia.em_empleado_id'] = $em_empleado_id;
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor1'] = $periodo['nom_periodo_fecha_inicial_pago'];
        $filtro_rango['nom_incidencia.fecha_incidencia']['valor2'] = $periodo['nom_periodo_fecha_final_pago'];

        $incidencias = $this->filtro_and(filtro: $filtro, filtro_rango: $filtro_rango);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener el las incidencias', data: $incidencias);
        }

        return $incidencias;
    }

    public function total_dias_prima_dominical(int $em_empleado_id, int $nom_periodo_id): array|int
    {

        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $incidencias = $this->get_incidencias_prima_dominical(em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $incidencias);
        }

        $total = 0;
        if ($incidencias->n_registros > 0) {
            foreach ($incidencias->registros as $incidencia) {
                $total += $incidencia['nom_incidencia_n_dias'];
            }
        }

        return $total;
    }

    public function total_dias_aplica_dia_festivo_laborado(int $em_empleado_id, int $nom_periodo_id): array|int
    {

        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $incidencias = $this->get_incidencias_dia_festivo_laborado(em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $incidencias);
        }

        $total = 0;
        if ($incidencias->n_registros > 0) {
            foreach ($incidencias->registros as $incidencia) {
                $total += $incidencia['nom_incidencia_n_dias'];
            }
        }

        return $total;
    }

    public function total_dias_aplica_dia_descanso(int $em_empleado_id, int $nom_periodo_id): array|int
    {

        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $incidencias = $this->get_incidencias_dia_descanso(em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $incidencias);
        }

        $total = 0;
        if ($incidencias->n_registros > 0) {
            foreach ($incidencias->registros as $incidencia) {
                $total += $incidencia['nom_incidencia_n_dias'];
            }
        }

        return $total;
    }

    public function total_dias_vacaciones(int $em_empleado_id, int $nom_periodo_id): array|int
    {

        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $incidencias = $this->get_incidencias_vacaciones(em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $incidencias);
        }

        $total = 0;
        if ($incidencias->n_registros > 0) {
            foreach ($incidencias->registros as $incidencia) {
                $total += $incidencia['nom_incidencia_n_dias'];
            }
        }

        return $total;
    }

    public function total_dias_incidencias_n_dias(int $em_empleado_id, int $nom_periodo_id): array|int
    {

        if ($em_empleado_id <= 0) {
            return $this->error->error(mensaje: 'Error $em_empleado_id es menor a 1', data: $em_empleado_id);
        }

        if ($nom_periodo_id <= 0) {
            return $this->error->error(mensaje: 'Error $nom_periodo_id es menor a 1', data: $nom_periodo_id);
        }

        $total = 0;

        $incidencias = $this->get_incidencias_vacaciones(em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $incidencias);
        }

        if ($incidencias->n_registros > 0){
            foreach ($incidencias->registros as $incidencia){
                $total += $incidencia['nom_incidencia_n_dias'];
            }
        }

        $incidencias = $this->get_incidencias_faltas(em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $incidencias);
        }

        if ($incidencias->n_registros > 0){
            foreach ($incidencias->registros as $incidencia){
                $total += $incidencia['nom_incidencia_n_dias'];
            }
        }

        $incidencias = $this->get_incidencias_incapacidad(em_empleado_id: $em_empleado_id, nom_periodo_id: $nom_periodo_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener las incidencias', data: $incidencias);
        }

        if ($incidencias->n_registros > 0){
            foreach ($incidencias->registros as $incidencia){
                $total += $incidencia['nom_incidencia_n_dias'];
            }
        }


        return $total;
    }


}