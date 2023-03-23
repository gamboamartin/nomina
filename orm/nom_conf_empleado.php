<?php
namespace gamboamartin\nomina\models;
use base\orm\_modelo_parent;
use base\orm\modelo;
use gamboamartin\empleado\models\em_cuenta_bancaria;
use gamboamartin\empleado\models\em_empleado;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_conf_empleado extends _modelo_parent{

    public function __construct(PDO $link){
        $tabla = 'nom_conf_empleado';
        $columnas = array($tabla=>false,'em_cuenta_bancaria' => $tabla,'em_empleado'=>'em_cuenta_bancaria',
            'nom_conf_nomina' => $tabla,'nom_conf_factura' => 'nom_conf_nomina',
            'cat_sat_periodicidad_pago_nom'=>'nom_conf_nomina', 'em_registro_patronal'=>'em_empleado');
        $campos_obligatorios = array('em_cuenta_bancaria_id','nom_conf_nomina_id','codigo','descripcion');


        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        if (!isset($this->registro['codigo'])) {
            $codigo = $this->get_codigo_aleatorio();
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al generar codigo', data: $codigo);
            }
        }

        $this->registro = $this->limpia_campos_extras(registro: $this->registro,
            campos_limpiar: array('em_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $this->registro);
        }

        $r_alta_bd = parent::alta_bd($keys_integra_ds);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al insertar conf. empleado', data: $r_alta_bd);
        }

        return $r_alta_bd;
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

    public function modifica_bd(array $registro, int $id, bool $reactiva = false,
                                array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        $this->registro = $this->limpia_campos_extras(registro: $registro, campos_limpiar: array('em_empleado_id'));
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al limpiar campos', data: $registro);
        }

        $r_modifica_bd = parent::modifica_bd($registro, $id, $reactiva, $keys_integra_ds);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al modificar conf. empleado',data: $r_modifica_bd);
        }

        return $r_modifica_bd;
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