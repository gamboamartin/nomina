<?php
namespace models;
use gamboamartin\errores\errores;
use JsonException;
use PDO;
use stdClass;

class nom_par_deduccion extends nominas{



    public function __construct(PDO $link){
        $tabla = __CLASS__;
        $columnas = array($tabla=>false, 'nom_nomina'=>$tabla, 'nom_deduccion'=>$tabla,'fc_factura'=>'nom_nomina');
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

    public function elimina_bd(int $id): array
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