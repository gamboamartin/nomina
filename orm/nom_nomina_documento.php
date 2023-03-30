<?php
namespace gamboamartin\nomina\models;
use base\orm\_modelo_parent;
use gamboamartin\errores\errores;
use PDO;
use stdClass;

class nom_nomina_documento extends _modelo_parent {
    public function __construct(PDO $link){
        $tabla = 'nom_nomina_documento';
        $columnas = array($tabla=>false,'nom_nomina'=>$tabla,'doc_documento'=>$tabla,'doc_tipo_documento' => 'doc_documento');
        $campos_obligatorios = array();

        parent::__construct(link: $link, tabla: $tabla, campos_obligatorios: $campos_obligatorios, columnas: $columnas);

        $this->NAMESPACE = __NAMESPACE__;
    }

    public function alta_bd(array $keys_integra_ds = array('codigo', 'descripcion')): array|stdClass
    {
        if(!isset($this->registro['codigo'])){
            $this->registro['codigo'] =  $this->get_codigo_aleatorio();
            if(errores::$error){
                return $this->error->error(mensaje: 'Error al generar codigo aleatorio',data:  $this->registro);
            }
        }

        $this->registro['descripcion'] = $this->registro['codigo'];

        $this->registro = $this->campos_base(data: $this->registro,modelo: $this);
        if(errores::$error){
            return $this->error->error(mensaje: 'Error al inicializar campos base',data: $this->registro);
        }

        $r_alta_bd =  parent::alta_bd();
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error registrar factura documento', data: $r_alta_bd);
        }

        return $r_alta_bd;
    }

    public function get_nomina_documento(int $nom_nomina_id, string $tipo_documento): array|string{


        $documento = $this->get_nomina_documentos(nom_nomina_id: $nom_nomina_id,tipo_documento: $tipo_documento);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error al obtener documento', data: $documento);
        }


        $ruta_archivo = "";

        if ($documento->n_registros > 0){
            $ruta_archivo = $documento->registros[0]['doc_documento_ruta_relativa'];
        }

        return $ruta_archivo;
    }


    public function get_nomina_documentos(int $nom_nomina_id, string $tipo_documento): array|stdClass{

        $filtro['nom_nomina.id'] = $nom_nomina_id;
        $filtro['doc_tipo_documento.descripcion'] = $tipo_documento;
        $documento = $this->filtro_and(filtro: $filtro);
        if (errores::$error) {
            return $this->error->error(mensaje: 'Error obtener documento', data: $documento);
        }


        return $documento;
    }

}