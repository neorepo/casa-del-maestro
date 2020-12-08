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

function minLength(str, minLength) {
    let strlen = str.length;
    return (strlen >= minLength);
}

function maxLength(str, maxLength) {
    let strlen = str.length;
    return (strlen <= maxLength);
}

function isEmpty(str) {
    return (str.length == 0);
}


function validEmail(email) {
    // https://owasp.org/www-community/OWASP_Validation_Regex_Repository
    const regexEmail = /^[a-zA-Z0-9_+&*-]+(?:\.[a-zA-Z0-9_+&*-]+)*@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,7}$/;
    return regexEmail.test(email);
}


function validDocument(usuario) {
    const regexOnlyNumbers = /^[\d]{8}$/;
    return regexOnlyNumbers.test(usuario);
}


function onlyLetters(str) {
    const regexOnlyLetters = /^[a-zA-ZáéíóúÁÉÍÓÚÑñÜü\' ]+$/;
    return regexOnlyLetters.test(str);
}

function setError(el, msg) {
    el.classList.add("is-invalid");
    el.parentNode.querySelector('.invalid-feedback').innerHTML = msg;
}

function setSuccess(el) {
    el.classList.remove("is-invalid");
    el.parentNode.querySelector('.invalid-feedback').innerHTML = "";
}

/*related select*/
/*$('select#idEspecie').on('change', function () {
    var idEspecie = $(this).val(),
        selectRaza = $('select#idRaza');
    console.log($('option', selectRaza));
    $(selector).filter(filterFn);
    $('option', selectRaza).hide().filter('[data-id-especie="' + idEspecie + '"],[data-id-especie=""]').show();
    selectRaza.val('');
}).trigger('change');

document.querySelector('select#idEspecie').onchange = function (e) {
    let idEspecie = this.value;
    let selectRaza = document.querySelector('select#idRaza');
    let options = selectRaza.querySelectorAll('option');
    options.forEach(option => { option.style.display = 'none'; });
    Array.prototype.filter.call(options, option => {
        if (option.dataset.idEspecie === idEspecie) {
            option.style.display = 'block';
        }
    });
    selectRaza.value = '';
}*/