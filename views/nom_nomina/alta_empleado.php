<?php /** @var \gamboamartin\nomina\controllers\controlador_nom_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <form method="post" action="<?php echo $controlador->link_empleado_alta_bd; ?>" class="form-additional">

                        <?php include (new views())->ruta_templates."head/title.php"; ?>
                        <?php include (new views())->ruta_templates."head/subtitulo.php"; ?>
                        <?php include (new views())->ruta_templates."mensajes.php"; ?>

                        <?php echo $controlador->inputs->codigo; ?>
                        <?php echo $controlador->inputs->nombre; ?>
                        <?php echo $controlador->inputs->ap; ?>
                        <?php echo $controlador->inputs->am; ?>
                        <?php echo $controlador->inputs->telefono; ?>
                        <?php echo $controlador->inputs->select->dp_pais_id; ?>
                        <?php echo $controlador->inputs->select->dp_estado_id; ?>
                        <?php echo $controlador->inputs->select->dp_municipio_id; ?>
                        <?php echo $controlador->inputs->select->dp_cp_id; ?>
                        <?php echo $controlador->inputs->select->dp_colonia_postal_id; ?>
                        <?php echo $controlador->inputs->select->dp_calle_pertenece_id; ?>
                        <?php echo $controlador->inputs->select->cat_sat_regimen_fiscal_id; ?>
                        <?php echo $controlador->inputs->select->org_puesto_id; ?>
                        <?php echo $controlador->inputs->select->cat_sat_tipo_regimen_nom_id; ?>
                        <?php echo $controlador->inputs->select->cat_sat_tipo_jornada_nom_id; ?>

                        <?php echo $controlador->inputs->rfc; ?>
                        <?php echo $controlador->inputs->curp; ?>
                        <?php echo $controlador->inputs->nss; ?>
                        <?php echo $controlador->inputs->select->em_registro_patronal_id; ?>
                        <?php echo $controlador->inputs->select->em_centro_costo_id; ?>

                        <?php echo $controlador->inputs->salario_diario; ?>
                        <?php echo $controlador->inputs->salario_diario_integrado; ?>
                        <?php echo $controlador->inputs->salario_total; ?>
                        <?php include (new views())->ruta_templates.'botons/submit/alta_bd.php';?>
                    </form>
                </div>

            </div>

        </div>
    </div>

</main>

