<?php /** @var  \gamboamartin\nomina\controllers\controlador_nom_nomina $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="widget  widget-box box-container form-main widget-form-cart" id="form">
                    <?php include (new views())->ruta_templates."head/title.php"; ?>
                    <?php include (new views())->ruta_templates."head/subtitulo.php"; ?>
                    <?php include (new views())->ruta_templates."mensajes.php"; ?>
                    <form method="post" action="<?php echo $controlador->link_nom_nomina_modifica_bd; ?>" class="form-additional">
                        <?php echo $controlador->inputs->id; ?>
                        <?php echo $controlador->inputs->select->em_empleado_id; ?>
                        <?php echo $controlador->inputs->select->em_registro_patronal_id; ?>
                        <?php echo $controlador->inputs->select->nom_periodo_id; ?>

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

                        <?php include (new views())->ruta_templates.'botons/submit/modifica_bd.php';?>

                        <div class="control-group btn-alta">
                            <div class="controls">
                                <?php include 'templates/botons/nom_nomina_crea_nomina.php';?>
                            </div>
                        </div>
                    </form>
                    <div class="widget-header">
                        <h2>Neto</h2>
                    </div>
                    <form method="post" action="<?php echo $controlador->link_nom_nomina_recalcula_neto_bd; ?>" class="form-additional">
                        <?php echo $controlador->inputs->neto; ?>
                        <div class="controls">
                            <button type="submit" class="btn btn-success ">Recalcula sobre Neto</button><br>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>




</main>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <div class="widget widget-box box-container widget-mylistings">

                <div class="">
                    <div class="widget-header">
                        <h2>Percepciones</h2>
                    </div>

                    <table class="table table-striped footable-sort" data-sorting="true">
                        <th>Id</th>
                        <th>Codigo</th>
                        <th>Descripcion</th>
                        <th>Importe Gravado</th>
                        <th>Importe Exento</th>
                        <th>Modifica</th>
                        <th>Elimina</th>

                        <tbody>

                        <?php foreach ($controlador->percepciones->registros as $percepcion){?>
                            <tr>
                                <td><?php echo $percepcion['nom_par_percepcion_id']; ?></td>
                                <td><?php echo $percepcion['nom_percepcion_codigo']; ?></td>
                                <td><?php echo $percepcion['nom_par_percepcion_descripcion']; ?></td>
                                <td><?php echo $percepcion['nom_par_percepcion_importe_gravado']; ?></td>
                                <td><?php echo $percepcion['nom_par_percepcion_importe_exento']; ?></td>
                                <td><?php echo $percepcion['link_modifica']; ?></td>
                                <td><?php echo $percepcion['link_elimina']; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div class="box-body">
                        * Total registros: <?php echo $controlador->percepciones->n_registros; ?><br />
                        * Fecha Hora: <?php echo $controlador->fecha_hoy; ?>
                    </div>
                </div>
            </div> <!-- /. widget-table-->
        </div><!-- /.center-content -->
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <div class="widget widget-box box-container widget-mylistings">

                <div class="">
                    <div class="widget-header">
                        <h2>Deducciones</h2>
                    </div>

                    <table class="table table-striped footable-sort" data-sorting="true">
                        <th>Id</th>
                        <th>Codigo</th>
                        <th>Descripcion</th>
                        <th>Importe Gravado</th>
                        <th>Importe Exento</th>
                        <th>Modifica</th>
                        <th>Elimina</th>

                        <tbody>
                            <?php foreach ($controlador->deducciones->registros as $deduccion){?>
                                <tr>
                                    <td><?php echo $deduccion['nom_par_deduccion_id']; ?></td>
                                    <td><?php echo $deduccion['nom_deduccion_codigo']; ?></td>
                                    <td><?php echo $deduccion['nom_par_deduccion_descripcion']; ?></td>
                                    <td><?php echo $deduccion['nom_par_deduccion_importe_gravado']; ?></td>
                                    <td><?php echo $deduccion['nom_par_deduccion_importe_exento']; ?></td>
                                    <td><?php echo $deduccion['link_modifica']; ?></td>
                                    <td><?php echo $deduccion['link_elimina']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="box-body">
                        * Total registros: <?php echo $controlador->deducciones->n_registros; ?><br />
                        * Fecha Hora: <?php echo $controlador->fecha_hoy; ?>
                    </div>

                </div>
            </div> <!-- /. widget-table-->
        </div><!-- /.center-content -->
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <div class="widget widget-box box-container widget-mylistings">

                <div class="">
                    <div class="widget-header">
                        <h2>Otros Pagos</h2>
                    </div>

                    <table class="table table-striped footable-sort" data-sorting="true">
                        <th>Id</th>
                        <th>Codigo</th>
                        <th>Descripcion</th>
                        <th>Importe Gravado</th>
                        <th>Importe Exento</th>
                        <th>Modifica</th>
                        <th>Elimina</th>

                        <tbody>
                        <?php foreach ($controlador->otros_pagos->registros as $otro_pago){?>
                            <tr>
                                <td><?php echo $otro_pago['nom_par_otro_pago_id']; ?></td>
                                <td><?php echo $otro_pago['nom_otro_pago_codigo']; ?></td>
                                <td><?php echo $otro_pago['nom_par_otro_pago_descripcion']; ?></td>
                                <td><?php echo $otro_pago['nom_par_otro_pago_importe_gravado']; ?></td>
                                <td><?php echo $otro_pago['nom_par_otro_pago_importe_exento']; ?></td>
                                <td><?php echo $otro_pago['link_modifica']; ?></td>
                                <td><?php echo $otro_pago['link_elimina']; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div class="box-body">
                        * Total registros: <?php echo $controlador->otros_pagos->n_registros; ?><br />
                        * Fecha Hora: <?php echo $controlador->fecha_hoy; ?>
                    </div>

                </div>
            </div> <!-- /. widget-table-->
        </div><!-- /.center-content -->
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <div class="widget widget-box box-container widget-mylistings">

                <div class="">
                    <div class="widget-header">
                        <h2>Cuotas - Obrero Patronales</h2>
                    </div>

                    <table class="table table-striped footable-sort" data-sorting="true">
                        <th>Concepto</th>
                        <th>Monto</th>

                        <tbody>
                        <?php foreach ($controlador->cuotas_obrero_patronales->registros as $cuota_obrero_patronal){?>
                            <tr>
                                <td><?php echo $cuota_obrero_patronal['nom_tipo_concepto_imss_descripcion']; ?></td>
                                <td><?php echo $cuota_obrero_patronal['nom_concepto_imss_monto']; ?></td>
                            </tr>
                        <?php } ?>
                            <tr>
                                <td style="text-align: right">Total:</td>
                                <td><?php echo $controlador->cuota_total; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> <!-- /. widget-table-->
        </div><!-- /.center-content -->
    </div>
</div>
