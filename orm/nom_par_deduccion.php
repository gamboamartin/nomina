<?php
namespace gamboamartin\nomina\models;
use gamboamartin\errores\errores;
use JsonException;
use gamboamartin\nomina\models\base\limpieza;
use PDO;
use stdClass;

class nom_par_deduccion extends nominas{



    public function __construct(PDO $link){
        $tabla = 'nom_par_deduccion';
        $columnas = array($tabla=>false, 'nom_nomina'=>$tabla, 'nom_deduccion'=>$tabla,'fc_factura'=>'nom_nomina',
            'cat_sat_tipo_deduccion_nom'=>'nom_deduccion', 'em_empleado'=>'nom_nomina');
        $campos_obligatorios = array('nom_deduccion_id','importe_gravado','importe_exento');

        parent::__construct(link: $link,tabla:  $tabla, campos_obligatorios: $campos_obligatorios,
            columnas: $columnas);

        $this->tabla_nom_conf = 'nom_deduccion';

        $this->NAMESPACE = __NAMESPACE__;
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

        $descuento =  (new nom_nomina($this->link))->calcula_monto_abono(anticipo: $anticipo, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al calcular monto abono', data: $descuento);
        }

        $datos = (new limpieza())->maqueta_row_abono_base(anticipo: $anticipo, nom_nomina_id: $nom_nomina_id);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al integra base row', data: $datos);
        }

        $key_importe = trim($nom_conf_abono['adm_campo_descripcion']);
        $datos['importe_gravado'] = 0.0;
        $datos['importe_exento'] = 0.0;

        $datos['nom_nomina_id'] = $nom_nomina_id;
        $datos['nom_deduccion_id'] = $nom_conf_abono['nom_deduccion_id'];
        $datos[$key_importe] = $descuento;

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

    public function modifica_bd_deduccion(array $registro, int $id, bool $reactiva = false, $es_subsidio = false): array|stdClass
    {
        $nom_deduccion = $this->registro(registro_id:$id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro', data: $nom_deduccion);
        }

        $r_modifica_bd = parent::modifica_bd(registro: $registro,id:  $id,reactiva:  $reactiva); // TODO: Change the autogenerated stub
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al modificar registro', data: $r_modifica_bd);
        }

        $fc_partida_upd = (new transaccion_fc())->actualiza_fc_partida_factura(
            link: $this->link, nom_nomina_id: $nom_deduccion->nom_nomina_id);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al actualizar $fc_partida', data: $fc_partida_upd);
        }

        return $r_modifica_bd;
    }


    public function get_by_deduccion(int $nom_nomina_id, int $nom_deduccion_id){
        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['nom_deduccion.id'] = $nom_deduccion_id;

        $percepciones = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener percepciones', data: $percepciones);
        }
        return $percepciones;
    }




}