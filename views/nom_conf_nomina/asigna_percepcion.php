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
                        <?php echo $controlador->inputs->codigo_bis; ?>
                        <?php echo $controlador->inputs->descripcion; ?>
                        <?php echo $controlador->inputs->select->nom_conf_nomina_id; ?>
                        <?php echo $controlador->inputs->select->nom_percepcion_id; ?>
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
        <div class="row">
            <div class="col-lg-12">
                <div class="widget widget-box box-container widget-mylistings">
                    <div class="">
                        <div class="widget-header">
                            <h2>Percepciones Asignadas</h2>
                        </div>
                        <table class="table table-striped footable-sort" data-sorting="true">
                                        <thead>
                                        <tr>
                                            <th data-breakpoints="xs sm md" data-type="html">Id</th>
                                            <th data-breakpoints="xs sm md" data-type="html">Codigo </th>

                                            <th data-breakpoints="xs sm md"  data-type="html">Codigo Bis</th>
                                            <th data-breakpoints="xs sm md"  data-type="html">Descripcion</th>
                                            <th data-breakpoints="xs sm md"  data-type="html">Descripcion Select</th>
                                            <th data-breakpoints="xs sm md"  data-type="html">Alias</th>

                                            <th data-breakpoints="xs md" class="control"  data-type="html">Modifica</th>
                                            <th data-breakpoints="xs md" class="control"  data-type="html">Elimina</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        <?php foreach ($controlador->percepciones->registros as $percepcion){?>
                                            <tr>
                                                <td><?php echo $percepcion['nom_conf_percepcion_id']; ?></td>
                                                <td><?php echo $percepcion['nom_conf_percepcion_codigo']; ?></td>
                                                <td><?php echo $percepcion['nom_conf_percepcion_codigo_bis']; ?></td>
                                                <td><?php echo $percepcion['nom_conf_percepcion_descripcion']; ?></td>
                                                <td><?php echo $percepcion['nom_conf_percepcion_descripcion_select']; ?></td>
                                                <td><?php echo $percepcion['nom_conf_percepcion_alias']; ?></td>
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

</main>







