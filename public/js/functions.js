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
// https://html.spec.whatwg.org/multipage/input.html#valid-e-mail-address
// /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/
// https://www.w3.org/Bugs/Public/show_bug.cgi?id=15489
// const regexEmail = /^[a-zA-Z0-9.!#$%&'*+-/=?\^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
// https://owasp.org/www-community/OWASP_Validation_Regex_Repository
const regexEmail = /^[a-zA-Z0-9_+&*-]+(?:\.[a-zA-Z0-9_+&*-]+)*@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,7}$/;

function validEmail(email) {
    return regexEmail.test(email);
}

const regexOnlyNumbers = /^[\d]{8}$/;

function validDocument(usuario) {
    return regexOnlyNumbers.test(usuario);
}

const regexOnlyLetters = /^[a-zA-ZáéíóúÁÉÍÓÚÑñÜü\' ]+$/;

function onlyLetters(str) {
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