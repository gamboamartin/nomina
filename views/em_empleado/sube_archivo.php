<?php /** @var gamboamartin\empleado\controllers\controlador_em_registro_patronal $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>
<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <form method="post" action="./index.php?seccion=em_empleado&accion=lee_archivo&session_id=<?php echo $controlador->session_id; ?>" class="form-additional" enctype="multipart/form-data">
                        <?php include (new views())->ruta_templates."head/title.php"; ?>
                        <?php include (new views())->ruta_templates."head/subtitulo.php"; ?>
                        <?php include (new views())->ruta_templates."mensajes.php"; ?>

                        <div class="control-group col-sm-12">
                            <label class="control-label" for="archivo">Archivo Nomina</label>
                            <div class="controls">
                                <input type="file" id="archivo" name="archivo" multiple />
                            </div>
                        </div>

                        <div class="controls">
                            <button type="submit" class="btn btn-success" value="bancos" name="btn_action_next">Sube Empleados</button><br>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
