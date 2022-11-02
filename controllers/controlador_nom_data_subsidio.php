<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */

namespace gamboamartin\nomina\controllers;

use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;


use html\nom_data_subsidio_html;
use models\nom_data_subsidio;


use PDO;
use stdClass;

class controlador_nom_data_subsidio extends system
{
    public stdClass $paths_conf;

    public function __construct(PDO      $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass())
    {
        $modelo = new nom_data_subsidio(link: $link);
        $html_ = new nom_data_subsidio_html(html: $html);
        $obj_link = new links_menu(link: $link, registro_id: $this->registro_id);
        parent::__construct(html: $html_, link: $link, modelo: $modelo, obj_link: $obj_link, paths_conf: $paths_conf);
        $this->titulo_lista = 'Data Subsidio';
        $this->paths_conf = $paths_conf;
    }


}
