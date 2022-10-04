<?php
namespace models;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;

class nom_par_deduccion extends nominas{



    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_nomina'=>$tabla, 'nom_deduccion'=>$tabla,'fc_factura'=>'nom_nomina',
            'cat_sat_tipo_deduccion_nom'=>'nom_deduccion');
        $campos_obligatorios = array('nom_deduccion_id','importe_gravado','importe_exento');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->tabla_nom_conf = 'nom_deduccion';
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


        $modelo = new nom_deduccion($this->link);
        $registro = $this->asigna_registro_alta(modelo: $modelo, registro: $this->registro);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al asignar datos', data: $registro);
        }
        $this->registro = $registro;


        $r_alta_bd =  parent::alta_bd(); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al insertar percepcion', data: $r_alta_bd);
        }

        $fc_partida_upd = (new transaccion_fc())->actualiza_fc_partida_factura(
            link: $this->link, nom_nomina_id: $this->registro['nom_nomina_id']);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar $fc_partida', data: $fc_partida_upd);
        }

        return $r_alta_bd;
    }

    public function deducciones_by_nomina(int $nom_nomina_id): array|stdClass
    {
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $deducciones = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener deducciones', data: $deducciones);
        }
        return $deducciones;
    }

    public function elimina_bd(int $id): array|stdClass
    {

        $nom_datas_subsidios = (new nom_data_subsidio($this->link))->get_data_by_deduccion(nom_par_deduccion_id: $id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener data subsidio', data: $nom_datas_subsidios);
        }

        $dels = $this->del_data_subsidio(nom_datas_subsidios: $nom_datas_subsidios);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar data subsidio', data: $dels);
        }

        $r_elimina_bd = parent::elimina_bd($id); // TODO: Change the autogenerated stub
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al eliminar', data: $r_elimina_bd);
        }
        return $r_elimina_bd;
    }

    public function inserta_deduccion_anticipo(array $anticipo, int $nom_nomina_id, array $nom_conf_abono): array|stdClass
    {
        $nom_par_deduccion = $this->maquetar_nom_par_deduccion(anticipo: $anticipo,nom_nomina_id: $nom_nomina_id,
            nom_conf_abono: $nom_conf_abono);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al maquetar deduccion', data: $nom_par_deduccion);
        }

        $alta = (new nom_par_deduccion($this->link))->alta_registro($nom_par_deduccion);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al dat de alta deduccion', data: $alta);
        }
        return $alta;
    }

    private function maquetar_nom_par_deduccion(array $anticipo, int $nom_nomina_id, array $nom_conf_abono):array{

        $descuento = round($anticipo['em_tipo_descuento_monto'],2);

        if($anticipo['em_metodo_calculo_descripcion'] === "porcentaje_monto_bruto"){
            $total_percepciones = (new nom_nomina($this->link))->total_percepciones_gravado($nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener el total de percepciones', data: $total_percepciones);
            }

            $total_otros_pagos = (new nom_nomina($this->link))->total_otras_deducciones_monto($nom_nomina_id);
            if (errores::$error) {
                return $this->error->error(mensaje: 'Error al obtener el total de otros pagos', data: $total_otros_pagos);
            }

            $descuento = ($total_percepciones + $total_otros_pagos) * $anticipo['em_tipo_descuento_monto'];
        }

        $saldo = isset($anticipo['em_anticipo_saldo'])?  round($anticipo['em_anticipo_saldo'],2) : 0.0;
        if($descuento > $saldo){
            $descuento = $saldo;
        }

        $datos['descripcion'] = $anticipo['em_anticipo_descripcion'].$anticipo['em_anticipo_id'];
        $datos['codigo'] = $anticipo['em_anticipo_codigo'].$anticipo['em_tipo_descuento_codigo'].$nom_nomina_id;
        $datos['descripcion_select'] = strtoupper($datos['descripcion']);
        $datos['codigo_bis'] = strtoupper($datos['codigo']);
        $datos['alias'] = $datos['codigo'].$datos['descripcion'];
        $datos['nom_nomina_id'] = $nom_nomina_id;
        $datos['nom_deduccion_id'] = $nom_conf_abono['nom_deduccion_id'];
        $datos['importe_gravado'] = ($nom_conf_abono['adm_campo_descripcion'] === "importe_gravado") ?  $descuento : 0;
        $datos['importe_exento'] = ($nom_conf_abono['adm_campo_descripcion'] === "importe_exento") ?  $descuento : 0;

        return $datos;
    }

    /**
     * @throws JsonException
     */
    public function modifica_isr(int $nom_par_deduccion_id, float $importe_gravado, float $importe_exento): array|stdClass
    {
        $nom_par_deduccion_upd['importe_gravado'] = $importe_gravado;
        $nom_par_deduccion_upd['importe_exento'] = $importe_exento;
        $r_nom_par_deduccion = parent::modifica_bd(registro: $nom_par_deduccion_upd, id:$nom_par_deduccion_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar isr', data: $r_nom_par_deduccion);
        }

        return $r_nom_par_deduccion;


    }





}