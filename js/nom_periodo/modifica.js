let url = getAbsolutePath();

let session_id = getParameterByName('session_id');

let sl_cat_sat_periodicidad_pago_nom = $("#cat_sat_periodicidad_pago_nom_id");

let txt_fecha_inicial_pago = $('#fecha_inicial_pago');
let txt_fecha_final_pago = $('#fecha_final_pago');



sl_cat_sat_periodicidad_pago_nom.change(function(){
    let selected = $(this).find('option:selected');
    let num_dias_pagados = selected.data('cat_sat_periodicidad_pago_nom_n_dias');

    let fechaFinal

    if (selected.val() !== '') {
         fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    } else {
         fechaFinal = fecha(txt_fecha_inicial_pago)
    }
    txt_fecha_final_pago.val(fechaFinal)

});

txt_fecha_inicial_pago.change(function() {
    let selected = sl_cat_sat_periodicidad_pago_nom.find('option:selected');
    let num_dias_pagados = selected.data('cat_sat_periodicidad_pago_nom_n_dias');

    let fechaFinal

    if (selected.val() !== '') {
        fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    } else {
        num_dias_pagados = (txt_num_dias_pagados.val() !== '') ? txt_num_dias_pagados.val() : 1
        fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    }
    txt_fecha_final_pago.val(fechaFinal)
});


let fecha = (fechaInicio, numDias = 1) => {

    var fechaInicial = new Date(fechaInicio.val());
    var fechaFinal   = new Date(fechaInicio.val());
    var dias = parseInt(numDias);

    fechaFinal.setDate(fechaInicial.getDate() + dias);
    return fechaFinal.getFullYear() + "-" +  ('0' + (fechaFinal.getMonth()+1)).slice(-2) + "-" + ('0' + fechaFinal.getDate()).slice(-2);
};

let subTotal = (salario = 0, diasPagados = 0) => {
   return salario * diasPagados
};



