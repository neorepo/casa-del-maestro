'use strict';

const provincia = document.querySelector("#id-provincia");
const localidad = document.querySelector("#id-localidad");

$(document).ready(function () {
    initDataTable();
});

document.addEventListener('DOMContentLoaded', () => {
    initOnchangeProvincia();
    initErrorFields();
    initShowPasswords();
    initFlashes();
    // preventFormSubmit();
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


function initOnchangeProvincia() {
    // Si existe el id de provincia y el id de localidad 
    if (!provincia && !localidad) return;

    provincia.onchange = function () {
        if (this.value === '5') {
            reset();
            let newOption = document.createElement("option");
            newOption.value = 5001;
            newOption.text = "CIUDAD AUTONOMA DE BUENOS AIRES";
            try {
                localidad.add(newOption);
            } catch (e) {
                localidad.appendChild(newOption);
            }
            return;
        }
        // Validamos el id de provincia
        if (!validId(this.value, 1, 24)) {
            reset();
            return;
        }

        let data = "id_provincia=" + encodeURIComponent(this.value);
        sendHttpRequest('POST', 'server_processing.php', data, loadLocalities);
    }
}

function reset() {
    localidad.options.length = 0;
    localidad.options[0] = new Option("- Seleccionar -");
    localidad.options[0].value = 0;
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
        localidad.appendChild($fragment);
    }
}

// https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/Using_XMLHttpRequest
function sendHttpRequest(method, url, data, callback) {
    const xhr = getXhr();
    xhr.onreadystatechange = processRequest;
    function getXhr() {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else {
            return new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    function processRequest() {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            if (xhr.status == 200) {
                if (callback) callback(xhr.responseText);
            }
        }
    }
    xhr.open(method, url + ((/\?/).test(url) ? "&" : "?") + (new Date()).getTime());
    if (data && !(data instanceof FormData)) xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(data);
}

// Validamos que contenga un identificador válido
function validId(id, min, max) {
    if (get_int(id)) {
        id = parseInt(id);
        if (id >= min && id <= max) {
            return true;
        }
    }
    return false;
}

function get_int(n) {
    if (n != null) {
        // Si es un caracter numérico entero
        if (/^[+-]?\d+$/.test(n)) {
            return true;
        }
    }
    return false;
}

function preventFormSubmit() {
    // Evitar enviar el formulario presionando la tecla ENTER en un input field
    if (!document.querySelector('form')) {
        return;
    }
    // También se puede utilizar el evento onkeydown
    document.querySelector('form').onkeypress = (e) => {
        if (e.target.tagName !== "TEXTAREA") {
            if (e.key === "Enter") {
                // Evitamos que se ejecuté el evento
                e.preventDefault();
                // Retornamos false
                return false;
            }
        }
    }
}

function initShowPasswords() {
    // Muestra las contraseñas en el formulario de registro de usuario
    if (document.querySelector('#password')
        && document.querySelector('#confirm-password')) {

        if (document.querySelector('#show-password')) {
            document.querySelector('#show-password').onclick = showPasswords;
        }
    }
}

function initFlashes() {
    if (document.querySelector('.alert')) {
        document.querySelectorAll('.alert').forEach(el => {
            setTimeout(function () { /*el.style.display = 'none';*/ el.remove(); }, 7000);
        });
    }
}

function showPasswords() {
    let x = document.querySelector('#password');
    let y = document.querySelector('#confirm-password');
    if (x.type === 'password' && y.type === 'password') {
        x.type = y.type = 'text';
    } else {
        x.type = y.type = 'password';
    }
}

function initErrorFields() {
    // Captura el primer error que exista en los formularios de registro
    if (document.querySelector('.is-invalid')) {
        document.querySelector('.is-invalid').focus();
    }
}