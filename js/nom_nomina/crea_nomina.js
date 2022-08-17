
let sl_nom_empleado = $("#em_empleado_id");
let sl_cat_sat_periodicidad_pago_nom = $("#cat_sat_periodicidad_pago_nom_id");


let txt_rfc = $('#rfc');
let txt_curp = $('#curp');
let txt_nss = $('#nss');
let txt_salario_diario = $('#salario_diario');
let txt_salario_diario_integrado = $('#salario_diario_integrado');
let txt_fecha_inicio_rel_laboral = $('#fecha_inicio_rel_laboral');
let txt_num_dias_pagados = $('#num_dias_pagados');

sl_nom_empleado.change(function(){
    let selected = $(this).find('option:selected');

    let rfc = selected.data('em_empleado_rfc');
    let curp = selected.data('em_empleado_curp');
    let nss = selected.data('em_empleado_nss');
    let salario_diario = selected.data('em_empleado_salario_diario');
    let salario_diario_integrado = selected.data('em_empleado_salario_diario_integrado');
    let fecha_inicio_rel_laboral  = selected.data('em_empleado_fecha_inicio_rel_laboral');

    txt_rfc.val(rfc);
    txt_curp.val(curp);
    txt_nss.val(nss);
    txt_salario_diario.val(salario_diario);
    txt_salario_diario_integrado.val(salario_diario_integrado);
    txt_fecha_inicio_rel_laboral.val(fecha_inicio_rel_laboral);
});

sl_cat_sat_periodicidad_pago_nom.change(function(){
    let selected = $(this).find('option:selected');

    let num_dias_pagados = selected.data('cat_sat_periodicidad_pago_nom_n_dias');

    txt_num_dias_pagados.val(num_dias_pagados);
});

