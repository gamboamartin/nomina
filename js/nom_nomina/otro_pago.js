
let sl_nom_otro_pago = $("#nom_otro_pago_id");

let txt_descripcion = $('#descripcion');


sl_nom_otro_pago.change(function(){
    let selected = $(this).find('option:selected');

    let descripcion = selected.data('nom_otro_pago_descripcion');

    txt_descripcion.val(descripcion);
});





