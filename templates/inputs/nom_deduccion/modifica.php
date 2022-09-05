<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_deduccion $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->id; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->select->cat_sat_tipo_deduccion_nom_id; ?>
<?php echo $controlador->inputs->descripcion_select; ?>
<?php echo $controlador->inputs->alias; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>