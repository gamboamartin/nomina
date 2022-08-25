<?php
namespace models;
use base\orm\modelo;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;
use Throwable;

class nom_par_percepcion extends modelo{

    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_nomina'=>$tabla, 'nom_percepcion'=>$tabla,
            'cat_sat_tipo_percepcion_nom'=>'nom_percepcion','cat_sat_periodicidad_pago_nom'=>'nom_nomina');
        $campos_obligatorios = array('nom_nomina_id','descripcion_select','alias','codigo_bis','nom_percepcion_id',
            'importe_gravado','importe_exento');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);
    }

    /**
     * @throws JsonException
     */
    public function alta_bd(): array|stdClass
    {

        $keys = array('nom_nomina_id');
        $valida = $this->validacion->valida_ids(keys: $keys, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al validar registro', data: $valida);
        }

        $registro = $this->asigna_registro_alta(registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }
        $this->registro = $registro;

        $total = $this->total_percepcion(registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener total', data: $total);
        }

        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar percepcion', data: $r_alta_bd);
        }


        $isr = $this->calcula_isr_nomina(nom_par_percepcion_id: $r_alta_bd->registro_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }



        return $r_alta_bd;
    }

    private function asigna_alias(array $registro): array
    {
        if(!isset($registro['alias'])){

            $registro['alias'] = $registro['descripcion'];

        }
        return $registro;
    }

    private function asigna_codigo(array $registro): array
    {
        if(!isset($registro['codigo'])){

            $codigo = $this->genera_codigo(nom_nomina_id: $registro['nom_nomina_id'], registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener codigo', data: $codigo);
            }
            $registro['codigo'] = $codigo;
        }
        return $registro;
    }

    private function asigna_codigo_bis(array $registro
    ): array
    {
        if(!isset($registro['codigo_bis'])){

            $registro['codigo_bis'] = $registro['codigo'];
        }
        return $registro;
    }

    private function asigna_descripcion(array $registro): array
    {
        if(!isset($registro['descripcion'])){

            $descripcion = $this->genera_descripcion(nom_nomina_id: $registro['nom_nomina_id'], registro: $registro);
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al obtener descripcion', data: $descripcion);
            }

            $registro['descripcion'] = $descripcion;

        }
        return $registro;
    }

    private function asigna_descripcion_select(array $registro): array
    {
        if(!isset($registro['descripcion_select'])){

            $registro['descripcion_select'] = $registro['descripcion'];
        }
        return $registro;
    }

    private function asigna_importe_exento(array $registro): array
    {
        if(!isset($registro['importe_exento'])){

            $registro['importe_exento'] = 0;
        }
        return $registro;
    }

    private function asigna_importe_gravado(array $registro): array
    {
        if(!isset($registro['importe_gravado'])){

            $registro['importe_gravado'] = 0;
        }
        return $registro;
    }

    private function asigna_registro_alta(array $registro): array
    {
        $registro = $this->asigna_codigo(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar codigo', data: $registro);
        }

        $registro = $this->asigna_descripcion(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion', data: $registro);
        }

        $registro = $this->asigna_descripcion_select(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        $registro = $this->asigna_alias(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        $registro = $this->asigna_codigo_bis(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        $registro = $this->asigna_importe_gravado(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }

        $registro = $this->asigna_importe_exento(registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar descripcion_select', data: $registro);
        }
       return $registro;
    }

    private function calcula_isr_nomina(int $nom_par_percepcion_id): float|array
    {
        $isr = 0.0;
        $total_gravado = (new nom_nomina($this->link))->total_gravado(nom_nomina_id: $nom_par_percepcion_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al calcular total gravado', data: $total_gravado);
        }

        if($total_gravado >0.0) {

            $isr = $this->isr_total_nomina_por_percepcion(
                nom_par_percepcion_id: $nom_par_percepcion_id, total_gravado: $total_gravado);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
            }

        }

        return $isr;
    }

    private function codigo_alta(stdClass $nom_nomina, array $registro): array|string
    {
        $codigo = $registro['nom_nomina_id'];
        $codigo .= '-';
        $codigo .= $nom_nomina->cat_sat_periodicidad_pago_nom_id;
        $codigo .= '-';
        $codigo .= $nom_nomina->em_empleado_rfc;
        $codigo .= '-';
        $codigo .= $nom_nomina->im_registro_patronal_id;
        $codigo .= '-';


        $codigo_random = $this->codigo_random();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener codigo random', data: $codigo_random);
        }

        $codigo.=$codigo_random;

        return $codigo;
    }

    private function codigo_random(): array|string
    {
        try {
            $codigo = random_int(10, 99) . random_int(10, 99) . random_int(10, 99) . random_int(10, 99);
        }
        catch (Throwable $e){
            return $this->error->error(mensaje: 'Error al generar codigo random', data: $e);
        }
        return $codigo;
    }

    private function descripcion_alta(stdClass $nom_nomina, array $registro): array|string
    {
        $descripcion = $registro['nom_nomina_id'];
        $descripcion .= '-';
        $descripcion .= $nom_nomina->cat_sat_periodicidad_pago_nom_descripcion;
        $descripcion .= '-';
        $descripcion .= $nom_nomina->em_empleado_rfc;
        $descripcion .= '-';
        $descripcion .= $nom_nomina->im_registro_patronal_descripcion;
        $descripcion .= '-';

        $descripcion_random = $this->codigo_random();
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener descripcion', data: $descripcion_random);
        }
        $descripcion.=$descripcion_random;

        return $descripcion;
    }

    private function genera_codigo(int $nom_nomina_id, array $registro): array|string
    {
        $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $nom_nomina_id,
            retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener $nom_nomina', data: $nom_nomina);
        }

        $codigo = $this->codigo_alta(nom_nomina: $nom_nomina, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener codigo', data: $codigo);
        }
        return $codigo;
    }

    private function genera_descripcion(int $nom_nomina_id, array $registro): array|string
    {
        $nom_nomina = (new nom_nomina($this->link))->registro(registro_id: $nom_nomina_id,
            retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener $nom_nomina', data: $nom_nomina);
        }

        $descripcion = $this->descripcion_alta(nom_nomina: $nom_nomina, registro: $registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener descripcion', data: $descripcion);
        }
        return $descripcion;
    }

    private function isr_total_nomina_por_percepcion(int $nom_par_percepcion_id, string $total_gravado): float|array
    {
        $nom_par_percepcion = $this->registro(registro_id: $nom_par_percepcion_id, retorno_obj: true);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener nom_par_percepcion', data: $nom_par_percepcion);
        }

        $isr = (new nom_nomina($this->link))->isr(
            cat_sat_periodicidad_pago_nom_id: $nom_par_percepcion->cat_sat_periodicidad_pago_nom_id,
            monto: $total_gravado, fecha: $nom_par_percepcion->nom_nomina_fecha_final_pago);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener isr', data: $isr);
        }

        return $isr;
    }

    private function total_percepcion(array $registro): float|array
    {
        $total = $registro['importe_exento']+ $registro['importe_gravado'];
        $total = round($total,2);

        if($total<=0.0){
            return $this->error->error(mensaje: 'Error total es 0', data: $total);
        }
        return $total;
    }
}