<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_abono $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->adm_campo_id; ?>
<?php echo $controlador->inputs->em_tipo_anticipo_id; ?>
<?php echo $controlador->inputs->em_tipo_abono_anticipo_id; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->nom_deduccion_id; ?>
<?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>