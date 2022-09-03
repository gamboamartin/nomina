<?php use config\views; ?>
<?php /** @var stdClass $row  viene de registros del controler*/ ?>
<tr>
    <td><?php echo $row->nom_periodo_id; ?></td>
    <td><?php echo $row->nom_periodo_codigo; ?></td>
    <td><?php echo $row->nom_periodo_descripcion; ?></td>
    <!-- Dynamic generated -->
    <td><?php echo $row->nom_periodo_fecha_inicial_pago; ?></td>
    <td><?php echo $row->nom_periodo_fecha_final_pago; ?></td>
    <td><?php echo $row->nom_periodo_fecha_pago; ?></td>

    <td><?php include 'templates/botons/nom_periodo/link_procesa_nomina.php';?></td>

    <!-- End dynamic generated -->

    <?php include (new views())->ruta_templates.'listas/action_row.php';?>
</tr>
