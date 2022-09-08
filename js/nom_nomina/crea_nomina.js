let url = getAbsolutePath();

let session_id = getParameterByName('session_id');

let sl_nom_empleado = $("#em_empleado_id");
let sl_cat_sat_periodicidad_pago_nom = $("#cat_sat_periodicidad_pago_nom_id");
let sl_em_cuenta_bancaria_id = $("#em_cuenta_bancaria_id");
let sl_nom_conf_empleado = $("#nom_conf_empleado_id");
let sl_cat_sat_tipo_nomina = $("#cat_sat_tipo_nomina_id");
let sl_org_puesto = $("#org_puesto_id");

let txt_rfc = $('#rfc');
let txt_curp = $('#curp');
let txt_nss = $('#nss');
let txt_salario_diario = $('#salario_diario');
let txt_salario_diario_integrado = $('#salario_diario_integrado');
let txt_fecha_inicio_rel_laboral = $('#fecha_inicio_rel_laboral');
let txt_num_dias_pagados = $('#num_dias_pagados');
let txt_fecha_inicial_pago = $('#fecha_inicial_pago');
let txt_fecha_final_pago = $('#fecha_final_pago');
let txt_subtotal = $('#subtotal');
let txt_descuento = $('#descuento');
let txt_total = $('#total');

let configuraciones = {};


sl_nom_empleado.change(function(){
    let selected = $(this).find('option:selected');

    let rfc = selected.data('em_empleado_rfc');
    let curp = selected.data('em_empleado_curp');
    let nss = selected.data('em_empleado_nss');
    let salario_diario = selected.data('em_empleado_salario_diario');
    let salario_diario_integrado = selected.data('em_empleado_salario_diario_integrado');
    let fecha_inicio_rel_laboral  = selected.data('em_empleado_fecha_inicio_rel_laboral');
    let org_puesto_id  = selected.data('em_empleado_org_puesto_id');

    txt_rfc.val(rfc);
    txt_curp.val(curp);
    txt_nss.val(nss);
    txt_salario_diario.val(salario_diario);
    txt_salario_diario_integrado.val(salario_diario_integrado);
    txt_fecha_inicio_rel_laboral.val(fecha_inicio_rel_laboral);

    let fechaInicioRelLaboral = new Date(txt_fecha_inicio_rel_laboral.val());
    let fechaInicialPago = new Date(txt_fecha_inicial_pago.val());

    if(fechaInicioRelLaboral > fechaInicialPago){
        txt_fecha_inicial_pago.val(fecha_inicio_rel_laboral);
    }

    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    let total = sub_Total - txt_descuento.val()

    txt_subtotal.val(sub_Total)
    txt_total.val(total)

    em_empleado_id = $(this).val();

    let url = "index.php?seccion=em_cuenta_bancaria&ws=1&accion=get_cuentas_bancarias&em_empleado_id="+em_empleado_id+"&session_id="+session_id;

    getData(url,(data) => {
        sl_em_cuenta_bancaria_id.empty();
        integra_new_option("#em_cuenta_bancaria_id",'Seleccione una cuenta bancaria','');

        $.each(data.registros, function( index, em_cuenta_bancaria ) {

            integra_new_option("#em_cuenta_bancaria_id",em_cuenta_bancaria.bn_banco_descripcion_select+' '+em_cuenta_bancaria.em_cuenta_bancaria_num_cuenta,em_cuenta_bancaria.em_cuenta_bancaria_id);
        });

        sl_em_cuenta_bancaria_id.selectpicker('refresh');
    });

    let url_conf = "index.php?seccion=nom_conf_empleado&ws=1&accion=get_configuraciones_empleado&em_empleado_id="+em_empleado_id+"&session_id="+session_id;

    getData(url_conf,(data) => {
        sl_nom_conf_empleado.empty();

        integra_new_option("#nom_conf_empleado_id",'Seleccione una configuración','');

        $.each(data.registros, function( index, nom_conf_empleado ) {
            configuraciones[nom_conf_empleado.nom_conf_empleado_id] = nom_conf_empleado;
            integra_new_option("#nom_conf_empleado_id",nom_conf_empleado.nom_conf_empleado_descripcion+' '+nom_conf_empleado.em_empleado_id,nom_conf_empleado.nom_conf_empleado_id);
        });

        sl_nom_conf_empleado.selectpicker('refresh');
    });

    sl_cat_sat_tipo_nomina.val("").change();
    sl_cat_sat_periodicidad_pago_nom.val("").change();
    sl_org_puesto.val(org_puesto_id).change();
});

sl_nom_conf_empleado.change(function () {
    let selected = $(this).find('option:selected').val();

    if (selected !== "") {
        let elemento = Object.keys(configuraciones)
            .filter((key) => key.includes(selected))
            .reduce((obj, key) => {
                return configuraciones[key];
            }, {});
        sl_cat_sat_tipo_nomina.val(elemento.nom_conf_nomina_cat_sat_tipo_nomina_id).change();
        sl_cat_sat_periodicidad_pago_nom.val(elemento.nom_conf_nomina_cat_sat_periodicidad_pago_nom_id).change();
    } else {
        sl_cat_sat_tipo_nomina.val("").change();
        sl_cat_sat_periodicidad_pago_nom.val("").change();
    }
})

let getData = async (url, acciones) => {
     fetch(url)
        .then(response => response.json())
        .then(data => acciones(data))
        .catch(err => {
            alert('Error al ejecutar');
            console.error("ERROR: ", err.message)
        });
}

txt_descuento.change(function() {

    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    let total = sub_Total - txt_descuento.val()

    txt_total.val(total)

});

sl_cat_sat_periodicidad_pago_nom.change(function(){
    let selected = $(this).find('option:selected');
    let num_dias_pagados = selected.data('cat_sat_periodicidad_pago_nom_n_dias');

    let fechaFinal

    if (selected.val() !== '') {
         fechaFinal = fecha(txt_fecha_inicial_pago, num_dias_pagados)
    } else {
         fechaFinal = fecha(txt_fecha_inicial_pago)
    }
    txt_num_dias_pagados.val(num_dias_pagados);
    txt_fecha_final_pago.val(fechaFinal)
    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    let total = sub_Total - txt_descuento.val()

    txt_subtotal.val(sub_Total)
    txt_total.val(total)
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

txt_num_dias_pagados.change(function() {
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
    let sub_Total = subTotal(txt_salario_diario.val(),txt_num_dias_pagados.val())
    txt_subtotal.val(sub_Total)
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



