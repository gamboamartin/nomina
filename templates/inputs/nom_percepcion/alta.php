<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_percepcion $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->select->cat_sat_tipo_percepcion_nom_id; ?>
<?php echo $controlador->inputs->aplica_imss; ?>
<?php echo $controlador->inputs->aplica_subsidio; ?>
    <input type="checkbox" name="aux" value="activo"> Aux
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>