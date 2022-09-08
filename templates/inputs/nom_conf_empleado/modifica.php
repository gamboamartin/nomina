<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_empleado $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->id; ?>
<?php echo $controlador->inputs->select->em_empleado_id; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->select->em_cuenta_bancaria_id; ?>
<?php echo $controlador->inputs->select->nom_conf_nomina_id; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>