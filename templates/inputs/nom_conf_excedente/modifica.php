<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_excedente $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->nom_conf_nomina_id; ?>
<?php echo $controlador->inputs->nom_percepcion_id; ?>
<?php echo $controlador->inputs->descripcion_select; ?>
<?php echo $controlador->inputs->alias; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>