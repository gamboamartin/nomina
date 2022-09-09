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

                        <?php echo $controlador->inputs->select->im_registro_patronal_id; ?>
                        <?php echo $controlador->inputs->select->nom_periodo_id; ?>
                        <?php echo $controlador->inputs->select->em_empleado_id; ?>
                        <?php echo $controlador->inputs->select->org_puesto_id; ?>
                        <?php echo $controlador->inputs->select->cat_sat_tipo_contrato_nom_id; ?>
                        <?php echo $controlador->inputs->select->nom_conf_empleado_id; ?>
                        <?php echo $controlador->inputs->select->em_cuenta_bancaria_id; ?>
                        <?php echo $controlador->inputs->rfc; ?>
                        <?php echo $controlador->inputs->curp; ?>
                        <?php echo $controlador->inputs->nss; ?>
                        <?php echo $controlador->inputs->folio; ?>
                        <?php echo $controlador->inputs->fecha; ?>
                        <?php echo $controlador->inputs->fecha_inicio_rel_laboral; ?>
                        <?php echo $controlador->inputs->select->cat_sat_tipo_nomina_id; ?>
                        <?php echo $controlador->inputs->select->cat_sat_periodicidad_pago_nom_id; ?>
                        <?php echo $controlador->inputs->fecha_pago; ?>
                        <?php echo $controlador->inputs->fecha_inicial_pago; ?>
                        <?php echo $controlador->inputs->fecha_final_pago; ?>
                        <?php echo $controlador->inputs->num_dias_pagados; ?>
                        <?php echo $controlador->inputs->salario_diario; ?>
                        <?php echo $controlador->inputs->salario_diario_integrado; ?>
                        <?php echo $controlador->inputs->subtotal; ?>
                        <?php echo $controlador->inputs->descuento; ?>
                        <?php echo $controlador->inputs->total; ?>

                        <?php include (new views())->ruta_templates.'botons/submit/alta_bd.php';?>

                    </form>
                </div>

            </div>

        </div>
    </div>

</main>

