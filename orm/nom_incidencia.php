<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_incidencia extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_tipo_incidencia'=>$tabla,'em_empleado'=>$tabla);
        $campos_obligatorios = array();

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
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
            $this->registro['descripcion'] = $this->registro['n_dias'].' - '.
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

    public function incidencias_por_tipo(int $em_empleado_id, int $nom_tipo_incidencia_id){
        $filtro['em_empleado.id'] = $em_empleado_id;
        $filtro['nom_tipo_incidencia.id'] = $nom_tipo_incidencia_id;
        $campos['n_dias'] = 'nom_incidencia.n_dias';

        $n_dias = (new nom_incidencia($this->link))->suma(campos: $campos, filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener incidencias', data: $n_dias);
        }

        return $n_dias['n_dias'];
    }
}