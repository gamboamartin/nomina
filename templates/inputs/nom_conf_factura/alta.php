<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_factura $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->select->cat_sat_forma_pago_id; ?>
<?php echo $controlador->inputs->select->cat_sat_metodo_pago_id; ?>
<?php echo $controlador->inputs->select->cat_sat_moneda_id; ?>
<?php echo $controlador->inputs->select->com_tipo_cambio_id; ?>
<?php echo $controlador->inputs->select->cat_sat_uso_cfdi_id; ?>
<?php echo $controlador->inputs->select->cat_sat_tipo_de_comprobante_id; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>