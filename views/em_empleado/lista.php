<?php /** @var \gamboamartin\empleado\models\em_empleado $controlador controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <?php include (new views())->ruta_templates . "head/lista/title.php"; ?>
                <?php include (new views())->ruta_templates . "mensajes.php"; ?>
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <div class="table-head" style="display: flex; justify-content: space-between; ">
                        <?php include (new views())->ruta_templates . "head/subtitulo.php"; ?>
                        <div class="botones">
                            <a href="<?php echo $controlador->link_em_empleado_sube_archivo; ?>" class="btn btn-success" style="border-radius: 5px">
                                <span class="glyphicon glyphicon-list-alt" aria-hidden="true" style="color: #ffffff; margin-right: 5px"></span>
                                Sube Empleados
                            </a>
                            <a href="<?php echo $controlador->link_em_empleado_reportes; ?>" class="btn btn-success" style="border-radius: 5px">
                                <span class="glyphicon glyphicon-list-alt" aria-hidden="true" style="color: #ffffff; margin-right: 5px"></span>
                                Reportes
                            </a>
                        </div>
                    </div>
                    <table class="table table-striped datatable"></table>
                </div>

            </div>
        </div>
    </div>
</main>







