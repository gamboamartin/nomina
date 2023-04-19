<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_rel_empleado_sucursal $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>

<?php echo $controlador->inputs->select->em_empleado_id; ?>
<?php echo $controlador->inputs->select->com_sucursal_id; ?>

<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>

