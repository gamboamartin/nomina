<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_abono $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->id; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->select->em_tipo_abono_anticipo_id; ?>
<?php echo $controlador->inputs->select->nom_deduccion_id; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>