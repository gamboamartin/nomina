<?php
namespace models;

use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_percepcion extends nominas_confs {

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'cat_sat_tipo_percepcion_nom'=>$tabla);
        $campos_obligatorios = array('cat_sat_tipo_percepcion_nom_id');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function registro_estado_subsidio(): array|stdClass
    {
        /**
         * refactorizar
         */
        $QUERY = "SELECT * FROM nom_percepcion WHERE aplica_subsidio = 'activo' LIMIT 1";
        $r_nom_percepcion = $this->ejecuta_consulta($QUERY);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al buscar percepcion subsidio', data: $r_nom_percepcion);
        }
        return $r_nom_percepcion;
    }

    public function get_aplica_septimo_dia(): array|stdClass
    {
        $filtro['nom_percepcion.aplica_septimo_dia'] = 'activo';
        $r_nom_percepcion = $this->filtro_and(filtro: $filtro,limit: 1);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al buscar percepcion subsidio', data: $r_nom_percepcion);
        }
        return $r_nom_percepcion;
    }

    public function get_aplica_isn(): array|stdClass
    {
        $filtro['nom_percepcion.aplica_isn'] = 'activo';
        $r_nom_percepcion = $this->filtro_and(filtro: $filtro,limit: 1);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al buscar percepcion subsidio', data: $r_nom_percepcion);
        }
        return $r_nom_percepcion;
    }

    public function get_aplica_prima_dominical(): array|stdClass
    {
        $filtro['nom_percepcion.aplica_prima_dominical'] = 'activo';
        $r_nom_percepcion = $this->filtro_and(filtro: $filtro,limit: 1);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al buscar percepcion subsidio', data: $r_nom_percepcion);
        }
        return $r_nom_percepcion;
    }

    public function id_registro_estado_subsidio(mixed $registro): int
    {
        /**
         * REFACTORIZAR
         */
        if ($registro->n_registros === 0) {
            return -1;
        }

        return $registro->registros[0]['id'];;
    }
}