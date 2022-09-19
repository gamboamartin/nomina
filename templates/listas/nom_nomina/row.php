<?php use config\views; ?>
<?php /** @var stdClass $row  viene de registros del controler*/ ?>
<tr>
    <td><?php echo $row->nom_nomina_id; ?></td>
    <td><?php echo $row->nom_nomina_codigo; ?></td>
    <!-- Dynamic generated -->
    <td><?php echo $row->em_empleado_codigo; ?></td>
    <td><?php echo $row->em_empleado_nombre. ' '. $row->em_empleado_ap. ' '.$row->em_empleado_am; ?></td>
    <td><?php echo $row->nom_nomina_fecha_inicial_pago; ?></td>
    <td><?php echo $row->nom_nomina_fecha_final_pago; ?></td>
    <td><?php echo $row->org_empresa_rfc; ?></td>

    <td><?php include 'templates/botons/nom_nomina/link_genera_xml.php';?></td>

    <!-- End dynamic generated -->

    <?php include (new views())->ruta_templates.'listas/action_row.php';?>
</tr>
