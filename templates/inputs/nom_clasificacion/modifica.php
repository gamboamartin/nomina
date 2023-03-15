<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_clasificacion $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<?php echo $controlador->inputs->cob_concepto_id; ?>
<?php echo $controlador->inputs->cob_cliente_id; ?>

<?php echo $controlador->inputs->monto; ?>
<?php echo $controlador->inputs->fecha_vencimiento; ?>

<?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>

<div class="cold-row-12">
    <?php foreach ($controlador->buttons as $button){ ?>
        <?php echo $button; ?>
    <?php }?>
</div>
