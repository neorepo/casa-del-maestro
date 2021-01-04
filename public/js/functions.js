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
    if (!get_int(id)) return false;
    id = parseInt(id);
    return (id >= min && id <= max);
}

function get_int(n) {
    if (n == null) return false;
    // Si es un caracter numérico entero
    return (/^[+-]?\d+$/.test(n));
}

function minLength(str, minLength) {
    const strlen = str.length;
    return (strlen >= minLength);
}

function maxLength(str, maxLength) {
    const strlen = str.length;
    return (strlen <= maxLength);
}

function isEmpty(str) {
    return (str.length == 0);
}


function validEmail(email) {
    // https://owasp.org/www-community/OWASP_Validation_Regex_Repository
    const regexEmail = /^[a-zA-Z0-9_+&*-]+(?:\.[a-zA-Z0-9_+&*-]+)*@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,7}$/;
    return regexEmail.test(email);
    // var objRE = /^[\w-\.\']{1,}\@([\da-zA-Z\-]{1,}\.){1,}[\da-zA-Z\-]{2,}$/;
}


function validDocument(num) {
    const regexOnlyNumbers = /^[\d]{8}$/;
    return regexOnlyNumbers.test(num);
}


function onlyLetters(str) {
    const regexOnlyLetters = /^[a-zA-ZáéíóúÁÉÍÓÚÑñÜü\s]+$/;
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
    $('option', selectRaza).hide().filter('[data-id-especie="' + idEspecie + '"],[data-id-especie=""]').show();
    selectRaza.val('');
}).trigger('change');

document.querySelector('select#idEspecie').onchange = function (e) {
    const idEspecie = this.value;
    const selectRaza = document.querySelector('select#idRaza');
    const options = selectRaza.querySelectorAll('option');
    options.forEach(opt => opt.style.display = 'none');
    Array.prototype.filter.call(options, opt => opt.dataset.idEspecie === idEspecie).forEach(opt => opt.style.display = 'block');
    selectRaza.selectedIndex = '0';
}*/