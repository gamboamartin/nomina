<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_percepcion $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->cat_sat_tipo_percepcion_nom_id; ?>
<?php echo $controlador->inputs->descripcion_select; ?>
<?php echo $controlador->inputs->alias; ?>
<?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>