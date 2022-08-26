<?php /** @var \gamboamartin\nomina\controllers\controlador_nom_nomina $controlador */ ?>

<a href="index.php?seccion=nom_nomina&accion=nueva_percepcion&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>" class="btn btn-info"><i class="icon-edit"></i>
    Nueva Percepcion
</a>

<a href="index.php?seccion=nom_nomina&accion=nueva_deduccion&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>" class="btn btn-info"><i class="icon-edit"></i>
    Nueva Deduccion
</a>

<a href="index.php?seccion=nom_nomina&accion=otro_pago&registro_id=<?php echo $controlador->nom_nomina_id; ?>&session_id=<?php echo $controlador->session_id; ?>" class="btn btn-info"><i class="icon-edit"></i>
    Otros Pagos
</a>