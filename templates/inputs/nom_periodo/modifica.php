<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_periodo $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->id; ?>
<?php echo $controlador->inputs->codigo; ?>
<?php echo $controlador->inputs->codigo_bis; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->select->cat_sat_periodicidad_pago_nom_id; ?>
<?php echo $controlador->inputs->select->em_registro_patronal_id; ?>
<?php echo $controlador->inputs->select->nom_tipo_periodo_id; ?>
<?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
<?php echo $controlador->inputs->fecha_inicial_pago; ?>
<?php echo $controlador->inputs->fecha_final_pago; ?>
<?php echo $controlador->inputs->fecha_pago; ?>
<?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>
<div class="control-group btn-modifica">
    <div class="controls">
        <?php include 'templates/botons/nom_periodo/procesa_nomina.php';?>
    </div>
</div>

