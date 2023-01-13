<?php
namespace gamboamartin\nomina\models;
use base\orm\modelo;
use gamboamartin\errores\errores;


class nominas_confs extends modelo {



    public function aplica_imss(int $registro_id): bool|array
    {

        $row = $this->registro(registro_id: $registro_id, retorno_obj: true);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al obtener registro' , data: $row);
        }

        $aplica_imss = false;
        $key_aplica = $this->tabla.'_aplica_imss';
        if(isset($row->$key_aplica) && $row->$key_aplica === 'activo'){
            $aplica_imss = true;
        }

        return $aplica_imss;

    }


}
