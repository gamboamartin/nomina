<?php use config\views; ?>
<section class="top-title">

    <ul class="breadcrumb">
        <?php include (new views())->ruta_templates."breadcrumb/adm_session/inicio.php"; ?>
        <li class="item"><a href="<?php  echo $controlador->link_crea_nomina; ?>"> Nueva nomina </a></li>
        <li class="item"> Lista  </li>
    </ul>
    
</section> <!-- /. content-header -->
