<?php
namespace models;

use gamboamartin\template\html;
use gamboamartin\errores\errores;
use html\nom_percepcion_html;
use PDO;
use stdClass;

class nom_percepcion extends nominas_confs {

    public html $html_base;

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html()){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false,'cat_sat_tipo_percepcion_nom'=>$tabla);
        $campos_obligatorios = array();
        $this->html_base = $html;

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    public function alta_bd(): array|stdClass
    {
        if (isset($this->registro['aplica_subsidio'])) {
            $exite = $this->existe_registro_subsidio();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al comprobar el estado de subsidio para percepcion', data: $exite);
            }

            if (is_bool($exite) && $exite === false) {
                return parent::alta_bd();
            }
        }

        return parent::alta_bd();
    }

    public function existe_registro_subsidio(): array|int|bool
    {
        $filtro['nom_percepcion.aplica_subsidio'] = 'activo';

        $r_nom_percepcion = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al buscar percepcion subsidio', data: $r_nom_percepcion);
        }

        if($r_nom_percepcion->n_registros === 0){
            return false;
        }

        if($r_nom_percepcion->n_registros > 0 ){
            return $this->error->error(mensaje: 'Error ya existe un subsidio activo', data: $r_nom_percepcion);
        }

        return (int)$r_nom_percepcion->registros[0]['nom_percepcion'];
    }
}