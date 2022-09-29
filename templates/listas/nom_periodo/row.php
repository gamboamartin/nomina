<?php use config\views; ?>
<?php /** @var stdClass $row  viene de registros del controler*/ ?>
<tr>
    <td><?php echo $row->nom_periodo_id; ?></td>
    <td><?php echo $row->nom_periodo_codigo; ?></td>
    <!-- Dynamic generated -->
    <td><?php echo $row->nom_periodo_fecha_inicial_pago; ?></td>
    <td><?php echo $row->nom_periodo_fecha_final_pago; ?></td>
    <td><?php echo $row->nom_periodo_fecha_pago; ?></td>
    <td><?php echo $row->total_cuota_patronal; ?></td>

    <td><?php include 'templates/botons/nom_periodo/link_sube_archivo.php';?></td>
    <td><?php include 'templates/botons/nom_periodo/link_periodo_nominas.php';?></td>

    <!-- End dynamic generated -->

    <?php include (new views())->ruta_templates.'listas/action_row.php';?>
</tr>
