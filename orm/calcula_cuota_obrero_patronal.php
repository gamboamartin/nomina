<?php

namespace models;
use gamboamartin\errores\errores;
use gamboamartin\validacion\validacion;
use stdClass;

class calcula_imss{

    private errores $error;
    private validacion $validacion;
    public float $porc_prestaciones_en_dinero_trabajador = .0025;
    public float $porc_pensionados_beneficiarios = .00375;
    public float $porc_invalidez_vida = .00625;
    public float $porc_cesantia = .01125;
    public float $porc_exc = .004;
    public array $salario_minimo = array(2020=>123.22,2021=>141.70,2022=>172.87);
    public array $uma = array(2020=>86.88,2021=>89.62,2022=>96.22);
    public string $year = '';
    public float $monto_uma = 0.0;
    public float $n_dias = 0.0;
    public float $sbc = 0.0;
    public float $sd= 0.0;
    public float $uma_3v = 0.0;
    public float $dif_uma_sbc = 0.0;
    public float $excedente = 0.0;
    public float $total_percepciones = 0.0;
    public float $prestaciones_en_dinero = 0.0;
    public float $pensionados_beneficiarios= 0.0;
    public float $invalidez_vida= 0.0;
    public float $cesantia= 0.0;
    public float $total= 0.0;
    public int $n_dias_mes= 0;
    public array $dias_vacaciones = array(6,8,10,12,14,14,14,14,14,16,16,16,16,16,18,18,18,18,18,20,20,20,20,20,
        22,22,22,22,22);


    public function __construct(){
        $this->error = new errores();
        $this->validacion = new validacion();

    }

}
