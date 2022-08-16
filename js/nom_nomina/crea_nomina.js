
let sl_nom_empleado = $("#em_empleado_id");

let txt_rfc = $('#rfc');


sl_nom_empleado.change(function(){

    let selected = $(this).find('option:selected');

    let rfc = selected.data('em_empleado_rfc');
    txt_rfc.val(rfc);

   

});

