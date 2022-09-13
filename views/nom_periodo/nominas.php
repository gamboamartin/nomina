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
                                        <th>Id</th>
                                        <th>Codigo</th>
                                        <th>Codigo Bis</th>
                                        <th>Descripcion</th>
                                        <th>Descripcion Select</th>
                                        <th>Alias</th>
                                        <th>Modifica</th>
                                        <th>Elimina</th>
                                        <tbody>

                                        <?php foreach ($controlador->nominas->registros as $nomina){?>
                                            <tr>
                                                <td><?php echo $nomina['nom_nomina_id']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_codigo']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_codigo_bis']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_descripcion']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_descripcion_select']; ?></td>
                                                <td><?php echo $nomina['nom_nomina_alias']; ?></td>
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





