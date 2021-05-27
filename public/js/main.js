'use strict';

// $(document).ready(function () {
// });

document.addEventListener('DOMContentLoaded', () => {
    initDataTable();
    initChangeProvincia();
    // initChangeLocalidad();
    // initPreventKeyboard();
    initErrorFields();
    initShowPasswords();
    initFlashes();
    // formSubmissionHandler();
});

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

function initDataTable() {
    $('#tabla-asociado').DataTable({
        // "order": [[ 0, "desc" ]],
        // "pagingType": "full_numbers",
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontró nada, lo siento",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "",
            "sSearchPlaceholder": 'Buscar...',
            "oPaginate": {
                "sFirst": "Primero",
                "sPrevious": "Anterior",
                "sNext": "Siguiente",
                "sLast": "Último"
            }
        }
    });
}

// Inicio el proceso carga de localidades según la provincia seleccionada
const selectP = document.querySelector("select#id-provincia");
const selectL = document.querySelector("select#id-localidad");

// Inicializa el proceso de cambio de Provincia
function initChangeProvincia() {
    // Si no existe el elemento select provincia, detenemos el proceso
    if (!selectP) return;
    selectP.onchange = function (e) { return handleChangeProvincia(this, e); }
}

/*function initChangeLocalidad() {
    // Si no existe el elemento select localidad, detenemos el proceso
    if (!selectL) return;
    // selectL.onchange = function (e) { return handleChangeLocalidad(this, e); }
}*/

// Manejador del cambio de Provincia
function handleChangeProvincia(objSelect, objEvent) {
    // Si no existe el elemento select localidad, detenemos el proceso.
    if (!selectL) return;
    // Si existen opciones en el select de localidades, las removemos.
    removeOptions(selectL);
    // Deshabilitamos el select de localidades
    selectL.disabled = true;
    // Si la opción elegida es distinta del marcador de posición
    if (objSelect.selectedIndex > 0) {
        // Obtenemos el valor de la provincia seleccionada, en este caso el id
        const idProvincia = objSelect.value;
        // Validamos el id de la provincia seleccionada, si no es válido detenemos el proceso.
        if (!validId(idProvincia, 1, 24)) return;
        // Si el valor es 5, creamos una opción por defecto.
        if (idProvincia === '5') {
            createOptions(selectL, [{ id_localidad: "5001", nombre: "CIUDAD AUTONOMA DE BUENOS AIRES", cp: "" }]);
        }
        // Enviamos la solicitud al servidor.
        else {
            const data = "id_provincia=" + encodeURIComponent(idProvincia);
            sendHttpRequest('POST', 'server_processing.php', data, loadLocalities);
        }
        // Habilitamos el select de localidades
        selectL.disabled = false;
    }
}

// Carga localidades
function loadLocalities(response) {
    const data = JSON.parse(response);
    if (!data.success) return;
    createOptions(selectL, data.localidades);
}

// Remueve opciones en elementos select
function removeOptions(objSelect) {
    let len = objSelect.options.length;
    while (len-- > 1) objSelect.remove(1);
}

// Crea opciones en elementos select
function createOptions(selectObj, data) {
    let newOpt;
    const fragment = document.createDocumentFragment();
    data.forEach(obj => {
        newOpt = document.createElement('option');
        newOpt.value = obj.id_localidad;
        newOpt.text = `${obj.nombre} (${obj.cp})`;
        // add the new option 
        try {
            // this will fail in DOM browsers but is needed for IE
            fragment.add(newOpt);
        } catch (e) {
            fragment.appendChild(newOpt);
        }
    });
    selectObj.appendChild(fragment);
}
// Fin del proceso carga de localidades según la provincia seleccionada

// Evitar enviar el formulario presionando la tecla ENTER en input field
function formSubmissionHandler() {
    const formEl = document.querySelector('form');
    if (!formEl) return;
    // También se puede utilizar el evento onkeydown
    formEl.onkeypress = function (e) { return disableSendWithEnter(this, e); }
}

function disableSendWithEnter(obj, objEvent) {
    var iKeyCode;
    // console.log(objEvent.target.tagName);
    if (objEvent && objEvent.type == 'keypress') {
        if (objEvent.keyCode)
            iKeyCode = objEvent.keyCode;
        else if (objEvent.which)
            iKeyCode = objEvent.which;
        if (iKeyCode == 13)
            return false;
    }
}

function initShowPasswords() {
    const showPsw = document.querySelector('#show-password');
    if (!showPsw) return;
    showPsw.onclick = function (e) {
        const psw1 = document.querySelector('#password');
        const psw2 = document.querySelector('#confirm-password');
        if (psw1 && psw2) {
            if (psw1.type === 'password' && psw2.type === 'password') {
                psw1.type = psw2.type = 'text';
            } else {
                psw1.type = psw2.type = 'password';
            }
        }
    }
}

function initFlashes() {
    const obj = document.querySelector('.alert');
    if (obj) {
        // obj.forEach(el => { setTimeout(function () { el.remove(); }, 7000); });
        setTimeout(function () { obj.remove(); }, 7000);
    }
}

// Focus en el primer error que exista en los formularios de registro.
function initErrorFields() {
    const obj = document.querySelector('.is-invalid');
    if (obj) obj.focus();
}

function print(el, message) {
    const obj = document.querySelector(el);
    if (obj) obj.innerHTML = message;
}

function initPreventKeyboard() {
    window.oncontextmenu = (e) => { e.preventDefault(); }
    window.onkeydown = (e) => {
        if ((e.ctrlKey && e.shiftKey && e.keyCode == 73) ||
            (e.ctrlKey && e.keyCode == 85)) {
            e.preventDefault();
        }
    }
}