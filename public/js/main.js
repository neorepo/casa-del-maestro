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

function initOnchangeProvincia() {
    // Si no existe el elemento select provincia, detenemos el proceso
    if (!selectProvincia) return;
    selectProvincia.onchange = function (e) {
        // Si no existe el elemento select localidad, detenemos el proceso
        if (!selectLocalidad) return;
        let idProvincia = this.value;
        if (idProvincia === '5') {
            reset();
            let newOption = document.createElement("option");
            newOption.value = 5001;
            newOption.text = "CIUDAD AUTONOMA DE BUENOS AIRES";
            try {
                selectLocalidad.add(newOption);
            } catch (e) {
                selectLocalidad.appendChild(newOption);
            }
            return;
        }
        // Validamos el id de provincia
        if (!validId(idProvincia, 1, 24)) {
            reset();
            return;
        }
        // S todo esta Okay enviamos la solicitud al servidor
        let data = "id_provincia=" + encodeURIComponent(idProvincia);
        sendHttpRequest('POST', 'server_processing.php', data, loadLocalities);
    }
}

function reset() {
    selectLocalidad.options.length = 0;
    selectLocalidad.options[0] = new Option("- Seleccionar -");
    selectLocalidad.options[0].value = 0;
}

function loadLocalities(response) {
    let newOption;
    const $fragment = document.createDocumentFragment();
    let data = JSON.parse(response);
    if (data.success) {
        reset();
        data.localidades.forEach(item => {
            newOption = document.createElement("option");
            newOption.value = item.id_localidad;
            newOption.text = `${item.nombre} (${item.cp})`;
            // add the new option 
            try {
                // this will fail in DOM browsers but is needed for IE
                $fragment.add(newOption);
            } catch (e) {
                $fragment.appendChild(newOption);
            }
        });
        selectLocalidad.appendChild($fragment);
    }
}
// Fin el proceso carga de localidades según la provincia seleccionada

function formSubmissionHandler() {
    const formEl = document.querySelector('form');
    // Evitar enviar el formulario presionando la tecla ENTER en input field
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
    if (showPsw) {
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
}

function initFlashes() {
    const obj = document.querySelector('div.alert');
    if (obj) {
        obj.forEach(el => { setTimeout(function () { el.remove(); }, 7000); });
    }
}

// Focus en el primer error que exista en los formularios de registro.
function initErrorFields() {
    const obj = document.querySelector('.is-invalid');
    if (obj) {
        obj.focus();
    }
}