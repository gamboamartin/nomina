<?php use config\views; ?>
<?php /** @var stdClass $row  viene de registros del controler*/ ?>
<tr>
    <td><?php echo $row->nom_percepcion_id; ?></td>
    <td><?php echo $row->nom_percepcion_codigo; ?></td>
    <td><?php echo $row->nom_percepcion_codigo_bis; ?></td>
    <!-- Dynamic generated -->
    <td><?php echo $row->nom_percepcion_descripcion; ?></td>
    <td><?php echo $row->nom_percepcion_descripcion_select; ?></td>
    <td><?php echo $row->nom_percepcion_alias; ?></td>
    <td><?php include 'templates/botons/nom_percepcion/link_estado_subsidio.php';?></td>

    <!-- End dynamic generated -->

    <?php include (new views())->ruta_templates.'listas/action_row.php';?>
</tr>
