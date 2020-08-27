'use strict';

let provincia, localidad;

document.addEventListener('DOMContentLoaded', () => {
    // Si existe el id de provincia y el id de localidad 
    if (document.querySelector("#id-provincia") && document.querySelector("#id-localidad")) {

        provincia = document.querySelector("#id-provincia");
        localidad = document.querySelector("#id-localidad");

        provincia.onchange = () => {

            // Validamos el id de provincia
            if ( isValidProvinceId(provincia.value) ) {

                data_request( parseInt(provincia.value) );
            } else {
                reset();
            }
        }
    }

    // Muestra las contraseñas en el formulario de registro de usuario
    if (document.querySelector('#password') && document.querySelector('#confirm-password')) {

        if (document.querySelector('#show-password')) {
            document.querySelector('#show-password').onclick = showPasswords;
        }
    }

    // Captura el primer error que exista en los formularios de registro
    if (document.querySelector('.is-invalid')) {
        document.querySelector('.is-invalid').focus();
    }
});

function data_request(id_provincia) {
    let data = "id_provincia=" + encodeURIComponent(id_provincia);
    let url = 'server_processing.php';
    sendHttpRequest(
        'POST',
        url + ((/\?/).test(url) ? "&" : "?") + (new Date()).getTime(),
        data,
        loadLocalities
    );
}

function reset() {
    localidad.options.length = 0;
    localidad.options[0] = new Option("- Seleccionar -");
    localidad.options[0].value = 0;
}

function loadLocalities(response) {
    let newOption;
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
                localidad.add(newOption);
            } catch (e) {
                localidad.appendChild(newOption);
            }
        });
    }
}


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

    xhr.open(method, url);
    xhr.withCredentials = true;
    if (data && !(data instanceof FormData)) xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(data);
}

function isValidProvinceId(id) {
    if (get_int(id)) {
        if (id > 0 && id < 25) {
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

function showPasswords() {
    let x = document.querySelector('#password');
    let y = document.querySelector('#confirm-password');
    if (x.type === 'password' && y.type === 'password') {
        x.type = y.type = 'text';
    } else {
        x.type = y.type = 'password';
    }
}