
let sl_nom_deduccion = $("#nom_deduccion_id");

let txt_descripcion = $('#descripcion');



sl_nom_deduccion.change(function(){
    let selected = $(this).find('option:selected');

    let descripcion = selected.data('nom_deduccion_descripcion');

    txt_descripcion.val(descripcion);
});





