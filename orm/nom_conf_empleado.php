<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_conf_empleado extends modelo{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_empleado';
        $columnas = array($tabla=>false,'em_cuenta_bancaria' => $tabla,'em_empleado'=>'em_cuenta_bancaria',
            'nom_conf_nomina' => $tabla,'nom_conf_factura' => 'nom_conf_nomina',
            'cat_sat_periodicidad_pago_nom'=>'nom_conf_nomina', 'im_registro_patronal'=>'em_empleado');
        $campos_obligatorios = array('em_cuenta_bancaria_id','nom_conf_nomina_id','codigo','descripcion');

        $campos_view['em_cuenta_bancaria_id'] = array('type' => 'selects', 'model' => new em_cuenta_bancaria($link));
        $campos_view['nom_conf_nomina_id'] = array('type' => 'selects', 'model' => new nom_conf_nomina($link));
        $campos_view['em_empleado_id'] = array('type' => 'selects', 'model' => new em_empleado($link));
        $campos_view['codigo'] = array('type' => 'inputs');
        $campos_view['codigo_bis'] = array('type' => 'inputs');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas,campos_view: $campos_view);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(): array|stdClass
    {
        $keys = array('codigo','descripcion');

        $valida = $this->validacion->valida_existencia_keys(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        if(!isset($this->registro['codigo_bis'])){
            $this->registro['codigo_bis'] = strtoupper($this->registro['codigo']);
        }

        if(!isset($this->registro['descripcion_select'])){
            $this->registro['descripcion_select'] = strtoupper($this->registro['descripcion']);
        }

        $this->registro = $this->limpia_campos(registro: $this->registro,
            campos_limpiar: array('em_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $this->registro);
        }

        return parent::alta_bd();
    }

    private function limpia_campos(array $registro, array $campos_limpiar): array
    {
        foreach ($campos_limpiar as $valor) {
            if (isset($registro[$valor])) {
                unset($registro[$valor]);
            }
        }
        return $registro;
    }

    public function get_configuraciones_empleado(int $em_cuenta_bancaria_id): array|stdClass
    {
        if($em_cuenta_bancaria_id <=0){
            return $this->error->error(mensaje: 'Error $em_cuenta_bancaria debe ser mayor a 0', data: $em_cuenta_bancaria_id);
        }

        $filtro['em_cuenta_bancaria.id'] = $em_cuenta_bancaria_id;
        $registros = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener abonos', data: $registros);
        }

        return $registros;
    }

    public function nom_conf_empleado(int $em_empleado_id, int $nom_conf_nomina_id){
        $filtro['em_empleado.id'] = $em_empleado_id;
        $filtro['nom_conf_nomina.id'] = $nom_conf_nomina_id;
        $r_nom_conf_empleado = $this->filtro_and(filtro: $filtro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener nom_conf_empleado',data:  $r_nom_conf_empleado);
        }
        if($r_nom_conf_empleado->n_registros === 0){
            return $this->error->error(mensaje: 'Error no existe nom_conf_empleado',data:  $r_nom_conf_empleado);
        }
        if($r_nom_conf_empleado->n_registros > 1){
            return $this->error->error(mensaje: 'Error existe mas de un nom_conf_empleado',data:  $r_nom_conf_empleado);
        }

        return $r_nom_conf_empleado->registros[0];

    }
}