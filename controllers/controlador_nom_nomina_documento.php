<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */

namespace gamboamartin\nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\nomina\models\nom_nomina_documento;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\nom_nomina_documento_html;
use PDO;
use stdClass;

class controlador_nom_nomina_documento extends system
{
    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new nom_nomina_documento(link: $link);
        $html_ = new nom_nomina_documento_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);

        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, paths_conf: $paths_conf);
    }

    public function get_documentos_nomina(bool $header, bool $ws = true): array|stdClass
    {
        $keys['nom_nomina'] = array('id', 'descripcion', 'codigo', 'codigo_bis');

        $salida = $this->get_out(header: $header, keys: $keys, ws: $ws);
        if (errores::$error) {
            return $this->retorno_error(mensaje: 'Error al generar salida', data: $salida, header: $header, ws: $ws);
        }

        return $salida;
    }


}
