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
                    <form method="post" action="<?php echo $controlador->link_org_sucursal_alta_bd; ?>" class="form-additional">
                        <?php echo $controlador->inputs->id; ?>
                        <?php echo $controlador->inputs->codigo; ?>
                        <?php echo $controlador->inputs->codigo_bis; ?>
                        <?php echo $controlador->inputs->descripcion; ?>
                        <?php echo $controlador->inputs->descripcion_select; ?>
                        <?php echo $controlador->inputs->alias; ?>
                        <?php echo $controlador->inputs->select->im_registro_patronal_id; ?>
                        <?php echo $controlador->inputs->select->em_empleado_id; ?>
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
                        <?php include (new views())->ruta_templates.'botons/submit/alta_bd_otro.php';?>

                        <div class="control-group btn-alta">
                            <div class="controls">
                                <?php include 'templates/botons/nom_nomina_crea_nomina.php';?>
                            </div>
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
                        <th>Modifica</th>
                        <th>Elimina</th>

                        <tbody>
                        <?php foreach ($controlador->percepciones->registros as $percepcion){?>
                            <tr>
                                <td><?php echo $percepcion['nom_percepcion_id']; ?></td>
                                <td><?php echo $percepcion['nom_percepcion_codigo']; ?></td>
                                <td><?php echo $percepcion['nom_percepcion_descripcion']; ?></td>
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
                        <th>Modifica</th>
                        <th>Elimina</th>

                        <tbody>
                            <?php foreach ($controlador->deducciones->registros as $deduccion){?>
                                <tr>
                                    <td><?php echo $deduccion['nom_deduccion_id']; ?></td>
                                    <td><?php echo $deduccion['nom_deduccion_codigo']; ?></td>
                                    <td><?php echo $deduccion['nom_deduccion_descripcion']; ?></td>
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
