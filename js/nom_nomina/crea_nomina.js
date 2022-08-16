
let sl_nom_empleado = $("#em_empleado_id");

let txt_rfc = $('#rfc');
let txt_curp = $('#curp');
let txt_nss = $('#nss');


sl_nom_empleado.change(function(){

    let selected = $(this).find('option:selected');

    let rfc = selected.data('em_empleado_rfc');
    let curp = selected.data('em_empleado_curp');
    let nss = selected.data('em_empleado_nss');

    txt_rfc.val(rfc);
    txt_curp.val(curp);
    txt_nss.val(nss);
});

