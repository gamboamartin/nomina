<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->num_dias_pagados; ?>
<?php echo $controlador->inputs->fecha_pago; ?>
<?php echo $controlador->inputs->fecha_inicial_pago; ?>
<?php echo $controlador->inputs->fecha_final_pago; ?>
<?php echo $controlador->inputs->select->em_empleado_id; ?>
<?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
<?php echo $controlador->inputs->select->fc_factura_id; ?>
<?php echo $controlador->inputs->select->dp_calle_pertenece_id; ?>
<?php echo $controlador->inputs->select->im_registro_patronal_id; ?>
<?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>