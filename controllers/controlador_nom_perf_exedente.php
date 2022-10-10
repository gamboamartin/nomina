<?php

namespace gamboamartin\nomina\controllers;

use gamboamartin\errores\errores;
use gamboamartin\system\links_menu;
use gamboamartin\system\system;
use gamboamartin\template\html;
use html\nom_perf_exedente_html;
use html\nom_tipo_incidencia_html;
use models\nom_perf_exedente;
use models\nom_tipo_incidencia;
use PDO;
use stdClass;

class controlador_nom_perf_exedente extends system {

    public function __construct(PDO $link, html $html = new \gamboamartin\template_1\html(),
                                stdClass $paths_conf = new stdClass()){
        $modelo = new nom_perf_exedente(link: $link);
        $html_ = new nom_perf_exedente_html(html: $html);
        $obj_link = new links_menu($this->registro_id);
        parent::__construct(html:$html_, link: $link,modelo:  $modelo, obj_link: $obj_link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Excedente';
    }
}
