<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_tipo_incidencia $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->id; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->descripcion_select; ?>
<?php echo $controlador->inputs->alias; ?>
<?php echo $controlador->inputs->select->bn_sucursal_id; ?>
<?php echo $controlador->inputs->select->nom_periodo_id; ?>
<?php echo $controlador->inputs->fecha_pago; ?>
<?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>