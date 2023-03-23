let url = getAbsolutePath();
let session_id = getParameterByName('session_id');

let sl_nom_empleado = $("#em_empleado_id");
let sl_em_cuenta_bancaria_id = $("#em_cuenta_bancaria_id");

sl_nom_empleado.change(function(){
    let selected = $(this).find('option:selected');
    let em_empleado_id = $(this).val();
    let url = "index.php?seccion=em_cuenta_bancaria&ws=1&accion=get_cuentas_bancarias&em_empleado_id="+em_empleado_id+"&session_id="+session_id;

    getData(url,(data) => {
        sl_em_cuenta_bancaria_id.empty();
        integra_new_option("#em_cuenta_bancaria_id",'Seleccione una cuenta bancaria','');

        $.each(data.registros, function( index, em_cuenta_bancaria ) {
            integra_new_option("#em_cuenta_bancaria_id",em_cuenta_bancaria.bn_banco_descripcion_select+' '+em_cuenta_bancaria.em_cuenta_bancaria_num_cuenta,em_cuenta_bancaria.em_cuenta_bancaria_id);
        });

        sl_em_cuenta_bancaria_id.selectpicker('refresh');
    });
});

let getData = async (url, acciones) => {
    fetch(url)
        .then(response => response.json())
        .then(data => acciones(data))
        .catch(err => {
            alert('Error al ejecutar');
            console.error("ERROR: ", err.message)
        });
}
