<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_empleado $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->em_empleado_id; ?>
<?php echo $controlador->inputs->em_cuenta_bancaria_id; ?>
<?php echo $controlador->inputs->nom_conf_nomina_id; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd.php';?>