<?php /** @var \gamboamartin\nomina\controllers\controlador_nom_periodo $controlador  controlador en ejecucion */ ?>
<?php use config\views; ?>

<main class="main section-color-primary">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">

                <?php include (new views())->ruta_templates."head/title.php"; ?>
                <?php include (new views())->ruta_templates."mensajes.php"; ?>
            </div>
            <div class="col-lg-12">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="widget widget-box box-container widget-mylistings">

                                <div class="">
                                    <div class="widget-header">
                                        <h2>Nominas</h2>
                                    </div>

                                    <table class="table table-striped footable-sort" data-sorting="true">
                                        <thead>
                                        <tr>
                                            <th data-breakpoints="xs sm md" data-type="html">Id</th>
                                            <th data-breakpoints="xs sm md" data-type="html">Codigo Nomina</th>

                                            <th data-breakpoints="xs sm md"  data-type="html">Codigo Empleado</th>
                                            <th data-breakpoints="xs sm md"  data-type="html">Empleado</th>
                                            <th data-breakpoints="xs sm md"  data-type="html">Fecha Inicial</th>
                                            <th data-breakpoints="xs sm md"  data-type="html">Fecha Final</th>
                                            <th data-breakpoints="xs sm md"  data-type="html">Rfc</th>

                                            <th data-breakpoints="xs md" class="control"  data-type="html">Modifica</th>
                                            <th data-breakpoints="xs md" class="control"  data-type="html">Elimina</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <?php foreach ($controlador->nominas->registros as $nomina){?>
                                            <tr>
                                                <td><?php echo $nomina['nom_nomina_id']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_codigo']; ?></td>
                                                <td><?php echo $nomina['em_empleado_codigo']; ?></td>
                                                <td><?php echo $nomina['em_empleado_codigo']. ' '.$nomina['em_empleado_ap'].' '.$nomina['em_empleado_am']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_fecha_inicial_pago']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_fecha_final_pago']; ?></td>
                                                <td><?php echo $nomina['org_empresa_rfc']; ?></td>
                                                <td><?php echo $nomina['link_modifica']; ?></td>
                                                <td><?php echo $nomina['link_elimina']; ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    <div class="box-body">
                                        * Total registros: <?php echo $controlador->nominas->n_registros; ?><br />
                                        * Fecha Hora: <?php echo $controlador->fecha_hoy; ?>
                                    </div>
                                </div>
                            </div> <!-- /. widget-table-->
                        </div><!-- /.center-content -->
                    </div>
                </div>

            </div>
        </div>


    </div>
    <br>



</main>





