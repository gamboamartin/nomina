<?php /** @var \gamboamartin\nomina\controllers\controlador_nom_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <?php include (new views())->ruta_templates."head/title.php"; ?>
                    <?php include (new views())->ruta_templates."head/subtitulo.php"; ?>
                    <?php include (new views())->ruta_templates."mensajes.php"; ?>
                    <form method="post" action="<?php echo $controlador->link_nom_par_otro_pago_alta_bd; ?>" class="form-additional">
                        <?php echo $controlador->inputs->select->nom_nomina_id; ?>
                        <?php echo $controlador->inputs->select->nom_otro_pago_id; ?>
                        <?php echo $controlador->inputs->descripcion; ?>
                        <?php echo $controlador->inputs->importe_gravado; ?>
                        <?php echo $controlador->inputs->importe_exento; ?>
                        <div class="control-group btn-alta">
                            <div class="controls">
                                <button type="submit" class="btn btn-success" value="modifica" name="btn_action_next">Alta</button><br>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

</main>
