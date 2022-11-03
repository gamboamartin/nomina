<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->nom_conf_factura_id; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->cat_sat_periodicidad_pago_nom_id; ?>
<?php echo $controlador->inputs->cat_sat_tipo_nomina_id; ?>
<?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>