<?php /** @var \gamboamartin\nomina\controllers\controlador_nom_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <form method="post" action="<?php echo $controlador->link_nom_nomina_alta_bd; ?>" class="form-additional">

                        <?php include (new views())->ruta_templates."head/title.php"; ?>
                        <?php include (new views())->ruta_templates."head/subtitulo.php"; ?>
                        <?php include (new views())->ruta_templates."mensajes.php"; ?>

                        <?php echo $controlador->inputs->select->org_sucursal_id; ?>
                        <?php echo $controlador->inputs->select->em_empleado_id; ?>
                        <?php echo $controlador->inputs->rfc; ?>
                        <?php echo $controlador->inputs->curp; ?>
                        <?php echo $controlador->inputs->nss; ?>
                        <?php echo $controlador->inputs->folio; ?>
                        <?php echo $controlador->inputs->fecha; ?>

                        <?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>

                    </form>
                </div>

            </div>

        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <div class="widget widget-box box-container widget-mylistings">

                    <div class="">
                        <table class="table table-striped footable-sort" data-sorting="true">
                            <th>Id</th>
                            <th>Codigo</th>
                            <th>Descripcion</th>
                            <th>Modifica</th>
                            <th>Elimina</th>

                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div> <!-- /. widget-table-->
            </div><!-- /.center-content -->
        </div>
    </div>


</main>

