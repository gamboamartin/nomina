<?php /** @var \gamboamartin\nomina\controllers\controlador_nom_conf_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <?php include (new views())->ruta_templates."head/title.php"; ?>
                    <?php include (new views())->ruta_templates."head/subtitulo.php"; ?>
                    <?php include (new views())->ruta_templates."mensajes.php"; ?>
                    <form method="post" action="<?php echo $controlador->link_nom_conf_percepcion_alta_bd; ?>" class="form-additional">
                        <?php echo $controlador->inputs->codigo; ?>
                        <?php echo $controlador->inputs->nom_conf_nomina_id; ?>
                        <?php echo $controlador->inputs->descripcion; ?>
                        <?php echo $controlador->inputs->nom_percepcion_id; ?>
                        <?php echo $controlador->inputs->importe_gravado; ?>
                        <?php echo $controlador->inputs->importe_exento; ?>
                        <?php echo $controlador->inputs->fecha_inicio; ?>
                        <?php echo $controlador->inputs->fecha_fin; ?>
                        <div class="control-group btn-alta">
                            <div class="controls">
                                <button type="submit" class="btn btn-success" value="asigna_percepcion" name="btn_action_next">Alta</button><br>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <div class="widget widget-box box-container widget-mylistings">
                    <div class="widget-header" style="display: flex;justify-content: space-between;align-items: center;">
                        <h2>Percepciones Asignadas</h2>
                    </div>
                    <div class="">
                        <table id="nom_conf_percepcion" class="table table-striped" >
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>







