'use strict';

$(document).ready(function () {
    initDataTable();
});

document.addEventListener('DOMContentLoaded', () => {
    initOnchangeProvincia();
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
const selectProvincia = document.querySelector("select#id-provincia");
const selectLocalidad = document.querySelector("select#id-localidad");
const fragment = document.createDocumentFragment();

function initOnchangeProvincia() {
    // Si no existe el elemento select provincia, detenemos el proceso
    if (!selectProvincia) return;
    selectProvincia.onchange = function (e) { return handleChange(this, e); }
}

function handleChange(objSelect, objEvent) {
    // Si no existe el elemento select localidad, detenemos el proceso.
    if (!selectLocalidad) return;
    // Si existen opciones, las removemos.
    removeOptions(selectLocalidad);
    const idProvincia = objSelect.value;
    // Validamos el id de provincia, si no es válido detenemos el proceso.
    if (!validId(idProvincia, 1, 24)) return;
    // Si la opción es 5, creamos una opción por defecto.
    if (idProvincia === '5') {
        const newOption = document.createElement("option");
        newOption.value = 5001;
        newOption.text = "CIUDAD AUTONOMA DE BUENOS AIRES";
        try {
            selectLocalidad.add(newOption);
        } catch (e) {
            selectLocalidad.appendChild(newOption);
        }
    } else {
        // Si todo esta okay enviamos la solicitud al servidor
        const data = "id_provincia=" + encodeURIComponent(idProvincia);
        sendHttpRequest('POST', 'server_processing.php', data, loadLocalities);
    }
}

function removeOptions(objSelect) {
    // selectLocalidad.options.length = 0;
    let len = objSelect.options.length;
    while (len-- > 1) {
        objSelect.remove(1);
    }
}

function loadLocalities(response) {
    let newOption;
    const data = JSON.parse(response);
    data.forEach(obj => {
        newOption = document.createElement("option");
        newOption.value = obj.id_localidad;
        newOption.text = `${obj.nombre} (${obj.cp})`;
        // add the new option 
        try {
            // this will fail in DOM browsers but is needed for IE
            fragment.add(newOption);
        } catch (e) {
            fragment.appendChild(newOption);
        }
    });
    selectLocalidad.appendChild(fragment);

}
// Fin el proceso carga de localidades según la provincia seleccionada

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
    const obj = document.querySelector('div.alert');
    if (obj) {
        // obj.forEach(el => { setTimeout(function () { el.remove(); }, 7000); });
        setTimeout(function () { obj.remove(); }, 7000);
    }
}

// Focus en el primer error que exista en los formularios de registro.
function initErrorFields() {
    // Si no voy a reasignar la variable obj, entonces puedo utilizar una constante
    const obj = document.querySelector('.is-invalid');
    if (obj) {
        obj.focus();
    }
}