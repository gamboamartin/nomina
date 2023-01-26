<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_conf_percepcion $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<?php echo $controlador->inputs->nom_conf_nomina_id; ?>
<?php echo $controlador->inputs->nom_percepcion_id; ?>
<?php echo $controlador->inputs->descripcion; ?>
<?php echo $controlador->inputs->importe_gravado; ?>
<?php echo $controlador->inputs->importe_exento; ?>
<?php echo $controlador->inputs->fecha_inicio; ?>
<?php echo $controlador->inputs->fecha_fin; ?>
<?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>
<div class="col-row-12">
    <?php foreach ($controlador->buttons as $button){ ?>
        <?php echo $button; ?>
    <?php }?>
</div>

