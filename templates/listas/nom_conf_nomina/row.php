<?php use config\views; ?>
<?php /** @var stdClass $row  viene de registros del controler*/ ?>
<tr>
    <td><?php echo $row->nom_conf_nomina_id; ?></td>
    <td><?php echo $row->nom_conf_nomina_codigo; ?></td>
    <!-- Dynamic generated -->
    <td><?php echo $row->nom_conf_nomina_codigo_bis; ?></td>
    <td><?php echo $row->nom_conf_nomina_descripcion; ?></td>
    <td><?php echo $row->nom_conf_nomina_descripcion_select; ?></td>
    <td><?php echo $row->nom_conf_nomina_alias; ?></td>

    <td><?php include 'templates/botons/nom_conf_nomina/link_asigna_percepcion.php';?></td>

    <!-- End dynamic generated -->

    <?php include (new views())->ruta_templates.'listas/action_row.php';?>
</tr>
