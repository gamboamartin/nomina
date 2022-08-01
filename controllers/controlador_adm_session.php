<?php
/**
 * @author Martin Gamboa Vazquez
 * @version 1.0.0
 * @created 2022-05-14
 * @final En proceso
 *
 */
namespace gamboamartin\nomina\controllers;

use config\generales;
use gamboamartin\errores\errores;
use JsonException;
use stdClass;

class controlador_adm_session extends \gamboamartin\controllers\controlador_adm_session {
    public bool $existe_msj = false;
    public string $include_menu = '';
    public string $mensaje_html = '';

    /**
     * Funcion de controlador donde se ejecutaran siempre que haya un acceso denegado
     * @version 1.0.0
     * @param bool $header Si header es true cualquier error se mostrara en el html y cortara la ejecucion del sistema
     *              En false retornara un array si hay error y un string con formato html
     * @param bool $ws Si ws es true retornara el resultado en formato de json
     * @return array vacio siempre
     */
    public function denegado(bool $header, bool $ws = false): array
    {

        $this->include_menu = (new generales())->path_base;
        $this->include_menu .= 'templates/inicio.php';

        return array();

    }

    /**
     * Funcion de controlador donde se ejecutaran los elementos necesarios para poder mostrar el inicio en
     *      session/inicio
     * @version 1.0.0
     * @param bool $aplica_template Si aplica template buscara el header de la base
     *              No recomendado para vistas ajustadas como esta
     * @param bool $header Si header es true cualquier error se mostrara en el html y cortara la ejecucion del sistema
     *              En false retornara un array si hay error y un string con formato html
     * @param bool $ws Si ws es true retornara el resultado en formato de json
     * @return string|array string = html array = error
     * @throws JsonException si hay error en forma ws
     */
    public function inicio(bool $aplica_template = false, bool $header = true, bool $ws = false): string|array
    {

        $template =  parent::inicio($aplica_template, false); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje:  'Error al generar template',data: $template, header: $header, ws: $ws);
        }

        $this->include_menu = (new generales())->path_base;
        $this->include_menu .= 'templates/inicio.php';

        return $template;
    }

    /**
     * Funcion de controlador donde se ejecutaran los elementos necesarios para la asignacion de datos de logueo
     * @param bool $header Si header es true cualquier error se mostrara en el html y cortara la ejecucion del sistema
     *              En false retornara un array si hay error y un string con formato html
     * @param bool $ws Si ws es true retornara el resultado en formato de json
     * @param string $accion_header
     * @param string $seccion_header
     * @return array string = html array = error
     *
     */
    public function loguea(bool $header, bool $ws = false, string $accion_header = 'login', string $seccion_header = 'adm_session'): array
    {
        $loguea = parent::loguea(header: true,accion_header:  $accion_header,
            seccion_header:  $seccion_header); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje:  'Error al loguear',data: $loguea, header: $header, ws: $ws);
        }
        return $loguea;
    }


    /**
     * Funcion de controlador donde se ejecutaran los elementos de session/login
     *
     * @param bool $header Si header es true cualquier error se mostrara en el html y cortara la ejecucion del sistema
     *              En false retornara un array si hay error y un string con formato html
     * @param bool $ws Si ws es true retornara el resultado en formato de json
     * @return string|array string = html array = error
     */
    public function login(bool $header = true, bool $ws = false): stdClass|array
    {
        $login = parent::login($header, $ws); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->retorno_error(mensaje:  'Error al generar template',data: $login, header: $header, ws: $ws);
        }

        $this->mensaje_html = '';
        if(isset($_GET['mensaje']) && $_GET['mensaje'] !==''){
            $mensaje = trim($_GET['mensaje']);
            if($mensaje !== ''){
                $this->mensaje_html = $mensaje;
                $this->existe_msj = true;
            }
        }

        $this->include_menu .= 'templates/login.php';

        return $login;

    }



}
