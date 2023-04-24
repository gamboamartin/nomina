<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 0.48.13
 * @verclass 1.0.0
 * @created 2022-07-25
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;


use PDO;
use stdClass;


class controlador_dp_cp extends \controllers\controlador_dp_cp {

    public function __construct(PDO $link, stdClass $paths_conf = new stdClass()){


        parent::__construct(link: $link, paths_conf: $paths_conf);

        $this->titulo_lista = 'Codigos Postales';

        $this->lista_get_data = true;
    }


}
