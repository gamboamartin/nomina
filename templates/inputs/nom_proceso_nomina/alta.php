<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_proceso_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->descripcion_select; ?>
<?php echo $controlador->inputs->alias; ?>
<?php echo $controlador->inputs->select->adm_seccion_id; ?>
<?php echo $controlador->inputs->select->pr_tipo_proceso_id; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>