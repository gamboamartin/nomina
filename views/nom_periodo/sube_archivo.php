<?php /** @var \gamboamartin\nomina\controllers\controlador_nom_periodo $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <form method="post" action="<?php echo $controlador->link_lee_archivo; ?>" class="form-additional">

                        <?php include (new views())->ruta_templates."head/title.php"; ?>
                        <?php include (new views())->ruta_templates."head/subtitulo.php"; ?>
                        <?php include (new views())->ruta_templates."mensajes.php"; ?>
                        <div class="control-group col-sm-6">
                            <label class="control-label" for="archivo">Archivo Nomina</label>
                            <div class="controls">
                                <input type="file" id="archivo" name="archivo" multiple />
                            </div>
                        </div>

                        <?php include (new views())->ruta_templates.'botons/submit/alta_bd.php';?>

                    </form>
                </div>

            </div>

        </div>
    </div>

</main>






