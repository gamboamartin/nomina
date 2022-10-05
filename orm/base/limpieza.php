<?php
namespace models\base;
use base\controller\controler;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class limpieza{
    private errores $error;
    private validacion $validacion;
    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();
    }

    /**
     * Inicializa la descripcion y el codigo de una empresa en alta bd
     * @param array $registro Registro en ejecucion
     * @version 0.56.14
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-26 09:58
     * @return array
     */
    private function init_data_base_org_empresa(array $registro): array
    {
        if(!isset($registro['descripcion'])){
            $registro['descripcion'] = $registro['razon_social'];
        }
        if(!isset($this->registro['codigo_bis'])){
            $registro['codigo_bis'] = $registro['rfc'];
        }
        if(!isset($this->registro['descripcion_select'])){
            $registro['descripcion_select'] = $registro['descripcion'];
        }
        if(!isset($this->registro['alias'])){
            $registro['alias'] = $registro['descripcion'];
        }
        return $registro;
    }

    private function init_data_ubicacion(controler $controler, stdClass $org_empresa): stdClass
    {
        $controler->row_upd->dp_pais_id = $org_empresa->dp_pais_id;
        $controler->row_upd->dp_estado_id = $org_empresa->dp_estado_id;
        $controler->row_upd->dp_municipio_id = $org_empresa->dp_municipio_id;
        $controler->row_upd->dp_cp_id = $org_empresa->dp_cp_id;
        $controler->row_upd->dp_colonia_postal_id = $org_empresa->dp_colonia_postal_id;
        $controler->row_upd->dp_calle_pertenece_id = $org_empresa->dp_calle_pertenece_id;
        $controler->row_upd->dp_calle_pertenece_entre1_id = $org_empresa->org_empresa_dp_calle_pertenece_entre1_id;
        $controler->row_upd->dp_calle_pertenece_entre2_id = $org_empresa->org_empresa_dp_calle_pertenece_entre2_id;
        $controler->row_upd->org_tipo_empresa_id = $org_empresa->org_tipo_empresa_id;

        return $controler->row_upd;
    }

    private function init_foraneas(array $keys_foraneas, stdClass $org_empresa): stdClass
    {
        foreach ($keys_foraneas as $campo){
            if(is_null($org_empresa->$campo)){
                $org_empresa->$campo = '-1';
            }
        }
        return $org_empresa;
    }

    public function init_modifica_org_empresa(controler $controler): array|stdClass
    {
        if(!isset($controler->row_upd)){
            $controler->row_upd = new stdClass();
        }
        if(!isset($controler->row_upd->cat_sat_regimen_fiscal_id)){
            $controler->row_upd->cat_sat_regimen_fiscal_id = -1;
        }


        $org_empresa = $controler->modelo->registro(registro_id: $controler->registro_id,retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro',data:  $org_empresa);
        }


        $init = $this->init_upd_org_empresa(controler: $controler,org_empresa:  $org_empresa);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa datos',data:  $init);
        }
        return $init;
    }

    public function init_org_empresa_alta_bd(array $registro): array
    {
        $keys = array('razon_social','rfc');
        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $registro = $this->init_data_base_org_empresa(registro:$registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar registro', data: $registro);
        }


        $registro = $this->limpia_foraneas_org_empresa(registro:$registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al limpiar registro', data: $registro);
        }

        return $registro;
    }

    private function init_upd_org_empresa(controler $controler, stdClass $org_empresa): array|stdClass
    {
        $keys_foraneas = array('dp_pais_id','dp_estado_id','dp_municipio_id','dp_cp_id','dp_colonia_postal_id',
            'dp_calle_pertenece_id','org_empresa_dp_calle_pertenece_entre1_id',
            'org_empresa_dp_calle_pertenece_entre2_id','org_tipo_empresa_id');


        $init = $this->init_foraneas(keys_foraneas: $keys_foraneas,org_empresa:  $org_empresa);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa datos',data:  $init);

        }


        $init = $this->init_data_ubicacion(controler: $controler,org_empresa:  $org_empresa);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializa datos',data:  $init);
        }
        return $init;
    }



    /**
     * Limpia la llaves foraneas de la empresa a dar de alta
     * @param array $registro Registro en ejecucion
     * @version 0.58.14
     * @verfuncion 0.1.0
     * @author mgamboa
     * @fecha 2022-07-26 10:18
     * @return array
     */
    private function limpia_foraneas_org_empresa(array $registro): array
    {
        if(isset($registro['cat_sat_regimen_fiscal_id']) && (int)$registro['cat_sat_regimen_fiscal_id']===-1){
            unset($registro['cat_sat_regimen_fiscal_id']);
        }
        if(isset($registro['dp_calle_pertenece_id']) && (int)$registro['dp_calle_pertenece_id']===-1){
            unset($registro['dp_calle_pertenece_id']);
        }
        if(isset($registro['dp_calle_pertenece_entre2_id']) && (int)$registro['dp_calle_pertenece_entre2_id']===-1){
            unset($registro['dp_calle_pertenece_entre2_id']);
        }
        if(isset($registro['dp_calle_pertenece_entre1_id']) && (int)$registro['dp_calle_pertenece_entre1_id']===-1){
            unset($registro['dp_calle_pertenece_entre1_id']);
        }
        return $registro;
    }

    public function maqueta_row_abono_base(array $anticipo, int $nom_nomina_id): array
    {
        $datos['descripcion'] = $anticipo['em_anticipo_descripcion'].$anticipo['em_anticipo_id'];
        $datos['codigo'] = $anticipo['em_anticipo_codigo'].$anticipo['em_tipo_descuento_codigo'].$nom_nomina_id;
        $datos['descripcion_select'] = strtoupper($datos['descripcion']);
        $datos['codigo_bis'] = strtoupper($datos['codigo']);
        $datos['alias'] = $datos['codigo'].$datos['descripcion'];
        return $datos;
    }

}
