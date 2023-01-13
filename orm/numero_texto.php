<?php
namespace gamboamartin\nomina\models;
class numero_texto
{
    private $UNIDADES = array(
        '',
        'UN ',
        'DOS ',
        'TRES ',
        'CUATRO ',
        'CINCO ',
        'SEIS ',
        'SIETE ',
        'OCHO ',
        'NUEVE ',
        'DIEZ ',
        'ONCE ',
        'DOCE ',
        'TRECE ',
        'CATORCE ',
        'QUINCE ',
        'DIECISEIS ',
        'DIECISIETE ',
        'DIECIOCHO ',
        'DIECINUEVE ',
        'VEINTE '
    );
    private $DECENAS = array(
        'VEINTI',
        'TREINTA ',
        'CUARENTA ',
        'CINCUENTA ',
        'SESENTA ',
        'SETENTA ',
        'OCHENTA ',
        'NOVENTA ',
        'CIEN '
    );
    private $CENTENAS = array(
        'CIENTO ',
        'DOSCIENTOS ',
        'TRESCIENTOS ',
        'CUATROCIENTOS ',
        'QUINIENTOS ',
        'SEISCIENTOS ',
        'SETECIENTOS ',
        'OCHOCIENTOS ',
        'NOVECIENTOS '
    );

    public $monedas = array(
        'MXN' => array('moneda' => 'PESOS'),
        'USD' => array('moneda' => 'DOLLARS'),
        'XXX' => array('moneda' => 'SIN MONEDA')
    );

    private $separator = ',';
    private $decimal_mark = '.';
    private $glue = '  ';

    /**
     * DEBUG INI Evalua si el número contiene separadores o decimales
     * formatea y ejecuta la función conversora
     * @param $number número a convertir
     * @param $miMoneda clave de la moneda
     * @return string completo
     */
    public function to_word(float $number, string $miMoneda = '', $formatoCentavos = '', $aplicaDecimal = true, $cod_pais = '')
    {

        if (strpos($number, $this->decimal_mark) === FALSE) {
            $convertedNumber = array(
                $this->convertNumber($number, $miMoneda, 'entero', '', $aplicaDecimal, $cod_pais),
                $this->convertNumber(00, $miMoneda, 'decimal', $formatoCentavos, $aplicaDecimal, $cod_pais),
            );
        } else {
            $number = explode($this->decimal_mark, str_replace($this->separator, '', trim($number)));
            $convertedNumber = array(
                $this->convertNumber($number[0], $miMoneda, 'entero', '', $aplicaDecimal, $cod_pais),
                $this->convertNumber($number[1], $miMoneda, 'decimal', $formatoCentavos, $aplicaDecimal, $cod_pais),
            );
        }
        return implode($this->glue, array_filter($convertedNumber));
    }

    /**
     * DEBUG INI
     * @param float $number
     * @param string $miMoneda
     * @param $type
     * @param string $formatoCentavos
     * @param bool $aplicaDecimal
     * @return false|string
     */
    private function convertNumber(float $number, string $miMoneda, $type, $formatoCentavos = '', $aplicaDecimal = true)
    {

        $converted = '';

        if ($type != 'decimal') {

            if (($number < 0) || ($number > 999999999)) {
                return false;
            }
            $numberStr = (string)$number;
            $numberStrFill = str_pad($numberStr, 9, '0', STR_PAD_LEFT);
            $millones = substr($numberStrFill, 0, 3);
            $miles = substr($numberStrFill, 3, 3);
            $cientos = substr($numberStrFill, 6);
            if (intval($millones) > 0) {
                if ($millones == '001') {
                    $converted .= 'UN MILLON ';
                } else if (intval($millones) > 0) {
                    $converted .= sprintf('%sMILLONES ', $this->convertGroup($millones));
                }
            }

            if (intval($miles) > 0) {
                if ($miles == '001') {
                    $converted .= 'MIL ';
                } else if (intval($miles) > 0) {
                    $converted .= sprintf('%sMIL ', $this->convertGroup($miles));
                }
            }
            if (intval($cientos) > 0) {
                if ($cientos == '001') {
                    $converted .= 'UN ';
                } else if (intval($cientos) > 0) {
                    $converted .= sprintf('%s', $this->convertGroup($cientos));
                }
            }
            if ($aplicaDecimal) {
                if ($miMoneda === '') {
                    $converted .= '';
                } else {
                    $converted .= $this->monedas[$miMoneda]['moneda'] . ',';
                }
            } else {
                $converted = '';
            }
        } else {
            if ($number == 0) {
                $number = '00';
            } elseif ($number == 1) {
                $number = '10';
            } elseif ($number == 2) {
                $number = '20';
            } elseif ($number == 3) {
                $number = '30';
            } elseif ($number == 4) {
                $number = '40';
            } elseif ($number == 5) {
                $number = '50';
            } elseif ($number == 6) {
                $number = '60';
            } elseif ($number == 7) {
                $number = '70';
            } elseif ($number == 8) {
                $number = '80';
            } elseif ($number == 9) {
                $number = '90';
            }
            if ($aplicaDecimal) {
                $converted .= $number . '/100' . $formatoCentavos . ' ' . $miMoneda;
            } else {
                $converted .= '';
            }
        }
        return $converted . '';
    }

    /**
     * DEBUG INI Define el tipo de representación decimal (centenas/millares/millones)
     * @param $n
     * @return $output
     */
    private function convertGroup($n)
    {

        $output = '';
        if ($n == '100') {
            $output = "CIEN";
        } else if ($n[0] !== '0') {
            $output = $this->CENTENAS[$n[0] - 1];
        }
        $k = intval(substr($n, 1));
        if ($k <= 20) {
            $output .= $this->UNIDADES[$k];
        } else {
            if (($k > 30) && ($n[2] !== '0')) {
                $output .= sprintf('%sY %s', $this->DECENAS[intval($n[1]) - 2], $this->UNIDADES[intval($n[2])]);
            } else {
                $output .= sprintf('%s%s', $this->DECENAS[intval($n[1]) - 2], $this->UNIDADES[intval($n[2])]);
            }
        }
        return $output;
    }
}