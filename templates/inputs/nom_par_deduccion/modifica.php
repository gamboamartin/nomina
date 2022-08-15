<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_par_deduccion $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->select->nom_nomina_id; ?>
<?php echo $controlador->inputs->select->nom_deduccion_id; ?>
<?php echo $controlador->inputs->importe_gravado; ?>
<?php echo $controlador->inputs->importe_exento; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>