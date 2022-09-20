
let sl_nom_percepcion = $("#nom_percepcion_id");

let txt_descripcion = $('#descripcion');



sl_nom_percepcion.change(function(){
    let selected = $(this).find('option:selected');

    let descripcion = selected.data('nom_percepcion_descripcion');

    txt_descripcion.val(descripcion);
});





